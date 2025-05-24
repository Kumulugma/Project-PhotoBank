<?php
namespace backend\assets;

use yii\web\AssetBundle;

class QueueAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/queue.css',
    ];
    
    public $js = [
        'js/controllers/queue.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}
