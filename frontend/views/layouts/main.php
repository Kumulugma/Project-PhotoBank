<?php
/* @var $this yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\assets\IconAsset;
use frontend\assets\FontAsset;

AppAsset::register($this);
IconAsset::register($this);
//FontAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="no-js">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366f1">
    <meta name="description" content="Zasobnik B - Archiwum">
    <meta name="robots" content="noindex, nofollow">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: 'Zasobnik B') ?> - Zasobnik B</title>
    
    
    <?php $this->head() ?>
    
    <!-- Remove no-js class if JavaScript is enabled -->
    <script>document.documentElement.classList.remove('no-js');</script>
</head>
<body class="<?= Yii::$app->user->isGuest ? 'guest' : 'authenticated' ?>">
<?php $this->beginBody() ?>

<!-- Skip to main content link for accessibility -->
<a href="#main-content" class="skip-link">Przejdź do głównej treści</a>

<!-- Header -->
<header class="header" role="banner">
    <div class="container">
        <nav class="navbar" role="navigation" aria-label="główne menu">
            <!-- Logo -->
            <?= Html::a('Zasobnik B', ['/site/index'], [
                'class' => 'logo',
                'aria-label' => 'Zasobnik B - strona główna'
            ]) ?>
            
            <!-- Desktop Navigation -->
            <ul class="nav-menu" id="navMenu" role="menubar">
                <li role="none">
                    <?= Html::a('<i class="fas fa-home" aria-hidden="true"></i> <span>Strona główna</span>', ['/site/index'], [
                        'class' => 'nav-link',
                        'role' => 'menuitem',
                        'encode' => false
                    ]) ?>
                </li>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-images" aria-hidden="true"></i> <span>Galeria</span>', ['/gallery/index'], [
                            'class' => 'nav-link',
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-search" aria-hidden="true"></i> <span>Wyszukiwanie</span>', ['/search/index'], [
                            'class' => 'nav-link',
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php endif; ?>
                
                <!-- User menu -->
                <?php if (Yii::$app->user->isGuest): ?>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-sign-in-alt" aria-hidden="true"></i> <span>Logowanie</span>', ['/site/login'], [
                            'class' => 'nav-link',
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php else: ?>
                    <li class="nav-dropdown" role="none">
                        <button type="button" class="nav-link dropdown-toggle" 
                                id="userDropdown" 
                                aria-haspopup="true" 
                                aria-expanded="false"
                                data-bs-toggle="dropdown">
                            <i class="fas fa-user" aria-hidden="true"></i> 
                            <span><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu" role="menu" aria-labelledby="userDropdown">
                            <?php if (Yii::$app->user->can('managePhotos')): ?>
                                <?= Html::a('<i class="fas fa-cog" aria-hidden="true"></i> Panel administratora', 
                                    '//system.zasobnik.be', [
                                    'class' => 'dropdown-item',
                                    'target' => '_blank',
                                    'role' => 'menuitem',
                                    'rel' => 'noopener noreferrer',
                                    'encode' => false
                                ]) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="fas fa-sign-out-alt" aria-hidden="true"></i> Wyloguj', ['/site/logout'], [
                                'class' => 'dropdown-item',
                                'data-method' => 'post',
                                'role' => 'menuitem',
                                'encode' => false
                            ]) ?>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Mobile menu toggle -->
            <button type="button" 
                    class="mobile-menu-toggle" 
                    id="mobileToggle"
                    aria-label="Przełącz menu mobilne"
                    aria-expanded="false"
                    aria-controls="navMenu">
                <span class="hamburger">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </span>
            </button>
        </nav>
    </div>
</header>

<!-- Main Content -->
<main class="main" id="main-content" role="main">
    <!-- Breadcrumbs -->
    <?php if (!empty($this->params['breadcrumbs'])): ?>
        <nav class="breadcrumbs" aria-label="lokalizacja na stronie">
            <div class="container">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['/site/index']) ?>">
                            <i class="fas fa-home" aria-hidden="true"></i>
                            <span class="sr-only">Strona główna</span>
                        </a>
                    </li>
                    <?php foreach ($this->params['breadcrumbs'] as $breadcrumb): ?>
                        <?php if (is_array($breadcrumb)): ?>
                            <li class="breadcrumb-item">
                                <?= Html::a($breadcrumb['label'], $breadcrumb['url']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= Html::encode($breadcrumb) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Page Content -->
    <div class="page-content">
        <?= $content ?>
    </div>
</main>

<!-- Footer -->
<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                
            </div>
            
            <?php if (!Yii::$app->user->isGuest): ?>
                <div class="footer-section">
                   
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>
                    &copy; <?= date('Y') ?> Zasobnik B. Wszystkie prawa zastrzeżone.
                </p>
                <p class="powered-by">
                    Wspierane przez: 
                    <a href="//k3e.pl" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="k3e-link">
                        <span class="k3e-logo">K</span>3e.pl
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="photoModalLabel">Podgląd zdjęcia</h4>
                <button type="button" 
                        class="modal-close" 
                        data-dismiss="modal" 
                        aria-label="Zamknij modal">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image-container">
                    <img id="modalImage" 
                         src="" 
                         alt="" 
                         class="modal-image" 
                         loading="lazy">
                </div>
                <div class="modal-details">
                    <h5 id="modalTitle">Tytuł zdjęcia</h5>
                    <p id="modalDescription" class="modal-description"></p>
                    <div class="modal-meta">
                        <div class="modal-tags" id="modalTags"></div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" id="modalShare">
                                <i class="fas fa-share" aria-hidden="true"></i> Udostępnij
                            </button>
                            <a href="#" class="btn btn-primary" id="modalView">
                                <i class="fas fa-eye" aria-hidden="true"></i> Zobacz szczegóły
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Back to top button -->
<button type="button" 
        class="back-to-top" 
        id="backToTop"
        aria-label="Wróć na górę strony"
        tabindex="-1">
    <i class="fas fa-chevron-up" aria-hidden="true"></i>
</button>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay" aria-hidden="true">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
        <span class="sr-only">Ładowanie...</span>
    </div>
</div>

<!-- Flash Messages Container -->
<div id="flashMessages" class="flash-messages" aria-live="polite"></div>

<!-- JavaScript for flash messages -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            if (typeof showNotification === 'function') {
                showNotification(
                    <?= json_encode($message) ?>, 
                    <?= json_encode($type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info')) ?>
                );
            }
        <?php endforeach; ?>
    <?php endforeach; ?>
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>