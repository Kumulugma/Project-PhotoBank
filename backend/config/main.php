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
    'language' => 'pl-PL',
    'sourceLanguage' => 'en-US',
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
                'photos' => 'photos/index',
                'photos/queue' => 'photos/queue',
                'photos/upload' => 'photos/upload',
                'categories' => 'categories/index',
                'tags' => 'tags/index',
                'users' => 'users/index',
                'settings' => 'settings/index',
                's3' => 's3/index',
                's3/<action>' => 's3/<action>',
                'thumbnails' => 'thumbnail-size/index',
                'thumbnails/<action>' => 'thumbnail-size/<action>',
                'thumbnails/<action>/<id:\d+>' => 'thumbnail-size/<action>',
                'watermark' => 'watermark/index',
                'watermark/<action>' => 'watermark/<action>',
                'ai' => 'ai/index',
                'ai/<action>' => 'ai/<action>',
                'ai/<action>/<id:\d+>' => 'ai/<action>',
                'queue' => 'queue/index',
                'aws-cost' => 'aws-cost/index',
                'aws-cost/<action>' => 'aws-cost/<action>',
            ],
        ],
        'view' => [
            'class' => 'yii\web\View',
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false,
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'defaultTimeZone' => 'Europe/Warsaw',
            'timeZone' => 'Europe/Warsaw',
            'locale' => 'pl-PL',
            'dateFormat' => 'dd.MM.yyyy',
            'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
            'timeFormat' => 'HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'PLN',
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
                    if (Yii::$app->user->isGuest) {
                        return false;
                    }

                    if (Yii::$app->authManager) {
                        return Yii::$app->user->can('admin');
                    }

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
