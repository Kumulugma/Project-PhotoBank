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
        
        /* Critical mobile menu styles - inline to prevent FOUC */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 40px;
            height: 40px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
        }
        
        .hamburger {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 100%;
            height: 100%;
        }
        
        .hamburger-line {
            display: block;
            height: 3px;
            width: 100%;
            background: #1f2937;
            border-radius: 2px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            transform-origin: center;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
                z-index: 1001;
                position: relative;
            }
            
            .nav-menu {
                position: fixed;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100vh;
                background: #ffffff;
                flex-direction: column;
                justify-content: flex-start;
                align-items: stretch;
                padding: 80px 0 2rem;
                margin: 0;
                z-index: 1000;
                transition: left 0.3s ease-in-out;
                border-right: 1px solid #e5e7eb;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                overflow-y: auto;
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            body.menu-open {
                overflow: hidden;
            }
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
    console.log('DOM loaded, initializing...');
    
    // Mobile menu functionality - IMPROVED VERSION
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    
    console.log('Mobile toggle:', mobileToggle);
    console.log('Nav menu:', navMenu);
    
    if (mobileToggle && navMenu) {
        console.log('Initializing mobile menu...');
        
        // Handle menu toggle
        mobileToggle.addEventListener('click', function(e) {
            console.log('Mobile toggle clicked');
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            console.log('Current state:', isExpanded, 'New state:', newState);
            
            // Update button state
            this.setAttribute('aria-expanded', newState);
            
            // Toggle menu visibility
            navMenu.classList.toggle('active', newState);
            
            // Visual feedback for hamburger button
            if (newState) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
            
            // Prevent body scroll when menu is open
            document.body.classList.toggle('menu-open', newState);
            
            // Focus management
            if (newState) {
                // Focus first menu item when opening
                const firstMenuItem = navMenu.querySelector('.nav-link');
                if (firstMenuItem) {
                    setTimeout(() => firstMenuItem.focus(), 100);
                }
            }
            
            // Update hamburger animation
            const hamburgerLines = this.querySelectorAll('.hamburger-line');
            if (newState) {
                hamburgerLines[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                hamburgerLines[1].style.opacity = '0';
                hamburgerLines[1].style.transform = 'scale(0)';
                hamburgerLines[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
            } else {
                hamburgerLines[0].style.transform = '';
                hamburgerLines[1].style.opacity = '';
                hamburgerLines[1].style.transform = '';
                hamburgerLines[2].style.transform = '';
            }
            
            console.log('Menu toggled, active class:', navMenu.classList.contains('active'));
        });
        
        // Close menu when clicking on nav links (not dropdown toggles)
        navMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', function() {
                console.log('Nav link clicked, closing menu');
                closeMobileMenu();
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (navMenu.classList.contains('active') && 
                !mobileToggle.contains(e.target) && 
                !navMenu.contains(e.target)) {
                console.log('Clicked outside, closing menu');
                closeMobileMenu();
            }
        });
        
        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                console.log('Escape pressed, closing menu');
                closeMobileMenu();
                mobileToggle.focus();
            }
        });
        
        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Close mobile menu on resize to desktop
                if (window.innerWidth > 768 && navMenu.classList.contains('active')) {
                    console.log('Resized to desktop, closing menu');
                    closeMobileMenu();
                }
            }, 150);
        });
        
        // Helper function to close mobile menu
        function closeMobileMenu() {
            console.log('Closing mobile menu...');
            mobileToggle.setAttribute('aria-expanded', 'false');
            navMenu.classList.remove('active');
            document.body.classList.remove('menu-open');
            
            // Reset hamburger animation
            const hamburgerLines = mobileToggle.querySelectorAll('.hamburger-line');
            hamburgerLines.forEach(line => {
                line.style.transform = '';
                line.style.opacity = '';
            });
            
            // Close any open dropdowns
            navMenu.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
                dropdown.classList.remove('show');
                const toggle = dropdown.previousElementSibling;
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
        
        // Handle dropdown toggles in mobile menu
        navMenu.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                console.log('Dropdown toggle clicked');
                e.preventDefault();
                e.stopPropagation();
                
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const dropdown = this.nextElementSibling;
                
                // Close other dropdowns
                navMenu.querySelectorAll('.dropdown-toggle').forEach(otherToggle => {
                    if (otherToggle !== this) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                        const otherDropdown = otherToggle.nextElementSibling;
                        if (otherDropdown) {
                            otherDropdown.classList.remove('show');
                        }
                    }
                });
                
                // Toggle current dropdown
                this.setAttribute('aria-expanded', !isExpanded);
                if (dropdown) {
                    dropdown.classList.toggle('show', !isExpanded);
                }
            });
        });
        
        console.log('Mobile menu initialization complete');
    } else {
        console.error('Mobile toggle or nav menu not found!');
    }
    
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
    
    // Initialize dropdown menus (desktop)
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Skip if this is in mobile menu (handled separately)
            if (window.innerWidth <= 768) return;
            
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
    
    // Close dropdowns when clicking outside (desktop only)
    document.addEventListener('click', function(e) {
        if (window.innerWidth > 768 && !e.target.closest('.nav-dropdown')) {
            dropdownToggles.forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
                const menu = toggle.nextElementSibling;
                if (menu) menu.classList.remove('show');
            });
        }
    });
    
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
    
    console.log('All initialization complete');
});

// Touch gesture support for mobile menu
let touchStartX = 0;
let touchStartY = 0;

document.addEventListener('touchstart', function(e) {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
}, { passive: true });

document.addEventListener('touchend', function(e) {
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (!mobileToggle || !navMenu) return;
    
    const touchEndX = e.changedTouches[0].clientX;
    const touchEndY = e.changedTouches[0].clientY;
    const deltaX = touchEndX - touchStartX;
    const deltaY = touchEndY - touchStartY;
    
    // Swipe right to open menu (when closed)
    if (deltaX > 50 && Math.abs(deltaY) < 100 && !navMenu.classList.contains('active') && touchStartX < 50) {
        console.log('Swipe right detected, opening menu');
        mobileToggle.click();
    }
    
    // Swipe left to close menu (when open)
    if (deltaX < -50 && Math.abs(deltaY) < 100 && navMenu.classList.contains('active')) {
        console.log('Swipe left detected, closing menu');
        if (mobileToggle.getAttribute('aria-expanded') === 'true') {
            mobileToggle.click();
        }
    }
}, { passive: true });

// Screen reader announcement helper
window.announceToScreenReader = function(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    
    document.body.appendChild(announcement);
    
    // Remove after announcement
    setTimeout(() => {
        if (document.body.contains(announcement)) {
            document.body.removeChild(announcement);
        }
    }, 1000);
};

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
});

// Global notification function fallback
if (typeof showNotification === 'undefined') {
    window.showNotification = function(message, type) {
        console.log('Notification:', type, message);
        alert(message);
    };
}

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