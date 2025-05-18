<?php
namespace frontend\assets;

use yii\web\AssetBundle;
/**
 * Font assets for Google Fonts
 */
class FontAsset extends AssetBundle
{
    public $css = [
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    ];
    
    public $cssOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}
