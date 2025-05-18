<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'name' => 'PersonalPhotoBank Admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
            'class' => 'modules\api\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
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
                '' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                
                // Zdjęcia
                'photos' => 'photos/index',
                'photos/view/<id:\d+>' => 'photos/view',
                'photos/create' => 'photos/create',
                'photos/update/<id:\d+>' => 'photos/update',
                'photos/delete/<id:\d+>' => 'photos/delete',
                'photos/queue' => 'photos/queue',
                'photos/approve/<id:\d+>' => 'photos/approve',
                'photos/approve-batch' => 'photos/approve-batch',
                'photos/upload' => 'photos/upload',
                'photos/import' => 'photos/import',
                
                // Kategorie
                'categories' => 'categories/index',
                'categories/view/<id:\d+>' => 'categories/view',
                'categories/create' => 'categories/create',
                'categories/update/<id:\d+>' => 'categories/update',
                'categories/delete/<id:\d+>' => 'categories/delete',
                
                // Tagi
                'tags' => 'tags/index',
                'tags/view/<id:\d+>' => 'tags/view',
                'tags/create' => 'tags/create',
                'tags/update/<id:\d+>' => 'tags/update',
                'tags/delete/<id:\d+>' => 'tags/delete',
                
                // Użytkownicy
                'users' => 'users/index',
                'users/view/<id:\d+>' => 'users/view',
                'users/create' => 'users/create',
                'users/update/<id:\d+>' => 'users/update',
                'users/delete/<id:\d+>' => 'users/delete',
                
                // Ustawienia
                'settings' => 'settings/index',
                'settings/update' => 'settings/update',
                
                // S3
                's3/settings' => 's3/settings',
                's3/test' => 's3/test',
                's3/sync' => 's3/sync',
                
                // Miniatury
                'thumbnails' => 'thumbnails/index',
                'thumbnails/create' => 'thumbnails/create',
                'thumbnails/update/<id:\d+>' => 'thumbnails/update',
                'thumbnails/delete/<id:\d+>' => 'thumbnails/delete',
                'thumbnails/regenerate' => 'thumbnails/regenerate',
                
                // Znak wodny
                'watermark' => 'watermark/index',
                
                // AI
                'ai/settings' => 'ai/settings',
                'ai/analyze/<id:\d+>' => 'ai/analyze',
                'ai/analyze-batch' => 'ai/analyze-batch',
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
                    return Yii::$app->user->can('managePhotos');
                }
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            return Yii::$app->response->redirect(['site/login']);
        },
    ],
    'params' => $params,
];