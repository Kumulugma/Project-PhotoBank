<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - PersonalPhotoBank Admin</title>
    <?php $this->head() ?>
</head>
<body class="admin-layout">
<?php $this->beginBody() ?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><?= Html::a('PhotoBank Admin', ['/site/index'], ['class' => 'sidebar-brand']) ?></h3>
        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="glyphicon glyphicon-menu-hamburger"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav">
            <li class="nav-item">
                <?= Html::a('<i class="glyphicon glyphicon-dashboard"></i> <span>Dashboard</span>', ['/site/index'], ['class' => 'nav-link']) ?>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle">
                    <i class="glyphicon glyphicon-picture"></i> <span>Photos</span>
                    <i class="glyphicon glyphicon-chevron-down arrow"></i>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('Active Photos', ['/photos/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Photo Queue', ['/photos/queue'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Upload Photos', ['/photos/upload'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle">
                    <i class="glyphicon glyphicon-tags"></i> <span>Classifications</span>
                    <i class="glyphicon glyphicon-chevron-down arrow"></i>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('Categories', ['/categories/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Tags', ['/tags/index'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
            
            <li class="nav-item">
                <?= Html::a('<i class="glyphicon glyphicon-user"></i> <span>Users</span>', ['/users/index'], ['class' => 'nav-link']) ?>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle">
                    <i class="glyphicon glyphicon-cog"></i> <span>System</span>
                    <i class="glyphicon glyphicon-chevron-down arrow"></i>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('Thumbnails', ['/thumbnails/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Watermark', ['/watermark/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('S3 Settings', ['/s3/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('AI Settings', ['/ai/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Job Queue', ['/queue/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('Settings', ['/settings/index'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
        </ul>
    </nav>
</div>

<!-- Main content wrapper -->
<div class="main-wrapper">
    <!-- Top navbar -->
    <nav class="navbar navbar-main">
        <div class="navbar-header">
            <button type="button" class="btn btn-link sidebar-toggle-btn" onclick="toggleSidebar()">
                <i class="glyphicon glyphicon-menu-hamburger"></i>
            </button>
            
            <div class="navbar-title">
                <h4><?= Html::encode($this->title) ?></h4>
            </div>
        </div>
        
        <div class="navbar-right">
            <div class="dropdown user-dropdown">
                <button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
                    <i class="glyphicon glyphicon-user"></i>
                    <?= Yii::$app->user->identity->username ?>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><?= Html::a('<i class="glyphicon glyphicon-user"></i> Profile', ['/users/view', 'id' => Yii::$app->user->id]) ?></li>
                    <li class="divider"></li>
                    <li>
                        <?= Html::beginForm(['/site/logout'], 'post') ?>
                        <?= Html::submitButton('<i class="glyphicon glyphicon-log-out"></i> Logout', ['class' => 'btn btn-link logout-btn']) ?>
                        <?= Html::endForm() ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Page content -->
    <div class="page-content">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'options' => ['class' => 'custom-breadcrumb'],
        ]) ?>
        
        <?= Alert::widget(['options' => ['class' => 'custom-alert']]) ?>
        
        <div class="content-container">
            <?= $content ?>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.querySelector('.main-wrapper').classList.toggle('sidebar-collapsed');
}

// Initialize sidebar state
document.addEventListener('DOMContentLoaded', function() {
    // Submenu toggle functionality
    document.querySelectorAll('.submenu-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('open');
        });
    });
    
    // Auto-expand parent menu for active page
    const activeLink = document.querySelector('.nav-link.active');
    if (activeLink) {
        const parentSubmenu = activeLink.closest('.has-submenu');
        if (parentSubmenu) {
            parentSubmenu.classList.add('open');
        }
    }
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>