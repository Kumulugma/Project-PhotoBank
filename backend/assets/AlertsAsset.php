<?php
namespace backend\assets;

use yii\web\AssetBundle;

class AlertsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/alerts.css',
    ];
    
    public $js = [
        'js/components/alerts.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}