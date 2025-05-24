<?php
namespace backend\assets;

use yii\web\AssetBundle;

class UsersAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/users.css',
    ];
    
    public $js = [
        'js/controllers/users.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}