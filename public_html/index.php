<?php
// Plik wejściowy dla frontendu

// Ustawienia debugowania - na produkcji powinny być wyłączone
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

// Ładowanie autoloadera Composer
require __DIR__ . '/../vendor/autoload.php';

// Ładowanie klasy Yii
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// Ładowanie konfiguracji bootstrap
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../frontend/config/bootstrap.php';

// Łączenie konfiguracji
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../frontend/config/main.php',
    require __DIR__ . '/../frontend/config/main-local.php'
);

// Tworzenie i uruchamianie aplikacji
(new yii\web\Application($config))->run();