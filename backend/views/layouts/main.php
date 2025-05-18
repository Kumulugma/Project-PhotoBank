<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - Zasobnik B - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php $this->head() ?>
</head>
<body class="admin-layout">
<?php $this->beginBody() ?>

<!-- Sidebar -->
<aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><?= Html::a('Zasobnik B', ['/site/index'], ['class' => 'sidebar-brand']) ?></h3>
        <button type="button" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav admin-scrollbar">
        <ul class="nav-list">
            <li class="nav-item">
                <?= Html::a('<i class="fas fa-tachometer-alt"></i><span>Dashboard</span>', ['/site/index'], ['class' => 'nav-link']) ?>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-images"></i><span>Zdjęcia</span>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('<i class="fas fa-list"></i><span>Wszystkie zdjęcia</span>', ['/photos/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-clock"></i><span>Poczekalnia</span>', ['/photos/queue'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-upload"></i><span>Prześlij zdjęcia</span>', ['/photos/upload'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-tags"></i><span>Organizacja</span>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('<i class="fas fa-folder"></i><span>Kategorie</span>', ['/categories/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-hashtag"></i><span>Tagi</span>', ['/tags/index'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
            
            <li class="nav-item">
                <?= Html::a('<i class="fas fa-users"></i><span>Użytkownicy</span>', ['/users/index'], ['class' => 'nav-link']) ?>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-cogs"></i><span>System</span>
                </a>
                <ul class="submenu">
                    <li><?= Html::a('<i class="fas fa-image"></i><span>Miniatury</span>', ['/thumbnails/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-tint"></i><span>Znak wodny</span>', ['/watermark/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fab fa-aws"></i><span>S3 Storage</span>', ['/s3/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-robot"></i><span>AI Integration</span>', ['/ai/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-list-alt"></i><span>Kolejka zadań</span>', ['/queue/index'], ['class' => 'nav-link']) ?></li>
                    <li><?= Html::a('<i class="fas fa-sliders-h"></i><span>Ustawienia</span>', ['/settings/index'], ['class' => 'nav-link']) ?></li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>

<!-- Main content wrapper -->
<div class="admin-main">
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-header-left">
            <button type="button" class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
        </div>
        
        <div class="admin-header-right">
            <div class="header-actions">
                <!-- Notifications -->
                <?php
                $queuedPhotos = \common\models\Photo::find()
                    ->where(['status' => \common\models\Photo::STATUS_QUEUE])
                    ->count();
                $pendingJobs = \common\models\QueuedJob::find()
                    ->where(['status' => \common\models\QueuedJob::STATUS_PENDING])
                    ->count();
                ?>
                
                <?php if ($queuedPhotos > 0): ?>
                <a href="<?= Url::to(['/photos/queue']) ?>" class="header-notification" title="Zdjęcia w poczekalni">
                    <i class="fas fa-clock"></i>
                    <span class="badge"><?= $queuedPhotos ?></span>
                </a>
                <?php endif; ?>
                
                <?php if ($pendingJobs > 0): ?>
                <a href="<?= Url::to(['/queue/index']) ?>" class="header-notification" title="Zadania w kolejce">
                    <i class="fas fa-tasks"></i>
                    <span class="badge"><?= $pendingJobs ?></span>
                </a>
                <?php endif; ?>
                
                <!-- Frontend link -->
                <a href="<?= 'http://' . str_replace('admin.', '', $_SERVER['HTTP_HOST']) ?>" 
                   class="header-notification" title="Przejdź do frontendu" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            
            <!-- User dropdown -->
            <div class="user-dropdown admin-dropdown">
                <button class="btn" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        
                    </div>
                    
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="<?= Url::to(['/users/view', 'id' => Yii::$app->user->id]) ?>">
                        <i class="fas fa-user me-2"></i>Profil
                    </a>
                    <a class="dropdown-item" href="<?= Url::to(['/settings/index']) ?>">
                        <i class="fas fa-cog me-2"></i>Ustawienia
                    </a>
                    <div class="dropdown-divider"></div>
                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'dropdown-item-form']) ?>
                    <?= Html::submitButton('<i class="fas fa-sign-out-alt me-2"></i>Wyloguj', [
                        'class' => 'dropdown-item logout-btn',
                        'style' => 'background: none; border: none; width: 100%; text-align: left; color: #dc3545;'
                    ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Breadcrumbs -->
    <?php if (isset($this->params['breadcrumbs'])): ?>
    <nav class="admin-content">
        <ol class="admin-breadcrumb">
            <?php foreach ($this->params['breadcrumbs'] as $index => $crumb): ?>
                <?php if ($index === array_key_last($this->params['breadcrumbs'])): ?>
                    <li class="breadcrumb-item active"><?= Html::encode($crumb) ?></li>
                <?php elseif (is_array($crumb)): ?>
                    <li class="breadcrumb-item">
                        <?= Html::a(Html::encode($crumb['label']), $crumb['url']) ?>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <?= Html::a(Html::encode($crumb), '#') ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php endif; ?>
    
    <!-- Page content -->
    <main class="admin-content">
        <?= Alert::widget([
            'options' => ['class' => 'admin-alert'],
            'alertTypes' => [
                'error' => 'admin-alert-danger',
                'danger' => 'admin-alert-danger',
                'success' => 'admin-alert-success',
                'info' => 'admin-alert-info',
                'warning' => 'admin-alert-warning'
            ]
        ]) ?>
        
        <div class="content-wrapper">
            <?= $content ?>
        </div>
    </main>
</div>

<!-- Loading overlay template -->
<script type="text/template" id="loading-template">
    <div class="admin-loader">
        <div class="loader-spinner"></div>
    </div>
</script>

<?php $this->endBody() ?>

<!-- Initialize admin panel -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set active navigation based on current page
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/^.*\/admin\//, ''))) {
            link.classList.add('active');
            
            // Expand parent submenu
            const submenu = link.closest('.submenu');
            if (submenu) {
                submenu.closest('.nav-item').classList.add('open');
            }
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.admin-alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});
</script>

</body>
</html>
<?php $this->endPage(); ?>