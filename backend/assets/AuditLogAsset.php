<?php
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