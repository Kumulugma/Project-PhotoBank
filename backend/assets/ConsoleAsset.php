<?php
namespace backend\assets;

use yii\web\AssetBundle;

class ConsoleAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/console.css',
    ];
    
    public $js = [
        'js/controllers/console.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}