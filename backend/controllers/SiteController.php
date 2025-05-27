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
class SiteController extends Controller
{
    public function behaviors()
    {
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

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->actionDashboard();
    }

    public function actionDashboard()
    {
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
            'auditStats' => $auditStats,
            'awsCosts' => $awsCosts,
        ]);
    }

    public function actionLogin()
    {
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

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        
        if ($user) {
            // Loguj wylogowanie
            AuditLog::logLogout($user);
        }
        
        Yii::$app->user->logout();

        return $this->goHome();
    }
}