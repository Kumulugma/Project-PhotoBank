<?php

namespace backend\controllers;

use Yii;
use common\models\Photo;
use common\models\Settings;
use common\models\AuditLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\helpers\PathHelper;

/**
 * ExifController handles EXIF data management
 */
class ExifController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-artist' => ['POST'],
                    'update-settings' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays EXIF settings page
     */
    public function actionIndex()
    {
        $settings = [
            'default_artist' => $this->getSettingValue('exif.default_artist', ''),
            'default_copyright' => $this->getSettingValue('exif.default_copyright', ''),
        ];

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Updates EXIF settings
     */
    public function actionUpdateSettings()
    {
        if (Yii::$app->request->isPost) {
            $artist = Yii::$app->request->post('default_artist', '');
            $copyright = Yii::$app->request->post('default_copyright', '');

            $transaction = Yii::$app->db->beginTransaction();
            try {
                Settings::setSetting('exif.default_artist', $artist, 'Domyślny artysta/autor do ustawienia w EXIF');
                Settings::setSetting('exif.default_copyright', $copyright, 'Domyślne prawa autorskie do ustawienia w EXIF');

                $transaction->commit();

                AuditLog::logSystemEvent("Zaktualizowano ustawienia EXIF - artysta: '$artist', copyright: '$copyright'",
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SETTINGS);

                Yii::$app->session->setFlash('success', 'Ustawienia EXIF zostały zaktualizowane.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                AuditLog::logSystemEvent("Błąd aktualizacji ustawień EXIF: " . $e->getMessage(),
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SETTINGS);
                Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas aktualizacji ustawień: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Sets artist/copyright for a photo
     */
    public function actionSetArtist($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $photo = Photo::findOne($id);
        if (!$photo) {
            return [
                'success' => false,
                'message' => 'Nie znaleziono zdjęcia.'
            ];
        }

        $artist = $this->getSettingValue('exif.default_artist', '');
        $copyright = $this->getSettingValue('exif.default_copyright', '');

        if (empty($artist) && empty($copyright)) {
            return [
                'success' => false,
                'message' => 'Nie skonfigurowano danych artysty/praw autorskich w ustawieniach.'
            ];
        }

        try {
            $filePath = PathHelper::getPhotoPath($photo->file_name, 'temp');
            $fileExists = file_exists($filePath);
            $tempDownloaded = false;

            // Pobierz plik z S3 jeśli nie istnieje lokalnie
            if (!$fileExists && !empty($photo->s3_path)) {
                if (Yii::$app->has('s3')) {
                    /** @var \common\components\S3Component $s3 */
                    $s3 = Yii::$app->get('s3');
                    $s3Settings = $s3->getSettings();

                    try {
                        PathHelper::ensureDirectoryExists('temp');

                        $s3->getObject([
                            'Bucket' => $s3Settings['bucket'],
                            'Key' => $photo->s3_path,
                            'SaveAs' => $filePath
                        ]);

                        $fileExists = true;
                        $tempDownloaded = true;
                    } catch (\Exception $e) {
                        return [
                            'success' => false,
                            'message' => 'Błąd pobierania pliku z S3: ' . $e->getMessage()
                        ];
                    }
                }
            }

            if (!$fileExists) {
                return [
                    'success' => false,
                    'message' => 'Plik zdjęcia nie jest dostępny.'
                ];
            }

            // Ustaw EXIF używając exiftool jeśli dostępny
            $success = $this->setExifData($filePath, $artist, $copyright);

            if ($success) {
                // Jeśli plik był pobrany z S3, prześlij z powrotem
                if ($tempDownloaded && !empty($photo->s3_path)) {
                    /** @var \common\components\S3Component $s3 */
                    $s3 = Yii::$app->get('s3');
                    $s3Settings = $s3->getSettings();

                    try {
                        $s3->putObject([
                            'Bucket' => $s3Settings['bucket'],
                            'Key' => $photo->s3_path,
                            'SourceFile' => $filePath,
                            'ContentType' => $photo->mime_type
                        ]);
                    } catch (\Exception $e) {
                        // Log warning ale nie przerywaj operacji
                        AuditLog::logSystemEvent("Błąd przesyłania zaktualizowanego pliku na S3: " . $e->getMessage(),
                            AuditLog::SEVERITY_WARNING, AuditLog::ACTION_UPDATE);
                    }
                }

                // Wyczyść tymczasowy plik jeśli był pobrany
                if ($tempDownloaded) {
                    @unlink($filePath);
                }

                // Wymuszone ponowne odczytanie EXIF
                $photo->extractAndSaveExif();

                AuditLog::logSystemEvent("Ustawiono dane EXIF dla zdjęcia {$photo->search_code} - artysta: '$artist', copyright: '$copyright'",
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_UPDATE, [
                        'model_class' => get_class($photo),
                        'model_id' => $photo->id
                    ]);

                return [
                    'success' => true,
                    'message' => 'Dane EXIF zostały ustawione pomyślnie.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Nie udało się ustawić danych EXIF. Sprawdź czy exiftool jest zainstalowany.'
                ];
            }
        } catch (\Exception $e) {
            AuditLog::logSystemEvent("Błąd ustawiania EXIF dla zdjęcia ID {$id}: " . $e->getMessage(),
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);

            return [
                'success' => false,
                'message' => 'Wystąpił błąd: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sets EXIF data using exiftool
     */
    protected function setExifData($filePath, $artist, $copyright)
    {
        // Sprawdź czy exiftool jest dostępny
        $exiftoolPath = $this->findExiftool();
        if (!$exiftoolPath) {
            Yii::warning('exiftool nie został znaleziony w systemie');
            return false;
        }

        $commands = [];
        
        if (!empty($artist)) {
            $commands[] = "-Artist=" . escapeshellarg($artist);
            $commands[] = "-XMP:Creator=" . escapeshellarg($artist);
        }
        
        if (!empty($copyright)) {
            $commands[] = "-Copyright=" . escapeshellarg($copyright);
            $commands[] = "-XMP:Rights=" . escapeshellarg($copyright);
        }

        if (empty($commands)) {
            return false;
        }

        // Utwórz kopię zapasową
        $backupPath = $filePath . '.backup';
        copy($filePath, $backupPath);

        try {
            $command = $exiftoolPath . ' ' . implode(' ', $commands) . ' -overwrite_original ' . escapeshellarg($filePath);
            $output = [];
            $returnCode = 0;
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Usuń kopię zapasową jeśli operacja się powiodła
                @unlink($backupPath);
                return true;
            } else {
                // Przywróć z kopii zapasowej w przypadku błędu
                copy($backupPath, $filePath);
                @unlink($backupPath);
                
                Yii::error('exiftool zwrócił kod błędu: ' . $returnCode . ', output: ' . implode("\n", $output));
                return false;
            }
        } catch (\Exception $e) {
            // Przywróć z kopii zapasowej
            copy($backupPath, $filePath);
            @unlink($backupPath);
            
            Yii::error('Błąd wykonania exiftool: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds exiftool executable
     */
    protected function findExiftool()
    {
        // Sprawdź typowe lokalizacje
        $possiblePaths = [
            '/usr/bin/exiftool',
            '/usr/local/bin/exiftool',
            '/opt/local/bin/exiftool',
            'exiftool', // W PATH
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'exiftool') {
                // Sprawdź czy jest w PATH
                $output = [];
                $returnCode = 0;
                exec('which exiftool', $output, $returnCode);
                if ($returnCode === 0 && !empty($output)) {
                    return 'exiftool';
                }
            } else {
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    protected function getSettingValue($key, $default = null)
    {
        return Settings::getSetting($key, $default);
    }
}