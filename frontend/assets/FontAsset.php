<?php
namespace frontend\assets;

use yii\web\AssetBundle;
/**
 * Font assets for Google Fonts - Updated with new fonts
 */
class FontAsset extends AssetBundle
{
    public $css = [
        'https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&family=Quicksand:wght@300;400;500;600;700&display=swap',
    ];
    
    public $cssOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}