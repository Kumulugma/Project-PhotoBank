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
        'css/admin.css', // Główny plik CSS
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
        $cssFile = '@web/css/components/' . $component . '.css';
        $cssPath = Yii::getAlias('@webroot/css/components/' . $component . '.css');
        
        if (file_exists($cssPath)) {
            $view->registerCssFile($cssFile, [
                'depends' => [self::class]
            ]);
        }
    }
}