<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Frontend application asset bundle with custom modern design.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/modern-design.css',
    ];
    
    public $js = [
        'js/modern-main.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        // Note: No Bootstrap dependencies - we use custom CSS
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // Add conditional CSS for older browsers
        $this->cssOptions = [
            'condition' => 'lte IE 9',
        ];
    }
}