<?php
namespace backend\assets;

use yii\web\AssetBundle;

class FormsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/forms.css',
    ];
    
    public $js = [
        'js/components/forms.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}