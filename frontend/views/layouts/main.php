<?php
/* @var $this \yii\web\View */
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
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - PersonalPhotoBank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => '<i class="fas fa-camera"></i> PersonalPhotoBank',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
        'innerContainerOptions' => ['class' => 'container-fluid'],
    ]);
    
    $menuItems = [
        ['label' => '<i class="fas fa-home"></i> Strona główna', 'url' => ['/site/index']],
    ];
    
    if (!Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => '<i class="fas fa-images"></i> Galeria', 'url' => ['/gallery/index']];
        $menuItems[] = ['label' => '<i class="fas fa-search"></i> Wyszukiwanie', 'url' => ['/search/index']];
    }
    
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => '<i class="fas fa-sign-in-alt"></i> Logowanie', 'url' => ['/site/login']];
    } else {
        $menuItems[] = [
            'label' => '<i class="fas fa-user"></i> ' . Yii::$app->user->identity->username,
            'items' => [
                [
                    'label' => '<i class="fas fa-cog"></i> Panel administratora',
                    'url' => 'http://admin.' . $_SERVER['HTTP_HOST'],
                    'visible' => Yii::$app->user->can('managePhotos'),
                    'linkOptions' => ['target' => '_blank'],
                ],
                '<li class="dropdown-divider"></li>',
                [
                    'label' => '<i class="fas fa-sign-out-alt"></i> Wyloguj',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post'],
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
    <div class="container-fluid">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">&copy; PersonalPhotoBank <?= date('Y') ?></span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">Powered by 
                    <a href="https://www.yiiframework.com/" target="_blank" class="text-decoration-none">
                        Yii Framework
                    </a>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" class="img-fluid" alt="" id="photoModalImage">
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>