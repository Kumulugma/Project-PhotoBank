<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Frontend application asset bundle with custom modern design.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/modern-design.css',
    ];
    public $js = [
        'js/main.js',
        'js/modern-ui.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset',
    ];
}