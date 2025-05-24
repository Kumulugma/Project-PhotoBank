<?php
namespace backend\assets;

use yii\web\AssetBundle;

class ButtonsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/components/buttons.css',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}
