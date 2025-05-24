<?php
namespace backend\assets;

use yii\web\AssetBundle;

class SettingsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/settings.css',
        'css/controllers/watermark.css',
    ];
    
    public $js = [
        'js/controllers/settings.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}