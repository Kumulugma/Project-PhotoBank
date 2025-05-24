<?php
// backend/assets/PhotosAsset.php
namespace backend\assets;

use yii\web\AssetBundle;

class PhotosAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/photos.css',
    ];
    
    public $js = [
        'js/controllers/photos.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}

// backend/assets/CategoriesAsset.php
namespace backend\assets;

use yii\web\AssetBundle;

class CategoriesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/categories.css',
    ];
    
    public $js = [
        'js/controllers/categories.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}

// backend/assets/TagsAsset.php
namespace backend\assets;

use yii\web\AssetBundle;

class TagsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/tags.css',
    ];
    
    public $js = [
        'js/controllers/tags.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}

// backend/assets/AuditLogAsset.php
namespace backend\assets;

use yii\web\AssetBundle;

class AuditLogAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/audit-log.css',
    ];
    
    public $js = [
        'js/controllers/audit-log.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}

// backend/assets/AiAsset.php
namespace backend\assets;

use yii\web\AssetBundle;

class AiAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/controllers/ai.css',
    ];
    
    public $js = [
        'js/controllers/ai.js',
    ];
    
    public $depends = [
        'backend\assets\AppAsset',
    ];
}