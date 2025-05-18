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
    'modules' => [
        'api' => [
            'class' => 'modules\api\Module',
        ],
    ],
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
                
                // System
                'settings' => 'settings/index',
                's3' => 's3/index',
                'thumbnails' => 'thumbnails/index',
                'watermark' => 'watermark/index',
                'ai' => 'ai/index',
                'queue' => 'queue/index',
            ],
        ],
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/admin',
                'baseUrl' => '@web/themes/admin',
                'pathMap' => [
                    '@app/views' => '@app/themes/admin',
                ],
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
            if (Yii::$app->user->isGuest) {
                return Yii::$app->response->redirect(['/site/login']);
            }
            throw new \yii\web\ForbiddenHttpException('Brak uprawnień do panelu administracyjnego.');
        },
    ],
    'params' => $params,
];