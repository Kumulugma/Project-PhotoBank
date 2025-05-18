<?php
/* @var $this yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366f1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - PersonalPhotoBank</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Inline CSS for immediate loading -->
    <style>
        <?= file_get_contents(__DIR__ . '/../../../web/css/modern-design.css') ?>
    </style>
    
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Header -->
<header class="header">
    <div class="container">
        <nav class="navbar">
            <?= Html::a('PersonalPhotoBank', ['/site/index'], ['class' => 'logo']) ?>
            
            <ul class="nav-menu" id="navMenu">
                <li>
                    <?= Html::a('<i class="fas fa-home"></i> Strona główna', ['/site/index'], [
                        'class' => 'nav-link',
                        'encode' => false
                    ]) ?>
                </li>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li>
                        <?= Html::a('<i class="fas fa-images"></i> Galeria', ['/gallery/index'], [
                            'class' => 'nav-link',
                            'encode' => false
                        ]) ?>
                    </li>
                    <li>
                        <?= Html::a('<i class="fas fa-search"></i> Wyszukiwanie', ['/search/index'], [
                            'class' => 'nav-link',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php endif; ?>
                
                <?php if (Yii::$app->user->isGuest): ?>
                    <li>
                        <?= Html::a('<i class="fas fa-sign-in-alt"></i> Logowanie', ['/site/login'], [
                            'class' => 'nav-link',
                            'encode' => false
                        ]) ?>
                    </li>
                <?php else: ?>
                    <li class="nav-dropdown">
                        <a href="#" class="nav-link">
                            <i class="fas fa-user"></i> <?= Html::encode(Yii::$app->user->identity->username) ?>
                        </a>
                        <div class="dropdown-menu">
                            <?php if (Yii::$app->user->can('managePhotos')): ?>
                                <?= Html::a('<i class="fas fa-cog"></i> Panel administratora', 
                                    '//admin.' . $_SERVER['HTTP_HOST'], [
                                    'class' => 'dropdown-item',
                                    'target' => '_blank',
                                    'encode' => false
                                ]) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="fas fa-sign-out-alt"></i> Wyloguj', ['/site/logout'], [
                                'class' => 'dropdown-item',
                                'data-method' => 'post',
                                'encode' => false
                            ]) ?>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
            
            <button class="mobile-menu-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </div>
</header>

<!-- Main Content -->
<main class="main">
    <!-- Breadcrumbs -->
    <?php if (!empty($this->params['breadcrumbs'])): ?>
        <section class="breadcrumbs">
            <div class="container">
                <ul class="breadcrumb-list">
                    <?php foreach ($this->params['breadcrumbs'] as $breadcrumb): ?>
                        <?php if (is_array($breadcrumb)): ?>
                            <li class="breadcrumb-item">
                                <?= Html::a($breadcrumb['label'], $breadcrumb['url']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><?= Html::encode($breadcrumb) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('<?= Html::encode($message) ?>', '<?= $type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info') ?>');
                });
            </script>
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    <!-- Page Content -->
    <div class="page-content">
        <?= $content ?>
    </div>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>PersonalPhotoBank</h4>
                <p>Twój osobisty bank zdjęć. Przechowuj i dziel się swoimi najlepszymi momentami.</p>
            </div>
            <div class="footer-section">
                <h4>Szybkie linki</h4>
                <p><?= Html::a('Galeria', ['/gallery/index']) ?></p>
                <p><?= Html::a('Wyszukiwanie', ['/search/index']) ?></p>
                <p><?= Html::a('Kategorie', ['/gallery/category']) ?></p>
            </div>
            <div class="footer-section">
                <h4>Kontakt</h4>
                <p><i class="fas fa-envelope"></i> kontakt@personalphotobank.pl</p>
                <p><i class="fas fa-phone"></i> +48 123 456 789</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y') ?> PersonalPhotoBank. Wszystkie prawa zastrzeżone. | 
                <span class="text-light">
                    Wspierane przez: 
                    <a href="//k3e.pl" style="color: #10b981;">
                        <span style="background: #10b981; color: white; padding: 2px 4px; border-radius: 4px; font-weight: bold;">K</span>3e.pl
                    </a>
                </span>
            </p>
        </div>
    </div>
</footer>

<!-- Photo Modal -->
<div class="modal" id="photoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Zdjęcie</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" alt="" style="width: 100%; height: auto; border-radius: 8px;">
            <div style="margin-top: 1rem;">
                <p id="modalDescription"></p>
                <div class="flex-between" style="margin-top: 1rem;">
                    <div class="tags" id="modalTags"></div>
                    <div class="flex-gap">
                        <button class="btn btn-secondary">
                            <i class="fas fa-share"></i> Udostępnij
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Pobierz
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>