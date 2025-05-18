<?php
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

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <?= Html::a('Zasobnik B', ['/site/index'], ['class' => 'sidebar-brand']) ?>
            <button type="button" class="sidebar-toggle d-lg-none" id="sidebarToggle">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <?= Html::a('<i class="fas fa-tachometer-alt"></i> Dashboard', ['/site/index'], ['class' => 'nav-link']) ?>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#photosSubmenu" role="button">
                        <i class="fas fa-images"></i> Zdjęcia
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse" id="photosSubmenu">
                        <div class="submenu">
                            <?= Html::a('<i class="fas fa-list"></i> Wszystkie zdjęcia', ['/photos/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-clock"></i> Poczekalnia', ['/photos/queue'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-upload"></i> Prześlij zdjęcia', ['/photos/upload'], ['class' => 'nav-link']) ?>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#orgSubmenu" role="button">
                        <i class="fas fa-tags"></i> Organizacja
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse" id="orgSubmenu">
                        <div class="submenu">
                            <?= Html::a('<i class="fas fa-folder"></i> Kategorie', ['/categories/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-hashtag"></i> Tagi', ['/tags/index'], ['class' => 'nav-link']) ?>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item">
                    <?= Html::a('<i class="fas fa-users"></i> Użytkownicy', ['/users/index'], ['class' => 'nav-link']) ?>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#systemSubmenu" role="button">
                        <i class="fas fa-cogs"></i> System
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse" id="systemSubmenu">
                        <div class="submenu">
                            <?= Html::a('<i class="fas fa-image"></i> Miniatury', ['/thumbnails/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-tint"></i> Znak wodny', ['/watermark/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fab fa-aws"></i> S3 Storage', ['/s3/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-robot"></i> AI Integration', ['/ai/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-list-alt"></i> Kolejka zadań', ['/queue/index'], ['class' => 'nav-link']) ?>
                            <?= Html::a('<i class="fas fa-sliders-h"></i> Ustawienia', ['/settings/index'], ['class' => 'nav-link']) ?>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link d-lg-none me-3" id="mobileToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
                </div>
                
                <div class="d-flex align-items-center">
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
                    <a href="<?= Url::to(['/photos/queue']) ?>" class="btn btn-outline-primary me-2 position-relative">
                        <i class="fas fa-clock"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                            <?= $queuedPhotos ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($pendingJobs > 0): ?>
                    <a href="<?= Url::to(['/queue/index']) ?>" class="btn btn-outline-info me-2 position-relative">
                        <i class="fas fa-tasks"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $pendingJobs ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Frontend link -->
                    <a href="<?= 'http://' . str_replace('admin.', '', $_SERVER['HTTP_HOST']) ?>" 
                       class="btn btn-outline-secondary me-3" target="_blank" title="Przejdź do frontendu">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    
                    <!-- User dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= Html::encode(Yii::$app->user->identity->username) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= Url::to(['/users/view', 'id' => Yii::$app->user->id]) ?>">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= Url::to(['/settings/index']) ?>">
                                    <i class="fas fa-cog me-2"></i>Ustawienia
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?= Html::beginForm(['/site/logout'], 'post') ?>
                                <?= Html::submitButton('<i class="fas fa-sign-out-alt me-2"></i>Wyloguj', [
                                    'class' => 'dropdown-item text-danger',
                                    'style' => 'background: none; border: none; width: 100%;'
                                ]) ?>
                                <?= Html::endForm() ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Breadcrumbs -->
        <?php if (isset($this->params['breadcrumbs'])): ?>
        <nav aria-label="breadcrumb" class="admin-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><?= Html::a('<i class="fas fa-home"></i>', ['/site/index']) ?></li>
                <?php foreach ($this->params['breadcrumbs'] as $index => $crumb): ?>
                    <?php if ($index === array_key_last($this->params['breadcrumbs'])): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= Html::encode($crumb) ?></li>
                    <?php elseif (is_array($crumb)): ?>
                        <li class="breadcrumb-item">
                            <?= Html::a(Html::encode($crumb['label']), $crumb['url']) ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <?= Html::encode($crumb) ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        
        <!-- Content -->
        <div class="admin-content">
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>
</div>

<!-- Overlay for mobile -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<?php $this->endBody() ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    mobileToggle?.addEventListener('click', function() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
    });
    
    sidebarToggle?.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
    
    overlay?.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
    
    // Set active navigation
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link[href]');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/.*\//, ''))) {
            link.classList.add('active');
            
            // Expand parent collapse
            const collapse = link.closest('.collapse');
            if (collapse) {
                collapse.classList.add('show');
                const button = document.querySelector(`[data-bs-toggle="collapse"][href="#${collapse.id}"]`);
                if (button) {
                    button.classList.remove('collapsed');
                }
            }
        }
    });
});
</script>

</body>
</html>
<?php $this->endPage(); ?>