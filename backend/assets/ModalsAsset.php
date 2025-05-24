<?php
namespace backend\assets;

use yii\web\AssetBundle;

class ModalsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/modals.css',
    ];
    
    public $js = [
        'js/components/modals.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}