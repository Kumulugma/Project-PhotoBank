<?php
namespace backend\assets;

use yii\web\AssetBundle;

class AiAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/ai.css',
    ];
    
    public $js = [
        'js/controllers/ai.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}
