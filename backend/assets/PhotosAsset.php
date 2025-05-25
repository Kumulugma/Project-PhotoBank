<?php
namespace backend\assets;

use yii\web\AssetBundle;

class PhotosAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/photos.css',
    ];
    
    public $js = [
        'js/controllers/photos.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}