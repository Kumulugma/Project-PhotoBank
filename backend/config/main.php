<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'name' => 'Zasobnik B - Admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'defaultRoute' => 'site/dashboard',
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => ['/site/login'],
        ],
        'session' => [
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/dashboard',
                'login' => 'site/login',
                'logout' => 'site/logout',
                
                // Photos
                'photos' => 'photos/index',
                'photos/view/<id:\d+>' => 'photos/view',
                'photos/update/<id:\d+>' => 'photos/update',
                'photos/delete/<id:\d+>' => 'photos/delete',
                'photos/queue' => 'photos/queue',
                'photos/upload' => 'photos/upload',
                'photos/upload-ajax' => 'photos/upload-ajax',
                'photos/upload-chunk' => 'photos/upload-chunk',
                'photos/approve/<id:\d+>' => 'photos/approve',
                'photos/approve-batch' => 'photos/approve-batch',
                'photos/batch-update' => 'photos/batch-update',
                'photos/batch-delete' => 'photos/batch-delete',
                
                // Categories
                'categories' => 'categories/index',
                'categories/view/<id:\d+>' => 'categories/view',
                'categories/create' => 'categories/create',
                'categories/update/<id:\d+>' => 'categories/update',
                'categories/delete/<id:\d+>' => 'categories/delete',
                
                // Tags
                'tags' => 'tags/index',
                'tags/view/<id:\d+>' => 'tags/view',
                'tags/create' => 'tags/create',
                'tags/update/<id:\d+>' => 'tags/update',
                'tags/delete/<id:\d+>' => 'tags/delete',
                
                // Users
                'users' => 'users/index',
                'users/view/<id:\d+>' => 'users/view',
                'users/create' => 'users/create',
                'users/update/<id:\d+>' => 'users/update',
                'users/delete/<id:\d+>' => 'users/delete',
                
                // System - Settings
                'settings' => 'settings/index',
                'settings/update' => 'settings/update',
                
                // System - S3
                's3' => 's3/index',
                's3/update' => 's3/update',
                's3/test' => 's3/test',
                's3/sync' => 's3/sync',
                
                // System - Thumbnails
                'thumbnails' => 'thumbnails/index',
                'thumbnails/view/<id:\d+>' => 'thumbnails/view',
                'thumbnails/create' => 'thumbnails/create',
                'thumbnails/update/<id:\d+>' => 'thumbnails/update',
                'thumbnails/delete/<id:\d+>' => 'thumbnails/delete',
                'thumbnails/regenerate' => 'thumbnails/regenerate',
                
                // System - Watermark
                'watermark' => 'watermark/index',
                'watermark/update' => 'watermark/update',
                'watermark/preview' => 'watermark/preview',
                
                // System - AI
                'ai' => 'ai/index',
                'ai/update' => 'ai/update',
                'ai/test' => 'ai/test',
                'ai/analyze-photo/<id:\d+>' => 'ai/analyze-photo',
                'ai/analyze-batch' => 'ai/analyze-batch',
                'ai/apply-tags' => 'ai/apply-tags',
                'ai/apply-description' => 'ai/apply-description',
                
                // System - Queue
                'queue' => 'queue/index',
                'queue/view/<id:\d+>' => 'queue/view',
                'queue/create' => 'queue/create',
                'queue/delete/<id:\d+>' => 'queue/delete',
                'queue/retry/<id:\d+>' => 'queue/retry',
                'queue/process/<id:\d+>' => 'queue/process',
                'queue/run' => 'queue/run',
                'queue/clear-completed' => 'queue/clear-completed',
                'queue/clear-failed' => 'queue/clear-failed',
            ],
        ],
        'view' => [
            'class' => 'yii\web\View',
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                // Disable conflicting Bootstrap versions
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false,
                'yii\bootstrap4\BootstrapAsset' => false,
                'yii\bootstrap4\BootstrapPluginAsset' => false,
            ],
        ],
    ],
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/login', 'site/error'],
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],
                'matchCallback' => function ($rule, $action) {
                    // Check if user is logged in and has admin role
                    if (Yii::$app->user->isGuest) {
                        return false;
                    }
                    
                    // Check if RBAC is configured
                    if (Yii::$app->authManager) {
                        return Yii::$app->user->can('admin');
                    }
                    
                    // Fallback: allow access if no RBAC configured
                    return true;
                }
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            if (Yii::$app->user->isGuest) {
                return Yii::$app->response->redirect(['/site/login']);
            }
            throw new \yii\web\ForbiddenHttpException('Brak uprawnień do panelu administracyjnego.');
        },
    ],
    'params' => $params,
];