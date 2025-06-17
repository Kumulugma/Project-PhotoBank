<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\AuditLog;
use common\models\Photo;
use common\models\Category;
use common\models\Tag;

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

        // Podstawowe statystyki zdjęć
        $totalPhotos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->count();

        $queuedPhotos = Photo::find()
            ->where(['status' => Photo::STATUS_QUEUE])
            ->count();

        // Statystyki widoczności zdjęć
        $publicPhotos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['is_public' => 1])
            ->count();

        $privatePhotos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['is_public' => 0])
            ->count();

        // Statystyki AI
        $aiPhotos = Photo::find()
            ->where(['!=', 'status', Photo::STATUS_DELETED])
            ->andWhere(['is_ai_generated' => 1])
            ->count();

        $aiPercentage = $totalPhotos > 0 ? ($aiPhotos / $totalPhotos) * 100 : 0;

        // Pozostałe statystyki
        $totalCategories = Category::find()->count();
        $totalTags = Tag::find()->count();

        // Statystyki katalogów
        $thumbnailsPath = Yii::getAlias('@frontend/web/uploads/thumbnails');
        $importPath = Yii::getAlias('@backend/web/uploads/import');

        // Rozmiar katalogu thumbnails
        $thumbnailsSize = $this->getDirectorySize($thumbnailsPath);
        $thumbnailsSizeFormatted = $this->formatBytes($thumbnailsSize);

        // Liczba plików w katalogu import
        $importFilesCount = $this->countFilesInDirectory($importPath);

        // Dane do wykresu - zdjęcia z ostatnich 7 dni
        $dailyUploads = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $startOfDay = strtotime($date . ' 00:00:00');
            $endOfDay = strtotime($date . ' 23:59:59');
            
            $count = Photo::find()
                ->where(['between', 'created_at', $startOfDay, $endOfDay])
                ->count();
                
            $dailyUploads[] = [
                'date' => $date,
                'count' => (int)$count
            ];
        }

        // Statystyki dziennika zdarzeń
        $auditStats = [
            'total_events' => AuditLog::find()->count(),
            'today_events' => AuditLog::find()
                ->where(['>=', 'created_at', strtotime('today')])
                ->count(),
            'warning_events' => AuditLog::find()
                ->where(['severity' => AuditLog::SEVERITY_WARNING])
                ->andWhere(['>=', 'created_at', strtotime('-7 days')])
                ->count(),
            'error_events' => AuditLog::find()
                ->where(['severity' => AuditLog::SEVERITY_ERROR])
                ->andWhere(['>=', 'created_at', strtotime('-7 days')])
                ->count(),
        ];

        // Pobierz koszty AWS jeśli komponent jest skonfigurowany
        $awsCosts = null;
        if (Yii::$app->has('awsCost')) {
            try {
                $awsCosts = [
                    'current' => Yii::$app->awsCost->getCurrentMonthCosts(),
                    'forecast' => Yii::$app->awsCost->getMonthEndForecast(),
                    'lastMonth' => Yii::$app->awsCost->getLastMonthCosts(),
                ];
                
                // Nie pobieramy osobno S3 - używamy danych z current
                $awsCosts['s3'] = ['total' => 0]; // Placeholder - można rozszerzyć
                
            } catch (\Exception $e) {
                $awsCosts = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
                AuditLog::logSystemEvent('Błąd pobierania kosztów AWS: ' . $e->getMessage(), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            }
        }

        return $this->render('dashboard', [
            'totalPhotos' => $totalPhotos,
            'queuedPhotos' => $queuedPhotos,
            'publicPhotos' => $publicPhotos,
            'privatePhotos' => $privatePhotos,
            'aiPhotos' => $aiPhotos,
            'aiPercentage' => $aiPercentage,
            'totalCategories' => $totalCategories,
            'totalTags' => $totalTags,
            'thumbnailsSize' => $thumbnailsSize,
            'thumbnailsSizeFormatted' => $thumbnailsSizeFormatted,
            'importFilesCount' => $importFilesCount,
            'dailyUploads' => $dailyUploads,
            'auditStats' => $auditStats,
            'awsCosts' => $awsCosts,
        ]);
    }

    /**
     * Login action.
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            AuditLog::logSystemEvent('Logowanie do panelu administracyjnego', AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_LOGIN);
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     */
    public function actionLogout() {
        AuditLog::logSystemEvent('Wylogowanie z panelu administracyjnego', AuditLog::SEVERITY_INFO, AuditLog::ACTION_LOGOUT);
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Oblicza rozmiar katalogu
     */
    private function getDirectorySize($path) {
        if (!is_dir($path)) {
            return 0;
        }

        $totalSize = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        return $totalSize;
    }

    /**
     * Formatuje bajty na czytelną formę
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Liczy pliki w katalogu
     */
    private function countFilesInDirectory($path) {
        if (!is_dir($path)) {
            return 0;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        return iterator_count($files);
    }
}