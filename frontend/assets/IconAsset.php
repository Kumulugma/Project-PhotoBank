<?php
namespace frontend\assets;

use yii\web\AssetBundle;
class IconAsset extends AssetBundle
{
    public $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    ];
    
    public $cssOptions = [
        'position' => \yii\web\View::POS_HEAD,
        'integrity' => 'sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT',
        'crossorigin' => 'anonymous',
    ];
}