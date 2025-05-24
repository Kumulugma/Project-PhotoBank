<?php
namespace backend\assets;

use yii\web\AssetBundle;

class NavbarAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/navbar.css',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}