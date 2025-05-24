<?php
namespace backend\assets;

use yii\web\AssetBundle;

class WatermarkAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/watermark.css',
    ];
    
    public $js = [
        'js/controllers/watermark.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}