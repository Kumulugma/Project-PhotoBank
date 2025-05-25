<?php
namespace backend\assets;

use yii\web\AssetBundle;

class TagsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/tags.css',
    ];
    
    public $js = [
        'js/controllers/tags.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
        'yii\web\JqueryAsset',
    ];
}
