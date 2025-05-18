<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller for backend
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Redirect to dashboard if user is logged in
        return $this->actionDashboard();
    }

    /**
     * Displays the dashboard page.
     *
     * @return string
     */
    public function actionDashboard()
    {
        $totalPhotos = \common\models\Photo::find()
            ->where(['status' => \common\models\Photo::STATUS_ACTIVE])
            ->count();
            
        $queuedPhotos = \common\models\Photo::find()
            ->where(['status' => \common\models\Photo::STATUS_QUEUE])
            ->count();
            
        $totalCategories = \common\models\Category::find()->count();
        $totalTags = \common\models\Tag::find()->count();
        
        return $this->render('dashboard', [
            'totalPhotos' => $totalPhotos,
            'queuedPhotos' => $queuedPhotos,
            'totalCategories' => $totalCategories,
            'totalTags' => $totalTags,
        ]);
    }

    /**
     * Login action.
     *
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Check if user has admin role
            if (!Yii::$app->user->can('admin')) {
                Yii::$app->user->logout();
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

    /**
     * Logout action.
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}