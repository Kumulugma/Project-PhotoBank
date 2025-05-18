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
<body>
<?php $this->beginBody() ?>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar bg-dark" id="sidebar">
        <div class="sidebar-header p-3 border-bottom">
            <?= Html::a('Zasobnik B', ['/site/index'], ['class' => 'text-white text-decoration-none h5']) ?>
            <button type="button" class="btn btn-sm btn-outline-light d-lg-none ms-auto" id="sidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav p-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <?= Html::a('<i class="fas fa-tachometer-alt me-2"></i>Dashboard', ['/site/index'], ['class' => 'nav-link text-white']) ?>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="collapse" data-bs-target="#photosMenu">
                        <i class="fas fa-images me-2"></i>Zdjęcia
                    </a>
                    <div class="collapse" id="photosMenu">
                        <div class="ms-3">
                            <?= Html::a('<i class="fas fa-list me-2"></i>Wszystkie', ['/photos/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-clock me-2"></i>Poczekalnia', ['/photos/queue'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-upload me-2"></i>Prześlij', ['/photos/upload'], ['class' => 'nav-link text-white-50']) ?>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="collapse" data-bs-target="#orgMenu">
                        <i class="fas fa-tags me-2"></i>Organizacja
                    </a>
                    <div class="collapse" id="orgMenu">
                        <div class="ms-3">
                            <?= Html::a('<i class="fas fa-folder me-2"></i>Kategorie', ['/categories/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-hashtag me-2"></i>Tagi', ['/tags/index'], ['class' => 'nav-link text-white-50']) ?>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item">
                    <?= Html::a('<i class="fas fa-users me-2"></i>Użytkownicy', ['/users/index'], ['class' => 'nav-link text-white']) ?>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="collapse" data-bs-target="#systemMenu">
                        <i class="fas fa-cogs me-2"></i>System
                    </a>
                    <div class="collapse" id="systemMenu">
                        <div class="ms-3">
                            <?= Html::a('<i class="fas fa-image me-2"></i>Miniatury', ['/thumbnails/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-tint me-2"></i>Znak wodny', ['/watermark/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fab fa-aws me-2"></i>S3 Storage', ['/s3/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-robot me-2"></i>AI Integration', ['/ai/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-list-alt me-2"></i>Kolejka zadań', ['/queue/index'], ['class' => 'nav-link text-white-50']) ?>
                            <?= Html::a('<i class="fas fa-sliders-h me-2"></i>Ustawienia', ['/settings/index'], ['class' => 'nav-link text-white-50']) ?>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main content -->
    <main class="main-content flex-grow-1">
        <!-- Header -->
        <header class="header bg-white shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
                </div>
                
                <div class="d-flex align-items-center">
                    <?php
                    $queuedPhotos = \common\models\Photo::find()
                        ->where(['status' => \common\models\Photo::STATUS_QUEUE])
                        ->count();
                    $pendingJobs = \common\models\QueuedJob::find()
                        ->where(['status' => \common\models\QueuedJob::STATUS_PENDING])
                        ->count();
                    ?>
                    
                    <?php if ($queuedPhotos > 0): ?>
                    <a href="<?= Url::to(['/photos/queue']) ?>" class="btn btn-sm btn-outline-warning me-2 position-relative">
                        <i class="fas fa-clock"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                            <?= $queuedPhotos ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($pendingJobs > 0): ?>
                    <a href="<?= Url::to(['/queue/index']) ?>" class="btn btn-sm btn-outline-info me-2 position-relative">
                        <i class="fas fa-tasks"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $pendingJobs ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= 'http://' . str_replace('admin.', '', $_SERVER['HTTP_HOST']) ?>" 
                       class="btn btn-sm btn-outline-secondary me-3" target="_blank" title="Przejdź do frontendu">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                                    'class' => 'dropdown-item text-danger border-0 bg-transparent',
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
        <nav class="breadcrumb-nav p-3 bg-light">
            <ol class="breadcrumb mb-0">
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
        <div class="content p-4">
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>
</div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<?php $this->endBody() ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    
    // Toggle sidebar
    toggleBtn?.addEventListener('click', function() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
    
    // Close sidebar
    closeBtn?.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    // Close sidebar when clicking overlay
    overlay?.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    // Set active navigation
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/.*\//, ''))) {
            link.classList.add('active');
            
            // Expand parent menu
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
            }
        }
    });
});
</script>

</body>
</html>
<?php $this->endPage(); ?>