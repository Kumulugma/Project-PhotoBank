<?php

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
        ],
        's3' => [
            'class' => 'common\components\S3Component',
        ],
        'imageProcessor' => [
            'class' => 'common\components\ImageProcessor',
        ],
        'rbac' => [
            'class' => 'common\components\RbacComponent',
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i',
            'timeFormat' => 'php:H:i',
            'defaultTimeZone' => 'Europe/Warsaw',
            'thousandSeparator' => ' ',
            'decimalSeparator' => ',',
            'currencyCode' => 'PLN',
        ],
        'i18n' => [
            'translations' => [
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
    ],
    // Moduły powinny być zdefiniowane na poziomie aplikacji, nie tutaj
    // 'modules' => [
    //     'api' => [
    //         'class' => 'common\modules\api\Module',
    //     ],
    // ],
    'bootstrap' => ['log'],
];
