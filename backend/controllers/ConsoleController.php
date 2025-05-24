<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\AuditLog;

/**
 * ConsoleController displays available console commands
 */
class ConsoleController extends Controller
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
        ];
    }

    /**
     * Lists all available console commands
     * @return mixed
     */
    public function actionIndex()
    {
        // Register console-specific assets
        \backend\assets\ConsoleAsset::register($this->view);
        
        AuditLog::logSystemEvent('Przeglądanie listy komend consolowych', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        // Lista dostępnych komend z opisami
        $commands = [
            'migrate' => [
                'title' => 'Migracje bazy danych',
                'description' => 'Zarządzanie migracjami bazy danych',
                'commands' => [
                    [
                        'command' => 'yii migrate',
                        'description' => 'Uruchamia wszystkie nowe migracje',
                        'example' => 'php yii migrate'
                    ],
                    [
                        'command' => 'yii migrate/status',
                        'description' => 'Pokazuje status migracji',
                        'example' => 'php yii migrate/status'
                    ],
                    [
                        'command' => 'yii migrate/down',
                        'description' => 'Cofa ostatnią migrację',
                        'example' => 'php yii migrate/down 1'
                    ]
                ]
            ],
            'queue' => [
                'title' => 'Zarządzanie kolejką zadań',
                'description' => 'Operacje na zadaniach w kolejce',
                'commands' => [
                    [
                        'command' => 'yii queue/run',
                        'description' => 'Uruchamia przetwarzanie zadań w kolejce',
                        'example' => 'php yii queue/run --limit=10'
                    ],
                    [
                        'command' => 'yii queue/status',
                        'description' => 'Pokazuje status kolejki zadań',
                        'example' => 'php yii queue/status'
                    ]
                ]
            ],
            'import' => [
                'title' => 'Import zdjęć',
                'description' => 'Testowanie i diagnostyka importu zdjęć',
                'commands' => [
                    [
                        'command' => 'yii import/test',
                        'description' => 'Testuje import zdjęć z podanego katalogu',
                        'example' => 'php yii import/test uploads/import --recursive=1 --deleteOriginals=0',
                        'params' => [
                            'directory' => 'Katalog do importu (domyślnie: uploads/import)',
                            'recursive' => 'Czy szukać rekursywnie (domyślnie: true)',
                            'deleteOriginals' => 'Czy usuwać oryginalne pliki (domyślnie: false)'
                        ]
                    ],
                    [
                        'command' => 'yii import/info',
                        'description' => 'Wyświetla informacje o systemie plików',
                        'example' => 'php yii import/info'
                    ]
                ]
            ],
            'debug' => [
                'title' => 'Debugowanie importu',
                'description' => 'Narzędzia diagnostyczne dla importu zdjęć',
                'commands' => [
                    [
                        'command' => 'yii debug/import-jobs',
                        'description' => 'Sprawdza status ostatnich zadań importu',
                        'example' => 'php yii debug/import-jobs --limit=5',
                        'params' => [
                            'limit' => 'Liczba zadań do wyświetlenia (domyślnie: 5)'
                        ]
                    ],
                    [
                        'command' => 'yii debug/queue-photos',
                        'description' => 'Sprawdza zdjęcia w poczekalni',
                        'example' => 'php yii debug/queue-photos --limit=10',
                        'params' => [
                            'limit' => 'Liczba zdjęć do wyświetlenia (domyślnie: 10)'
                        ]
                    ],
                    [
                        'command' => 'yii debug/check-files',
                        'description' => 'Sprawdza pliki w katalogach',
                        'example' => 'php yii debug/check-files uploads/import',
                        'params' => [
                            'directory' => 'Katalog do sprawdzenia (domyślnie: uploads/import)'
                        ]
                    ],
                    [
                        'command' => 'yii debug/logs',
                        'description' => 'Sprawdza logi aplikacji',
                        'example' => 'php yii debug/logs --lines=50',
                        'params' => [
                            'lines' => 'Liczba ostatnich linii (domyślnie: 50)'
                        ]
                    ]
                ]
            ],
            'exif' => [
                'title' => 'Zarządzanie danymi EXIF',
                'description' => 'Operacje na danych EXIF zdjęć',
                'commands' => [
                    [
                        'command' => 'yii exif/extract',
                        'description' => 'Wyodrębnia dane EXIF z istniejących zdjęć',
                        'example' => 'php yii exif/extract --limit=100 --force=1',
                        'params' => [
                            'limit' => 'Maksymalna liczba zdjęć do przetworzenia (0 = wszystkie)',
                            'force' => 'Wymusza ponowne wyodrębnienie (domyślnie: false)'
                        ]
                    ],
                    [
                        'command' => 'yii exif/show-copyright',
                        'description' => 'Pokazuje zdjęcia z informacjami o prawach autorskich',
                        'example' => 'php yii exif/show-copyright --limit=20',
                        'params' => [
                            'limit' => 'Liczba zdjęć do wyświetlenia (domyślnie: 20)'
                        ]
                    ],
                    [
                        'command' => 'yii exif/stats',
                        'description' => 'Wyświetla statystyki danych EXIF',
                        'example' => 'php yii exif/stats'
                    ],
                    [
                        'command' => 'yii exif/clean',
                        'description' => 'Usuwa wszystkie dane EXIF z bazy danych',
                        'example' => 'php yii exif/clean --confirm=1',
                        'params' => [
                            'confirm' => 'Potwierdza operację (wymagane)'
                        ]
                    ]
                ]
            ],
            'rbac' => [
                'title' => 'Zarządzanie rolami i uprawnieniami',
                'description' => 'Operacje RBAC (Role-Based Access Control)',
                'commands' => [
                    [
                        'command' => 'yii rbac/init',
                        'description' => 'Inicjalizuje system ról i uprawnień',
                        'example' => 'php yii rbac/init'
                    ]
                ]
            ],
            's3' => [
                'title' => 'Operacje S3',
                'description' => 'Zarządzanie plikami w Amazon S3',
                'commands' => [
                    [
                        'command' => 'yii s3/sync',
                        'description' => 'Synchronizuje pliki z S3',
                        'example' => 'php yii s3/sync'
                    ],
                    [
                        'command' => 'yii s3/test',
                        'description' => 'Testuje połączenie z S3',
                        'example' => 'php yii s3/test'
                    ]
                ]
            ]
        ];

        return $this->render('index', [
            'commands' => $commands,
        ]);
    }

    /**
     * Executes a console command (for testing purposes only)
     * This is disabled by default for security reasons
     * @return mixed
     */
    public function actionExecute()
    {
        // UWAGA: Ta funkcja jest wyłączona ze względów bezpieczeństwa
        // Można ją włączyć tylko w środowisku deweloperskim
        
        if (YII_ENV_PROD) {
            AuditLog::logSystemEvent('Próba wykonania komendy consolowej w środowisku produkcyjnym', 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_ACCESS);
            
            Yii::$app->session->setFlash('error', 'Wykonywanie komend jest wyłączone w środowisku produkcyjnym.');
            return $this->redirect(['index']);
        }

        $command = Yii::$app->request->post('command');
        
        if (empty($command)) {
            Yii::$app->session->setFlash('error', 'Nie podano komendy do wykonania.');
            return $this->redirect(['index']);
        }

        // Walidacja komendy - tylko dozwolone komendy
        $allowedCommands = [
            'yii migrate/status',
            'yii queue/status', 
            'yii import/info',
            'yii debug/import-jobs',
            'yii debug/queue-photos',
            'yii exif/stats'
        ];

        if (!in_array($command, $allowedCommands)) {
            AuditLog::logSystemEvent("Próba wykonania niedozwolonej komendy: {$command}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
            
            Yii::$app->session->setFlash('error', 'Komenda nie jest dozwolona do wykonania przez interfejs web.');
            return $this->redirect(['index']);
        }

        AuditLog::logSystemEvent("Wykonywanie komendy consolowej: {$command}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_SYSTEM);

        try {
            // Wykonanie komendy
            $output = [];
            $returnCode = 0;
            
            exec("cd " . Yii::getAlias('@app') . " && php {$command} 2>&1", $output, $returnCode);
            
            $outputText = implode("\n", $output);
            
            if ($returnCode === 0) {
                AuditLog::logSystemEvent("Komenda wykonana pomyślnie: {$command}", 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SYSTEM);
                
                Yii::$app->session->setFlash('success', 'Komenda wykonana pomyślnie.');
                Yii::$app->session->setFlash('commandOutput', $outputText);
            } else {
                AuditLog::logSystemEvent("Komenda zakończona błędem (kod: {$returnCode}): {$command}", 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
                
                Yii::$app->session->setFlash('error', "Komenda zakończona błędem (kod: {$returnCode}).");
                Yii::$app->session->setFlash('commandOutput', $outputText);
            }
            
        } catch (\Exception $e) {
            AuditLog::logSystemEvent("Błąd wykonywania komendy {$command}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas wykonywania komendy: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}