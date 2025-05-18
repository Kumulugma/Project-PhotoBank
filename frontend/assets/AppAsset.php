<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main Application Asset Bundle with updated dependencies
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/variables.css',
        'css/base.css',
        'css/components.css',
        'css/layout.css',
        'css/responsive.css',
    ];
    
    public $js = [
        'js/main.js',
        'js/photo-gallery.js',
        'js/modal.js',
        'js/search.js', // Add search-specific JS
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'frontend\assets\IconAsset',
        'frontend\assets\FontAsset',
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // Add cache busting in production
        if (!YII_DEBUG) {
            $this->cssOptions['v'] = time();
            $this->jsOptions['v'] = time();
        }
    }
}