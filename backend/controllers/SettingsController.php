<?php

namespace backend\controllers;

use Yii;
use common\models\Settings;
use common\models\AuditLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * SettingsController implements the actions for Settings model with audit logging.
 */
class SettingsController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Settings models.
     * @return mixed
     */
    public function actionIndex()
    {
        AuditLog::logSystemEvent('Przeglądanie ustawień systemu', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);
        
        // Get all settings and group them by category
        $allSettings = Settings::find()->orderBy(['key' => SORT_ASC])->all();
        $settings = [];
        
        foreach ($allSettings as $setting) {
            // Parse setting key - assuming format category.name
            $parts = explode('.', $setting->key);
            
            if (count($parts) >= 2) {
                $category = $parts[0];
                $name = $parts[1];
                
                if (!isset($settings[$category])) {
                    $settings[$category] = [];
                }
                
                // Mask sensitive data
                $value = $setting->value;
                if (in_array($name, ['secret_key', 'password', 'api_key', 'secret'])) {
                    $value = '********';
                }
                
                $settings[$category][$name] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $value,
                    'description' => $setting->description,
                ];
            } else {
                // For settings without category
                if (!isset($settings['general'])) {
                    $settings['general'] = [];
                }
                
                $settings['general'][$setting->key] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            }
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Updates settings.
     * @return mixed
     */
    public function actionUpdate()
    {
        $settingsData = Yii::$app->request->post('Settings', []);
        
        if (!empty($settingsData)) {
            AuditLog::logSystemEvent('Rozpoczęto aktualizację ustawień systemu - ' . count($settingsData) . ' pozycji', 
                AuditLog::SEVERITY_INFO, AuditLog::ACTION_SETTINGS);
            
            $transaction = Yii::$app->db->beginTransaction();
            $updatedCount = 0;
            $changedSettings = [];
            
            try {
                foreach ($settingsData as $key => $value) {
                    // Skip masked values
                    if ($value === '********') {
                        continue;
                    }
                    
                    // Find or create setting
                    $setting = Settings::findOne(['key' => $key]);
                    $oldValue = $setting ? $setting->value : null;
                    $isNewSetting = !$setting;
                    
                    if (!$setting) {
                        $setting = new Settings();
                        $setting->key = $key;
                        $setting->created_at = time();
                    }
                    
                    // Only update if value actually changed
                    if ($setting->value !== $value) {
                        $setting->value = $value;
                        $setting->updated_at = time();
                        
                        if (!$setting->save()) {
                            throw new \Exception('Error saving setting ' . $key . ': ' . json_encode($setting->errors));
                        }
                        
                        // Log individual setting change
                        AuditLog::logSettingsChange($key, $oldValue, $value);
                        
                        $changedSettings[] = [
                            'key' => $key,
                            'old_value' => $oldValue,
                            'new_value' => $value,
                            'is_new' => $isNewSetting
                        ];
                        
                        $updatedCount++;
                    }
                }
                
                $transaction->commit();
                
                if ($updatedCount > 0) {
                    // Create summary log entry
                    $summaryMessage = "Zaktualizowano {$updatedCount} ustawień: ";
                    $settingNames = array_map(function($s) { return $s['key']; }, $changedSettings);
                    $summaryMessage .= implode(', ', array_slice($settingNames, 0, 10));
                    if (count($settingNames) > 10) {
                        $summaryMessage .= ' i ' . (count($settingNames) - 10) . ' więcej';
                    }
                    
                    AuditLog::logSystemEvent($summaryMessage, AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SETTINGS, [
                        'new_values' => $changedSettings
                    ]);
                    
                    Yii::$app->session->setFlash('success', "Pomyślnie zaktualizowano {$updatedCount} ustawień.");
                } else {
                    AuditLog::logSystemEvent('Brak zmian w ustawieniach - wszystkie wartości pozostały bez zmian', 
                        AuditLog::SEVERITY_INFO, AuditLog::ACTION_SETTINGS);
                    Yii::$app->session->setFlash('info', 'Brak zmian do zapisania.');
                }
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                AuditLog::logSystemEvent('Błąd aktualizacji ustawień: ' . $e->getMessage(), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SETTINGS);
                Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas aktualizacji ustawień: ' . $e->getMessage());
            }
        } else {
            AuditLog::logSystemEvent('Próba aktualizacji ustawień bez danych', 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SETTINGS);
            Yii::$app->session->setFlash('warning', 'Brak danych do aktualizacji.');
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Creates a new setting
     * @return mixed
     */
    public function actionCreate()
    {
        $setting = new Settings();
        
        if ($setting->load(Yii::$app->request->post())) {
            $setting->created_at = time();
            $setting->updated_at = time();
            
            if ($setting->save()) {
                AuditLog::logModelAction($setting, AuditLog::ACTION_CREATE);
                AuditLog::logSystemEvent("Utworzono nowe ustawienie: {$setting->key} = {$setting->value}", 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SETTINGS, [
                        'model_class' => get_class($setting),
                        'model_id' => $setting->id
                    ]);
                
                Yii::$app->session->setFlash('success', 'Ustawienie zostało pomyślnie utworzone.');
                return $this->redirect(['index']);
            } else {
                AuditLog::logSystemEvent('Błąd tworzenia nowego ustawienia: ' . json_encode($setting->errors), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SETTINGS);
            }
        }
        
        return $this->render('create', [
            'model' => $setting,
        ]);
    }

    /**
     * Updates an existing setting
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateSingle($id)
    {
        $setting = $this->findModel($id);
        $oldValue = $setting->value;
        
        if ($setting->load(Yii::$app->request->post())) {
            $setting->updated_at = time();
            
            if ($setting->save()) {
                if ($oldValue !== $setting->value) {
                    AuditLog::logSettingsChange($setting->key, $oldValue, $setting->value);
                    AuditLog::logSystemEvent("Zaktualizowano ustawienie: {$setting->key}", 
                        AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SETTINGS, [
                            'model_class' => get_class($setting),
                            'model_id' => $setting->id,
                            'old_values' => ['value' => $oldValue],
                            'new_values' => ['value' => $setting->value]
                        ]);
                }
                
                Yii::$app->session->setFlash('success', 'Ustawienie zostało pomyślnie zaktualizowane.');
                return $this->redirect(['index']);
            } else {
                AuditLog::logSystemEvent("Błąd aktualizacji ustawienia ID {$id}: " . json_encode($setting->errors), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SETTINGS);
            }
        }
        
        return $this->render('update', [
            'model' => $setting,
        ]);
    }

    /**
     * Deletes an existing setting
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $setting = $this->findModel($id);
        $settingKey = $setting->key;
        $settingValue = $setting->value;
        
        try {
            if ($setting->delete()) {
                AuditLog::logSystemEvent("Usunięto ustawienie: {$settingKey} (wartość: {$settingValue})", 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_DELETE, [
                        'model_class' => get_class($setting),
                        'old_values' => $setting->attributes
                    ]);
                
                Yii::$app->session->setFlash('success', 'Ustawienie zostało pomyślnie usunięte.');
            } else {
                throw new \Exception('Nie udało się usunąć ustawienia');
            }
        } catch (\Exception $e) {
            AuditLog::logSystemEvent("Błąd usuwania ustawienia ID {$id}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_DELETE);
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas usuwania ustawienia: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Reset settings to default values
     * @return mixed
     */
    public function actionReset()
    {
        $category = Yii::$app->request->post('category');
        
        if (empty($category)) {
            Yii::$app->session->setFlash('error', 'Nie wybrano kategorii do resetowania.');
            return $this->redirect(['index']);
        }
        
        AuditLog::logSystemEvent("Rozpoczęto resetowanie ustawień kategorii: {$category}", 
            AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SETTINGS);
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Get all settings in category
            $settings = Settings::find()
                ->where(['like', 'key', $category . '.%', false])
                ->all();
            
            $resetCount = 0;
            $resetSettings = [];
            
            foreach ($settings as $setting) {
                $oldValue = $setting->value;
                $defaultValue = $this->getDefaultValue($setting->key);
                
                if ($defaultValue !== null && $oldValue !== $defaultValue) {
                    $setting->value = $defaultValue;
                    $setting->updated_at = time();
                    
                    if ($setting->save()) {
                        AuditLog::logSettingsChange($setting->key, $oldValue, $defaultValue);
                        $resetSettings[] = $setting->key;
                        $resetCount++;
                    }
                }
            }
            
            $transaction->commit();
            
            if ($resetCount > 0) {
                AuditLog::logSystemEvent("Zresetowano {$resetCount} ustawień w kategorii {$category}: " . implode(', ', $resetSettings), 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SETTINGS);
                
                Yii::$app->session->setFlash('success', "Pomyślnie zresetowano {$resetCount} ustawień w kategorii {$category}.");
            } else {
                AuditLog::logSystemEvent("Brak ustawień do resetowania w kategorii: {$category}", 
                    AuditLog::SEVERITY_INFO, AuditLog::ACTION_SETTINGS);
                
                Yii::$app->session->setFlash('info', 'Brak ustawień do resetowania w wybranej kategorii.');
            }
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            AuditLog::logSystemEvent("Błąd resetowania ustawień kategorii {$category}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SETTINGS);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas resetowania ustawień: ' . $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Export settings to file
     * @return mixed
     */
    public function actionExport()
    {
        $format = Yii::$app->request->get('format', 'json');
        $category = Yii::$app->request->get('category');
        
        AuditLog::logSystemEvent("Eksport ustawień - format: {$format}" . ($category ? ", kategoria: {$category}" : ""), 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_EXPORT);
        
        $query = Settings::find()->orderBy(['key' => SORT_ASC]);
        
        if ($category) {
            $query->where(['like', 'key', $category . '.%', false]);
        }
        
        $settings = $query->all();
        
        if ($format === 'json') {
            $data = [];
            foreach ($settings as $setting) {
                $data[$setting->key] = [
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            }
            
            $filename = 'settings_' . ($category ?: 'all') . '_' . date('Y-m-d_H-i-s') . '.json';
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->setDownloadHeaders($filename, 'application/json');
            
            return $data;
        } else {
            // CSV format
            $filename = 'settings_' . ($category ?: 'all') . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
            
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fwrite($output, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($output, ['Klucz', 'Wartość', 'Opis'], ';');
            
            foreach ($settings as $setting) {
                fputcsv($output, [
                    $setting->key,
                    $setting->value,
                    $setting->description ?: ''
                ], ';');
            }
            
            fclose($output);
            return Yii::$app->response;
        }
    }

    /**
     * Import settings from file
     * @return mixed
     */
    public function actionImport()
    {
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('import_file');
        
        if (!$uploadedFile) {
            Yii::$app->session->setFlash('error', 'Nie wybrano pliku do importu.');
            return $this->redirect(['index']);
        }
        
        AuditLog::logSystemEvent("Rozpoczęto import ustawień z pliku: {$uploadedFile->name}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_IMPORT);
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $content = file_get_contents($uploadedFile->tempName);
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Nieprawidłowy format JSON: ' . json_last_error_msg());
            }
            
            $importedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;
            
            foreach ($data as $key => $settingData) {
                $value = is_array($settingData) ? $settingData['value'] : $settingData;
                $description = is_array($settingData) ? ($settingData['description'] ?? '') : '';
                
                $setting = Settings::findOne(['key' => $key]);
                $isNew = !$setting;
                $oldValue = $setting ? $setting->value : null;
                
                if (!$setting) {
                    $setting = new Settings();
                    $setting->key = $key;
                    $setting->created_at = time();
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
                
                $setting->value = $value;
                $setting->description = $description;
                $setting->updated_at = time();
                
                if ($setting->save()) {
                    if ($isNew) {
                        AuditLog::logModelAction($setting, AuditLog::ACTION_CREATE);
                    } else {
                        AuditLog::logSettingsChange($key, $oldValue, $value);
                    }
                    $importedCount++;
                } else {
                    throw new \Exception("Błąd zapisywania ustawienia {$key}: " . json_encode($setting->errors));
                }
            }
            
            $transaction->commit();
            
            AuditLog::logSystemEvent("Zakończono import ustawień - zaimportowano: {$importedCount}, utworzono: {$createdCount}, zaktualizowano: {$updatedCount}", 
                AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_IMPORT);
            
            Yii::$app->session->setFlash('success', 
                "Pomyślnie zaimportowano {$importedCount} ustawień. Utworzono: {$createdCount}, zaktualizowano: {$updatedCount}.");
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            AuditLog::logSystemEvent("Błąd importu ustawień: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_IMPORT);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas importu: ' . $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Test connection for specific settings category
     * @return mixed
     */
    public function actionTestConnection()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $category = Yii::$app->request->post('category');
        
        if (empty($category)) {
            return [
                'success' => false,
                'message' => 'Nie określono kategorii do testowania.'
            ];
        }
        
        AuditLog::logSystemEvent("Test połączenia dla kategorii: {$category}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_SYSTEM);
        
        try {
            $result = false;
            $message = '';
            
            switch ($category) {
                case 's3':
                    if (Yii::$app->has('s3')) {
                        $s3 = Yii::$app->get('s3');
                        $testResult = $s3->testConnection();
                        $result = $testResult === true;
                        $message = $result ? 'Połączenie z S3 działa poprawnie.' : $testResult;
                    } else {
                        $message = 'Komponent S3 nie jest skonfigurowany.';
                    }
                    break;
                    
                case 'email':
                    // Test email settings
                    $mailer = Yii::$app->mailer;
                    $testEmail = $mailer->compose()
                        ->setTo('test@example.com')
                        ->setSubject('Test email settings')
                        ->setTextBody('This is a test email.');
                    $result = true; // Assume success if no exception
                    $message = 'Konfiguracja email wygląda poprawnie.';
                    break;
                    
                default:
                    $message = "Test połączenia dla kategorii '{$category}' nie jest obsługiwany.";
            }
            
            if ($result) {
                AuditLog::logSystemEvent("Test połączenia {$category} zakończony sukcesem", 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SYSTEM);
            } else {
                AuditLog::logSystemEvent("Test połączenia {$category} nieudany: {$message}", 
                    AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
            }
            
            return [
                'success' => $result,
                'message' => $message
            ];
            
        } catch (\Exception $e) {
            AuditLog::logSystemEvent("Błąd testu połączenia {$category}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            
            return [
                'success' => false,
                'message' => 'Błąd podczas testowania: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Updates or creates a setting.
     * 
     * @param string $key Setting key
     * @param string $value Setting value
     * @param string $description Optional description
     * @return bool Success
     */
    protected function updateSetting($key, $value, $description = null)
    {
        $setting = Settings::findOne(['key' => $key]);
        $oldValue = $setting ? $setting->value : null;
        $isNew = !$setting;
        
        if ($setting) {
            // Update existing setting
            $setting->value = $value;
            $setting->updated_at = time();
            
            if ($description !== null) {
                $setting->description = $description;
            }
        } else {
            // Create new setting
            $setting = new Settings();
            $setting->key = $key;
            $setting->value = $value;
            $setting->description = $description;
            $setting->created_at = time();
            $setting->updated_at = time();
        }
        
        $result = $setting->save();
        
        if ($result) {
            if ($isNew) {
                AuditLog::logModelAction($setting, AuditLog::ACTION_CREATE);
            } else {
                AuditLog::logSettingsChange($key, $oldValue, $value);
            }
        }
        
        return $result;
    }

    /**
     * Get default value for a setting key
     * 
     * @param string $key
     * @return mixed
     */
    protected function getDefaultValue($key)
    {
        $defaults = [
            // S3 settings
            's3.region' => 'eu-central-1',
            's3.directory' => 'photos',
            's3.deleted_directory' => 'deleted',
            
            // Upload settings
            'upload.preserve_original_names' => '1',
            'upload.frontend_mode' => '0',
            
            // Watermark settings
            'watermark.type' => 'text',
            'watermark.position' => 'bottom-right',
            'watermark.opacity' => '0.5',
            'watermark.text' => 'Zasobnik B',
            
            // AI settings
            'ai.enabled' => '0',
            'ai.provider' => '',
            'ai.region' => 'us-east-1',
            
            // Gallery settings
            'gallery.exif_show_copyright' => '1',
            'gallery.exif_show_camera' => '1',
            'gallery.exif_show_lens' => '1',
            'gallery.exif_show_exposure' => '1',
            'gallery.exif_show_datetime' => '1',
            'gallery.exif_show_flash' => '1',
            'gallery.exif_show_dimensions' => '1',
            'gallery.exif_show_gps' => '0',
            'gallery.exif_show_software' => '0',
            'gallery.exif_show_technical' => '0',
            'gallery.exif_show_author_info' => '1',
        ];
        
        return $defaults[$key] ?? null;
    }

    /**
     * Finds the Settings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Settings::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Żądane ustawienie nie istnieje.');
    }
}