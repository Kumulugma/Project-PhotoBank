<?php
namespace backend\assets;

use yii\web\AssetBundle;

class CategoriesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/categories.css',
    ];
    
    public $js = [
        'js/controllers/categories.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}