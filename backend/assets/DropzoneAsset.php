<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Dropzone.js
 */
class DropzoneAsset extends AssetBundle
{
//    public $sourcePath = '@vendor/bower-asset/dropzone/dist';
//    public $css = [
//        'dropzone.css',
//    ];
//    public $js = [
//        'dropzone.js',
//    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    
    // Alternatywnie można użyć CDN:
    
    public $baseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/';
    public $css = [
        'dropzone.min.css',
    ];
    public $js = [
        'dropzone.min.js',
    ];
    
}