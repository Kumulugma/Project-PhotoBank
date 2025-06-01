<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\AuditLog;

/**
 * Site controller for backend with audit logging
 */
class SiteController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex() {
        return $this->actionDashboard();
    }

    public function actionDashboard() {
        // Loguj dostęp do dashboardu
        AuditLog::logSystemEvent('Przeglądanie głównego dashboardu', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        $totalPhotos = \common\models\Photo::find()
                ->where(['status' => \common\models\Photo::STATUS_ACTIVE])
                ->count();

        $queuedPhotos = \common\models\Photo::find()
                ->where(['status' => \common\models\Photo::STATUS_QUEUE])
                ->count();

        $totalCategories = \common\models\Category::find()->count();
        $totalTags = \common\models\Tag::find()->count();

        // Statystyki katalogów
        $thumbnailsPath = Yii::getAlias('@frontend/web/uploads/thumbnails');
        $importPath = Yii::getAlias('@backend/web/uploads/import');

        // Rozmiar katalogu thumbnails
        $thumbnailsSize = $this->getDirectorySize($thumbnailsPath);
        $thumbnailsSizeFormatted = $this->formatBytes($thumbnailsSize);

        // Liczba plików w katalogu import
        $importFilesCount = $this->countFilesInDirectory($importPath);

        // Statystyki dziennika zdarzeń
        $auditStats = [
            'total_events' => AuditLog::find()->count(),
            'today_events' => AuditLog::find()->where(['>=', 'created_at', strtotime('today')])->count(),
            'errors_today' => AuditLog::find()
                    ->where(['severity' => AuditLog::SEVERITY_ERROR])
                    ->andWhere(['>=', 'created_at', strtotime('today')])
                    ->count(),
            'warnings_today' => AuditLog::find()
                    ->where(['severity' => AuditLog::SEVERITY_WARNING])
                    ->andWhere(['>=', 'created_at', strtotime('today')])
                    ->count(),
        ];

        // Pobieranie kosztów AWS
        $awsCosts = null;
        if (Yii::$app->has('awsCost')) {
            try {
                $currentCosts = Yii::$app->awsCost->getCurrentMonthCosts();
                $forecast = Yii::$app->awsCost->getMonthEndForecast();
                $lastMonth = Yii::$app->awsCost->getLastMonthCosts();
                $s3Costs = Yii::$app->awsCost->getS3Costs();

                $awsCosts = [
                    'current' => $currentCosts,
                    'forecast' => $forecast,
                    'lastMonth' => $lastMonth,
                    's3' => $s3Costs
                ];
            } catch (\Exception $e) {
                AuditLog::logSystemEvent('Błąd pobierania kosztów AWS: ' . $e->getMessage(),
                        AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
                $awsCosts = ['error' => true, 'message' => $e->getMessage()];
            }
        }

        return $this->render('dashboard', [
                    'totalPhotos' => $totalPhotos,
                    'queuedPhotos' => $queuedPhotos,
                    'totalCategories' => $totalCategories,
                    'totalTags' => $totalTags,
                    'thumbnailsSize' => $thumbnailsSize,
                    'thumbnailsSizeFormatted' => $thumbnailsSizeFormatted,
                    'importFilesCount' => $importFilesCount,
                    'auditStats' => $auditStats,
                    'awsCosts' => $awsCosts,
        ]);
    }

    /**
     * Oblicza rozmiar katalogu rekurencyjnie
     * 
     * @param string $path
     * @return int
     */
    private function getDirectorySize($path) {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            AuditLog::logSystemEvent('Błąd odczytu rozmiaru katalogu: ' . $path . ' - ' . $e->getMessage(),
                    AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
        }

        return $size;
    }

    /**
     * Formatuje bajty na czytelną wartość
     * 
     * @param int $size
     * @return string
     */
    private function formatBytes($size) {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($size, 1024));
        $power = min($power, count($units) - 1);

        return round($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Liczy pliki w katalogu (bez podkatalogów)
     * 
     * @param string $path
     * @return int
     */
    private function countFilesInDirectory($path) {
        if (!is_dir($path)) {
            return 0;
        }

        try {
            $files = glob($path . '/*');
            return count(array_filter($files, 'is_file'));
        } catch (\Exception $e) {
            AuditLog::logSystemEvent('Błąd odczytu liczby plików: ' . $path . ' - ' . $e->getMessage(),
                    AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
            return 0;
        }
    }

    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Check if user has admin role
            if (!Yii::$app->user->can('admin')) {
                $user = Yii::$app->user->identity;
                Yii::$app->user->logout();

                // Loguj odmowę dostępu
                AuditLog::log(AuditLog::ACTION_ACCESS,
                        "Odmowa dostępu do panelu administratora dla użytkownika: {$user->username}",
                        [
                            'severity' => AuditLog::SEVERITY_WARNING,
                            'model_class' => get_class($user),
                            'model_id' => $user->id
                        ]
                );

                Yii::$app->session->setFlash('error', 'You do not have permission to access the admin panel.');
                return $this->goHome();
            }

            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    public function actionLogout() {
        $user = Yii::$app->user->identity;

        if ($user) {
            // Loguj wylogowanie
            AuditLog::logLogout($user);
        }

        Yii::$app->user->logout();

        return $this->goHome();
    }

}
