<?php
namespace backend\assets;

use yii\web\AssetBundle;

class TablesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/tables.css',
    ];
    
    public $js = [
        'js/components/tables.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}