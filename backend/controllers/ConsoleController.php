<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\AuditLog;

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

    public function actionIndex()
    {
        \backend\assets\ConsoleAsset::register($this->view);
        
        AuditLog::logSystemEvent('Przeglądanie listy komend consolowych', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        $commands = [
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
}