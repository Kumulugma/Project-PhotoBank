<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Inline critical CSS asset for above-the-fold content
 */
class CriticalCssAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/critical.css',
    ];
    
    public $cssOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'media' => 'all',
    ];
}
