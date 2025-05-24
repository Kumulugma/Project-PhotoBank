<?php
namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;
use Yii;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/admin.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    ];
    public $js = [
        'js/admin.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset',
    ];
    
    private static $registeredAssets = [];
    
    /**
     * Registers controller-specific CSS file
     * @param View $view
     * @param string $controller
     */
    public static function registerControllerCss($view, $controller)
    {
        $cssFile = '@web/css/controllers/' . $controller . '.css';
        $cssPath = Yii::getAlias('@webroot/css/controllers/' . $controller . '.css');
        
        if (file_exists($cssPath)) {
            $view->registerCssFile($cssFile, [
                'depends' => [self::class]
            ]);
        }
    }
    
    /**
     * Registers component-specific CSS file
     * @param View $view
     * @param string $component
     */
    public static function registerComponentCss($view, $component)
    {
        $assetKey = 'css_' . $component;
        
        // Prevent duplicate registration
        if (isset(self::$registeredAssets[$assetKey])) {
            return;
        }
        
        $cssFile = '@web/css/components/' . $component . '.css';
        $cssPath = Yii::getAlias('@webroot/css/components/' . $component . '.css');
        
        if (file_exists($cssPath)) {
            $view->registerCssFile($cssFile, [
                'depends' => [self::class]
            ]);
            self::$registeredAssets[$assetKey] = true;
        }
    }
    
    /**
     * Registers controller-specific JS file
     * @param View $view
     * @param string $controller
     */
    public static function registerControllerJs($view, $controller)
    {
        $assetKey = 'js_' . $controller;
        
        // Prevent duplicate registration
        if (isset(self::$registeredAssets[$assetKey])) {
            return;
        }
        
        $jsFile = '@web/js/controllers/' . $controller . '.js';
        $jsPath = Yii::getAlias('@webroot/js/controllers/' . $controller . '.js');
        
        if (file_exists($jsPath)) {
            $view->registerJsFile($jsFile, [
                'depends' => [self::class],
                'position' => View::POS_END
            ]);
            self::$registeredAssets[$assetKey] = true;
        }
    }
    
    /**
     * Registers component-specific JS file
     * @param View $view
     * @param string $component
     */
    public static function registerComponentJs($view, $component)
    {
        $assetKey = 'js_component_' . $component;
        
        // Prevent duplicate registration
        if (isset(self::$registeredAssets[$assetKey])) {
            return;
        }
        
        $jsFile = '@web/js/components/' . $component . '.js';
        $jsPath = Yii::getAlias('@webroot/js/components/' . $component . '.js');
        
        if (file_exists($jsPath)) {
            $view->registerJsFile($jsFile, [
                'depends' => [self::class],
                'position' => View::POS_END
            ]);
            self::$registeredAssets[$assetKey] = true;
        }
    }
    
    /**
     * Registers full controller asset bundle (CSS + JS)
     * @param View $view
     * @param string $controller
     */
    public static function registerControllerAssets($view, $controller)
    {
        self::registerControllerCss($view, $controller);
        self::registerControllerJs($view, $controller);
    }

    /**
     * Registers settings controller assets (includes multiple controllers)
     * @param View $view
     */
    public static function registerSettingsAssets($view)
    {
        self::registerControllerCss($view, 'settings');
        self::registerControllerJs($view, 'settings');
        
        // Additional settings-related assets
        self::registerComponentCss($view, 'forms');
        self::registerComponentCss($view, 'alerts');
        self::registerComponentCss($view, 'modals');
    }
    
    /**
     * Registers essential components for most pages
     * @param View $view
     */
    public static function registerEssentialComponents($view)
    {
        // Register core components that are used on most pages
        self::registerComponentJs($view, 'alerts');
        self::registerComponentJs($view, 'forms');  
        self::registerComponentJs($view, 'modals');
        self::registerComponentJs($view, 'tables');
    }
    
    /**
     * Clear registered assets cache (useful for development)
     */
    public static function clearRegisteredAssets()
    {
        self::$registeredAssets = [];
    }
}