<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */

// Register updated fonts
frontend\assets\FontAsset::register($this);
frontend\assets\AppAsset::register($this);
frontend\assets\IconAsset::register($this);

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
    
    <!-- Additional inline styles for font optimization -->
    <style>
        /* Font display optimization */
        @font-face {
            font-family: 'Comfortaa';
            font-display: swap;
        }
        @font-face {
            font-family: 'Quicksand';
            font-display: swap;
        }
        
        /* Critical CSS overrides */
        :root {
            --font-family-base: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-family-display: 'Comfortaa', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            font-family: var(--font-family-base);
        }
        
        h1, h2, h3, h4, h5, h6, .logo, .hero-title, .section-title {
            font-family: var(--font-family-display);
        }
    </style>
</head>
<body class="<?= Yii::$app->user->isGuest ? 'guest' : 'authenticated' ?>">
<?php $this->beginBody() ?>

<!-- Skip to main content link for accessibility -->
<a href="#main-content" class="skip-link">Przejdź do głównej treści</a>

<!-- Screen reader announcements -->
<div id="aria-status" class="sr-only" aria-live="polite" aria-atomic="true"></div>

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
                        'class' => 'nav-link' . (Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? ' active' : ''),
                        'role' => 'menuitem',
                        'encode' => false
                    ]) ?>
                </li>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-images" aria-hidden="true"></i> <span>Galeria</span>', ['/gallery/index'], [
                            'class' => 'nav-link' . (Yii::$app->controller->id === 'gallery' ? ' active' : ''),
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-search" aria-hidden="true"></i> <span>Wyszukiwanie</span>', ['/search/index'], [
                            'class' => 'nav-link' . (Yii::$app->controller->id === 'search' ? ' active' : ''),
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php endif; ?>
                
                <!-- User menu -->
                <?php if (Yii::$app->user->isGuest): ?>
                    <li role="none">
                        <?= Html::a('<i class="fas fa-sign-in-alt" aria-hidden="true"></i> <span>Logowanie</span>', ['/site/login'], [
                            'class' => 'nav-link' . (Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'login' ? ' active' : ''),
                            'role' => 'menuitem',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php else: ?>
                    <li class="nav-dropdown" role="none">
                        <button type="button" class="nav-link dropdown-toggle" 
                                id="userDropdown" 
                                aria-haspopup="true" 
                                aria-expanded="false">
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
                        <a href="<?= \yii\helpers\Url::to(['/site/index']) ?>">
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
                <h3>Zasobnik B</h3>
                <p>Twój osobisty bank zdjęć. Przechowuj, organizuj i dziel się wspomnieniami.</p>
            </div>
            
            <?php if (!Yii::$app->user->isGuest): ?>
                <div class="footer-section">
                    <h4>Szybki dostęp</h4>
                    <ul class="footer-links">
                        <li><?= Html::a('Galeria', ['/gallery/index']) ?></li>
                        <li><?= Html::a('Wyszukiwanie', ['/search/index']) ?></li>
                        <?php if (Yii::$app->user->can('managePhotos')): ?>
                            <li><?= Html::a('Panel administratora', '//system.zasobnik.be', ['target' => '_blank', 'rel' => 'noopener']) ?></li>
                        <?php endif; ?>
                    </ul>
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
<div class="modal" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="photoModalLabel">Podgląd zdjęcia</h4>
                <button type="button" 
                        class="modal-close" 
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
        style="display: none;">
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

<!-- JavaScript initialization and flash messages -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize back to top button
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        let scrollTimer = null;
        
        window.addEventListener('scroll', function() {
            if (scrollTimer !== null) {
                clearTimeout(scrollTimer);
            }
            scrollTimer = setTimeout(function() {
                if (window.pageYOffset > 300) {
                    backToTop.style.display = 'block';
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                    setTimeout(() => {
                        if (!backToTop.classList.contains('visible')) {
                            backToTop.style.display = 'none';
                        }
                    }, 300);
                }
            }, 100);
        }, { passive: true });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Show flash messages
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
    
    // Initialize dropdown menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other dropdowns
            dropdownToggles.forEach(other => {
                if (other !== this) {
                    other.setAttribute('aria-expanded', 'false');
                    const otherMenu = other.nextElementSibling;
                    if (otherMenu) otherMenu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            this.setAttribute('aria-expanded', !isExpanded);
            const menu = this.nextElementSibling;
            if (menu) {
                menu.classList.toggle('show', !isExpanded);
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            dropdownToggles.forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
                const menu = toggle.nextElementSibling;
                if (menu) menu.classList.remove('show');
            });
        }
    });
    
    // Mobile menu functionality
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            navMenu.classList.toggle('active', !isExpanded);
            
            // Prevent body scroll when menu is open
            if (!isExpanded) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        // Close mobile menu when clicking on a link
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                mobileToggle.setAttribute('aria-expanded', 'false');
                navMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
    
    // Header scroll behavior
    const header = document.querySelector('.header');
    if (header) {
        let lastScrollTop = 0;
        let scrolling = false;
        
        window.addEventListener('scroll', function() {
            if (!scrolling) {
                requestAnimationFrame(function() {
                    const currentScroll = window.pageYOffset;
                    
                    if (currentScroll > 100) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                    
                    lastScrollTop = currentScroll;
                    scrolling = false;
                });
                scrolling = true;
            }
        }, { passive: true });
    }
    
    // Performance: Preload critical resources
    const preloadLinks = [
        '/images/zasobnik.png',
        '/images/zasobnik_be.png'
    ];
    
    preloadLinks.forEach(href => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = href;
        document.head.appendChild(link);
    });
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // You could send this to an error tracking service
});

// Service worker registration (if available)
if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
    navigator.serviceWorker.register('/sw.js').catch(err => {
        console.log('Service worker registration failed:', err);
    });
}
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>