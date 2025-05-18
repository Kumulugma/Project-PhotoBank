<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Dropzone.js
 */
class DropzoneAsset extends AssetBundle
{
    public $sourcePath = '@npm/dropzone/dist';
    public $css = [
        'min/dropzone.min.css',
    ];
    public $js = [
        'min/dropzone.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}