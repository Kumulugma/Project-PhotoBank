<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Console Commands page
 */
class ConsoleAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/console.css',
    ];
    
    public $js = [
        'js/console.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}