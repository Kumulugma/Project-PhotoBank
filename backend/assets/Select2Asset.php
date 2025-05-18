<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Select2
 */
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@npm/select2/dist';
    public $css = [
        'css/select2.min.css',
        'css/select2-bootstrap4.min.css',
    ];
    public $js = [
        'js/select2.full.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}