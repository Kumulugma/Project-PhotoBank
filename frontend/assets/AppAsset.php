<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Główny asset bundle dla frontendu Personal Photo Bank
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
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset', // Dodajemy jQuery dla kompatybilności
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // Dodaj cache busting tylko w trybie produkcyjnym
        if (!YII_DEBUG) {
            $this->cssOptions['v'] = filemtime($this->basePath . '/css/modern-design.css');
            $this->jsOptions['v'] = filemtime($this->basePath . '/js/main.js');
        }
    }
    
    /**
     * Register critical CSS inline
     */
    public static function registerCriticalCss($view)
    {
        $criticalCss = <<<CSS
        /* Critical CSS - Above the fold */
        :root {
            --primary-color: #6366f1;
            --primary-light: #a5b4fc;
            --primary-dark: #4f46e5;
            --secondary-color: #ec4899;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --background: #ffffff;
            --surface: #f9fafb;
            --border: #e5e7eb;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            --animation: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--background);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .header {
            background: var(--background);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--animation);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 0 0 4px 4px;
            z-index: 1000;
            transition: var(--animation);
        }
        
        .skip-link:focus {
            top: 0;
        }
CSS;
        
        $view->registerCss($criticalCss, [], 'critical-css');
    }
}