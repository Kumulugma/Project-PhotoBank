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
        'integrity' => 'sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==',
        'crossorigin' => 'anonymous',
    ];
}
