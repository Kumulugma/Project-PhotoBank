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

            /* Critical mobile menu styles - NAPRAWIONE */
            .mobile-menu-toggle {
                display: none;
                background: transparent;
                border: none;
                cursor: pointer;
                padding: 8px;
                width: 40px;
                height: 40px;
                border-radius: 4px;
                position: relative;
                z-index: 1001;
            }

            .mobile-menu-toggle:hover {
                background: rgba(0, 0, 0, 0.1);
            }

            .hamburger {
                width: 24px;
                height: 18px;
                position: relative;
                margin: 0 auto;
            }

            .hamburger-line {
                display: block;
                height: 3px;
                width: 100%;
                background: #1f2937;
                margin-bottom: 3px;
                border-radius: 2px;
                transition: all 0.3s ease;
            }

            .hamburger-line:last-child {
                margin-bottom: 0;
            }

            /* Animation gdy menu otwarte */
            .mobile-menu-toggle[aria-expanded="true"] .hamburger-line:nth-child(1) {
                transform: rotate(45deg) translate(6px, 6px);
            }

            .mobile-menu-toggle[aria-expanded="true"] .hamburger-line:nth-child(2) {
                opacity: 0;
            }

            .mobile-menu-toggle[aria-expanded="true"] .hamburger-line:nth-child(3) {
                transform: rotate(-45deg) translate(6px, -6px);
            }

            @media (max-width: 768px) {
                .mobile-menu-toggle {
                    display: block;
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

                .nav-menu li {
                    width: 100%;
                    border-bottom: 1px solid #e5e7eb;
                }

                .nav-menu .nav-link {
                    padding: 15px 20px;
                    border-left: 4px solid transparent;
                }

                .nav-menu .nav-link:hover,
                .nav-menu .nav-link.active {
                    background: #f9fafb;
                    border-left-color: #6366f1;
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

        <!-- Header Navigation - KOMPLETNIE NOWE -->
        <header class="header" role="banner">
            <div class="container">
                <nav class="navbar" role="navigation" aria-label="główne menu">
                    <!-- Logo -->
                    <?=
                    Html::a('Zasobnik B', ['/site/index'], [
                        'class' => 'logo',
                        'aria-label' => 'Zasobnik B - strona główna'
                    ])
                    ?>

                    <!-- Mobile menu toggle button -->
                    <button class="mobile-menu-toggle" 
                            id="mobileToggle" 
                            aria-expanded="false" 
                            aria-controls="navMenu"
                            aria-label="Przełącz menu mobilne">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>

                    <!-- Navigation Menu -->
                    <div class="nav-menu-wrapper">
                        <ul class="nav-menu" id="navMenu" role="menubar">
                            <li class="nav-item" role="none">
                                <?=
                                Html::a(
                                        '<i class="fas fa-home" aria-hidden="true"></i> <span>Strona główna</span>',
                                        ['/site/index'],
                                        [
                                            'class' => 'nav-link' . (Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? ' active' : ''),
                                            'role' => 'menuitem',
                                            'encode' => false
                                        ]
                                )
                                ?>
                            </li>

                            <?php if (!Yii::$app->user->isGuest): ?>
                                <li class="nav-item" role="none">
                                    <?=
                                    Html::a(
                                            '<i class="fas fa-images" aria-hidden="true"></i> <span>Galeria</span>',
                                            ['/gallery/index'],
                                            [
                                                'class' => 'nav-link' . (Yii::$app->controller->id === 'gallery' ? ' active' : ''),
                                                'role' => 'menuitem',
                                                'encode' => false
                                            ]
                                    )
                                    ?>
                                </li>

                                <li class="nav-item" role="none">
                                    <?=
                                    Html::a(
                                            '<i class="fas fa-search" aria-hidden="true"></i> <span>Wyszukiwanie</span>',
                                            ['/search/index'],
                                            [
                                                'class' => 'nav-link' . (Yii::$app->controller->id === 'search' ? ' active' : ''),
                                                'role' => 'menuitem',
                                                'encode' => false
                                            ]
                                    )
                                    ?>
                                </li>
                            <?php endif; ?>

                            <!-- Poprawka HTML - zastąp sekcję user menu w header -->

                            <!-- User menu -->
                            <?php if (Yii::$app->user->isGuest): ?>
                                <li class="nav-item" role="none">
                                    <?=
                                    Html::a(
                                            '<i class="fas fa-sign-in-alt" aria-hidden="true"></i> <span>Logowanie</span>',
                                            ['/site/login'],
                                            [
                                                'class' => 'nav-link' . (Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'login' ? ' active' : ''),
                                                'role' => 'menuitem',
                                                'encode' => false
                                            ]
                                    )
                                    ?>
                                </li>
                            <?php else: ?>
                                <li class="nav-item nav-dropdown" 
                                    data-username="<?= Html::encode(Yii::$app->user->identity->username) ?>" 
                                    role="none">
                                    <!-- Desktop dropdown button -->
                                    <button type="button" 
                                            class="nav-link dropdown-toggle" 
                                            id="userDropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <i class="fas fa-user" aria-hidden="true"></i> 
                                        <span><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                                        <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                                    </button>

                                    <!-- Dropdown menu (zawsze widoczne na mobile) -->
                                    <div class="dropdown-menu" role="menu" aria-labelledby="userDropdown">
                                        <?php if (Yii::$app->user->can('managePhotos')): ?>
                                            <?=
                                            Html::a(
                                                    '<i class="fas fa-cog" aria-hidden="true"></i> Panel administratora <i class="fas fa-external-link-alt external-link-icon" aria-hidden="true"></i>',
                                                    '//system.zasobnik.be',
                                                    [
                                                        'class' => 'dropdown-item',
                                                        'target' => '_blank',
                                                        'role' => 'menuitem',
                                                        'rel' => 'noopener noreferrer',
                                                        'encode' => false
                                                    ]
                                            )
                                            ?>
                                        <?php endif; ?>
                                        <?=
                                        Html::a(
                                                '<i class="fas fa-sign-out-alt" aria-hidden="true"></i> Wyloguj',
                                                ['/site/logout'],
                                                [
                                                    'class' => 'dropdown-item',
                                                    'data-method' => 'post',
                                                    'role' => 'menuitem',
                                                    'encode' => false
                                                ]
                                        )
                                        ?>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
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
            // JavaScript uproszczony - zastąp w main.php

            document.addEventListener('DOMContentLoaded', function () {
                console.log('Initializing navigation...');

                // Get elements
                const mobileToggle = document.getElementById('mobileToggle');
                const navMenuWrapper = document.querySelector('.nav-menu-wrapper');
                const navMenu = document.getElementById('navMenu');

                if (mobileToggle && navMenuWrapper && navMenu) {
                    // Mobile menu toggle
                    mobileToggle.addEventListener('click', function (e) {
                        e.preventDefault();
                        console.log('Mobile toggle clicked');

                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        const newState = !isExpanded;

                        // Update button state
                        this.setAttribute('aria-expanded', newState);

                        // Toggle menu wrapper
                        navMenuWrapper.classList.toggle('active', newState);

                        // Prevent body scroll
                        document.body.classList.toggle('menu-open', newState);

                        console.log('Menu state changed to:', newState);
                    });

                    // Close menu when clicking nav links (wszystkie nav-link i dropdown-item)
                    navMenu.querySelectorAll('.nav-link:not(.dropdown-toggle), .dropdown-item').forEach(link => {
                        link.addEventListener('click', function () {
                            console.log('Nav link clicked, closing menu');
                            closeMenu();
                        });
                    });

                    // Close menu when clicking outside (only on mobile)
                    document.addEventListener('click', function (e) {
                        if (window.innerWidth <= 768 &&
                                navMenuWrapper.classList.contains('active') &&
                                !mobileToggle.contains(e.target) &&
                                !navMenuWrapper.contains(e.target)) {
                            console.log('Clicked outside, closing menu');
                            closeMenu();
                        }
                    });

                    // ESC key to close menu
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && navMenuWrapper.classList.contains('active')) {
                            console.log('ESC pressed, closing menu');
                            closeMenu();
                            mobileToggle.focus();
                        }
                    });

                    // Close menu on window resize
                    window.addEventListener('resize', function () {
                        if (window.innerWidth > 768 && navMenuWrapper.classList.contains('active')) {
                            console.log('Resized to desktop, closing menu');
                            closeMenu();
                        }
                    });

                    // Function to close menu
                    function closeMenu() {
                        mobileToggle.setAttribute('aria-expanded', 'false');
                        navMenuWrapper.classList.remove('active');
                        document.body.classList.remove('menu-open');

                        // Close any open dropdowns (tylko desktop)
                        if (window.innerWidth > 768) {
                            navMenu.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
                                dropdown.classList.remove('show');
                                const toggle = dropdown.previousElementSibling;
                                if (toggle) {
                                    toggle.setAttribute('aria-expanded', 'false');
                                }
                            });
                        }
                    }

                    // Desktop dropdown functionality (tylko desktop)
                    const dropdownToggles = navMenu.querySelectorAll('.dropdown-toggle');
                    dropdownToggles.forEach(toggle => {
                        toggle.addEventListener('click', function (e) {
                            // Tylko na desktop
                            if (window.innerWidth <= 768)
                                return;

                            e.preventDefault();
                            console.log('Desktop dropdown toggle clicked');

                            const isExpanded = this.getAttribute('aria-expanded') === 'true';
                            const dropdown = this.nextElementSibling;

                            // Close other dropdowns
                            dropdownToggles.forEach(otherToggle => {
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

                    // Close dropdowns when clicking outside (desktop only)
                    document.addEventListener('click', function (e) {
                        if (window.innerWidth > 768 && !e.target.closest('.nav-dropdown')) {
                            dropdownToggles.forEach(toggle => {
                                toggle.setAttribute('aria-expanded', 'false');
                                const dropdown = toggle.nextElementSibling;
                                if (dropdown) {
                                    dropdown.classList.remove('show');
                                }
                            });
                        }
                    });

                    console.log('Navigation initialized successfully');
                } else {
                    console.error('Navigation elements not found!');
                }

                // Back to top button
                const backToTop = document.getElementById('backToTop');
                if (backToTop) {
                    window.addEventListener('scroll', function () {
                        if (window.pageYOffset > 300) {
                            backToTop.style.display = 'block';
                        } else {
                            backToTop.style.display = 'none';
                        }
                    });

                    backToTop.addEventListener('click', function () {
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    });
                }

                // Flash messages
<?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
    <?php foreach ((array) $messages as $message): ?>
                        if (typeof showNotification === 'function') {
                            showNotification(<?= json_encode($message) ?>, <?= json_encode($type) ?>);
                        }
    <?php endforeach; ?>
<?php endforeach; ?>

                console.log('All initialization complete');
            });

// Notification fallback
            if (typeof showNotification === 'undefined') {
                window.showNotification = function (message, type) {
                    console.log('Notification:', type, message);
                };
            }
        </script>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage(); ?>