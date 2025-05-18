<?php
/* @var $this yii\web\View */
/* @var $content string */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#3b82f6">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - PersonalPhotoBank</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => 'PersonalPhotoBank',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-lg navbar-dark fixed-top',
        ],
        'innerContainerOptions' => ['class' => 'container-fluid'],
    ]);
    
    $menuItems = [
        [
            'label' => '<i class="fas fa-home me-1"></i>Strona główna',
            'url' => ['/site/index'],
            'linkOptions' => ['class' => 'nav-link']
        ],
    ];
    
    if (!Yii::$app->user->isGuest) {
        $menuItems[] = [
            'label' => '<i class="fas fa-images me-1"></i>Galeria',
            'url' => ['/gallery/index'],
            'linkOptions' => ['class' => 'nav-link']
        ];
        $menuItems[] = [
            'label' => '<i class="fas fa-search me-1"></i>Wyszukiwanie',
            'url' => ['/search/index'],
            'linkOptions' => ['class' => 'nav-link']
        ];
    }
    
    if (Yii::$app->user->isGuest) {
        $menuItems[] = [
            'label' => '<i class="fas fa-sign-in-alt me-1"></i>Logowanie',
            'url' => ['/site/login'],
            'linkOptions' => ['class' => 'nav-link']
        ];
    } else {
        $menuItems[] = [
            'label' => '<i class="fas fa-user me-1"></i>' . Yii::$app->user->identity->username,
            'items' => [
                [
                    'label' => '<i class="fas fa-cog me-2"></i>Panel administratora',
                    'url' => ['//admin.' . $_SERVER['HTTP_HOST']],
                    'visible' => Yii::$app->user->can('managePhotos'),
                    'linkOptions' => [
                        'target' => '_blank',
                        'class' => 'dropdown-item'
                    ],
                ],
                '<li><hr class="dropdown-divider" /></li>',
                [
                    'label' => '<i class="fas fa-sign-out-alt me-2"></i>Wyloguj',
                    'url' => ['/site/logout'],
                    'linkOptions' => [
                        'data-method' => 'post',
                        'class' => 'dropdown-item'
                    ],
                ],
            ],
        ];
    }
    
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto'],
        'items' => $menuItems,
        'encodeLabels' => false,
    ]);
    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container-fluid"  style="padding-left: 0px !important; padding-right: 0px !important;">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <div class="breadcrumb-container mb-3 mt-3">
                <?= Breadcrumbs::widget([
                    'links' => $this->params['breadcrumbs'],
                    'options' => ['class' => 'breadcrumb'],
                ]) ?>
            </div>
        <?php endif; ?>
        
        <?= Alert::widget() ?>
        
        <div class="content-wrapper fade-in-up">
            <?= $content ?>
        </div>
    </div>
</main>

<footer class="footer mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    <i class="fas fa-copyright me-1"></i>
                    PersonalPhotoBank <?= date('Y') ?>
                </span>
            </div>
            <div class="col-md-6 text-end">
                <span id="support" class="mb-3 mb-md-0 text-body-secondary">
                    Wspierane przez: <a href="//k3e.pl"><span>K</span>3e.pl</a>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Enhanced Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">
                    <i class="fas fa-image me-2"></i>
                    <span id="photoModalTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-2">
                <div class="photo-modal-container">
                    <img src="" class="img-fluid" alt="" id="photoModalImage" style="max-height: 70vh; object-fit: contain;">
                    <div class="spinner-border text-primary" role="status" id="photoModalLoader">
                        <span class="visually-hidden">Ładowanie...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="photo-modal-info">
                    <small class="text-muted" id="photoModalInfo"></small>
                </div>
                <div class="photo-modal-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="photoModalShare">
                        <i class="fas fa-share-alt me-1"></i>Udostępnij
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="photoModalDownload">
                        <i class="fas fa-download me-1"></i>Pobierz
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional styles for layout components */
.dropdown-toggle::after {
    display: none; /* Ukrywa caret w dropdown */
}

/* Photo modal specific styles */
.photo-modal-container {
    position: relative;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#photoModalLoader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

#photoModalImage {
    display: none;
    border-radius: var(--border-radius);
}

#photoModalImage.loaded {
    display: block;
}

/* Breadcrumb container */
.breadcrumb-container {
    margin-top: 0;
}

.breadcrumb {
    background: var(--bg-primary);
    padding: 0.75rem 1.25rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-light);
    margin-bottom: 0;
}

.breadcrumb-item {
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    font-weight: 600;
    color: #666;
    font-size: 16px;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb-item a:hover {
    text-decoration: underline;
    color: var(--primary-hover);
}

.breadcrumb-item.active {
    color: var(--text-secondary);
}

/* Content wrapper */
.content-wrapper {
    min-height: calc(100vh - 260px);
}

/* Navbar scrolled state */
.navbar.scrolled {
    backdrop-filter: blur(20px);
    background: rgba(59, 130, 246, 0.95) !important;
    box-shadow: var(--shadow-lg);
}

/* Footer styling */
.footer {
    background: var(--bg-primary);
    border-top: 1px solid var(--border-light);
    padding: 2rem 0;
    margin-top: 4rem;
}

.footer .text-muted {
    color: var(--text-secondary) !important;
}

/* Support link styling */
footer #support {
    color: var(--bs-secondary-color) !important;
}

footer #support a {
    color: var(--bs-secondary-color) !important;
    text-decoration: none;
    border: solid 1px #eff0f250;
    padding: 0 2px 0 0;
    transition: all 0.3s ease-in-out;
}

footer #support a:hover {
    border: solid 1px #14540450;
}

footer #support a:hover span {
    background-color: #145404;
}

footer #support a span {
    background-color: #46b450;
    transition: all 0.3s ease-in-out;
    color: white;
    font-weight: bold;
    padding: 1px 2px;
}

/* Modal improvements */
.modal-content {
    border: none;
    border-radius: var(--border-radius-xl);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    border-bottom: none;
    padding: 1.5rem;
}

.modal-title {
    font-weight: 600;
}

.modal-header .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.modal-header .btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--border-light);
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
}

/* Responsive improvements */
@media (max-width: 768px) {
    body {
        padding-top: 60px; /* Smaller navbar on mobile */
    }
    
    .breadcrumb-container {
        margin-top: 0;
        margin-bottom: 0.75rem;
    }
    
    .breadcrumb {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    .footer {
        padding: 1.5rem 0;
        text-align: center;
    }
    
    .footer .row > div {
        text-align: center !important;
        margin-bottom: 0.5rem;
    }
    
    .footer .text-end {
        text-align: center !important;
    }
    
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>

<?php $this->endBody() ?>

<script>
// Enhanced photo modal functionality and other interactions
$(document).ready(function() {
    // Photo modal handling
    $(document).on('click', '.photo-item img, .photo-tile', function(e) {
        e.preventDefault();
        
        const $img = $(this).is('img') ? $(this) : $(this).find('img');
        const largeUrl = $img.data('large') || $img.attr('src');
        const title = $img.data('title') || $img.attr('alt') || 'Zdjęcie';
        
        $('#photoModalTitle').text(title);
        $('#photoModalImage').hide().removeClass('loaded');
        $('#photoModalLoader').show();
        
        const modal = new bootstrap.Modal('#photoModal');
        modal.show();
        
        // Load image
        const img = new Image();
        img.onload = function() {
            $('#photoModalImage').attr('src', largeUrl).show().addClass('loaded');
            $('#photoModalLoader').hide();
        };
        img.onerror = function() {
            $('#photoModalLoader').hide();
            $('#photoModalImage').attr('src', '/images/placeholder.jpg').show().addClass('loaded');
            if (typeof ModernPhotoBank !== 'undefined') {
                ModernPhotoBank.showNotification('Błąd ładowania zdjęcia', 'error');
            }
        };
        img.src = largeUrl;
        
        // Set additional info
        const $card = $img.closest('.card');
        const info = $card.find('.card-footer').text() || '';
        $('#photoModalInfo').text(info);
    });
    
    // Share functionality
    $('#photoModalShare').on('click', function() {
        const url = window.location.href;
        const title = $('#photoModalTitle').text();
        
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback - copy to clipboard
            if (typeof ModernPhotoBank !== 'undefined') {
                ModernPhotoBank.copyToClipboard(url);
            } else {
                // Simple fallback
                navigator.clipboard.writeText(url).then(function() {
                    alert('Link skopiowany do schowka!');
                });
            }
        }
    });
    
    // Download functionality
    $('#photoModalDownload').on('click', function() {
        const imageUrl = $('#photoModalImage').attr('src');
        const title = $('#photoModalTitle').text();
        
        // Create a temporary link to download the image
        const link = document.createElement('a');
        link.href = imageUrl;
        link.download = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        if (typeof ModernPhotoBank !== 'undefined') {
            ModernPhotoBank.showNotification(`Pobieranie zdjęcia "${title}"`, 'success');
        }
    });
    
    // Navbar scroll effect
    $(window).on('scroll', function() {
        const navbar = $('.navbar');
        if ($(window).scrollTop() > 50) {
            navbar.addClass('scrolled');
        } else {
            navbar.removeClass('scrolled');
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add fade-in animation to content
    $('.content-wrapper').addClass('fade-in-up');
});

// Simple notification function if ModernPhotoBank is not loaded yet
function showSimpleNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="
            position: fixed;
            top: 100px;
            right: 20px;
            min-width: 300px;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        ">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('body').append(notification);

    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}
</script>

</body>
</html>
<?php $this->endPage(); ?>