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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
   <?php
   NavBar::begin([
       'brandLabel' => Yii::$app->name,
       'brandUrl' => Yii::$app->homeUrl,
       'options' => [
           'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
       ],
   ]);
   $menuItems = [
       ['label' => 'Strona główna', 'url' => ['/site/index']],
       ['label' => 'Galeria', 'url' => ['/gallery/index']],
       ['label' => 'Wyszukiwanie', 'url' => ['/search/index']],
       ['label' => 'O nas', 'url' => ['/site/about']],
       ['label' => 'Kontakt', 'url' => ['/site/contact']],
   ];
   if (Yii::$app->user->isGuest) {
       $menuItems[] = ['label' => 'Rejestracja', 'url' => ['/site/signup']];
       $menuItems[] = ['label' => 'Logowanie', 'url' => ['/site/login']];
   } else {
       $menuItems[] = [
           'label' => 'Witaj, ' . Yii::$app->user->identity->username,
           'items' => [
               ['label' => 'Panel administratora', 'url' => ['/admin'], 'visible' => Yii::$app->user->can('managePhotos')],
               ['label' => 'Wyloguj', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
           ],
       ];
   }
   echo Nav::widget([
       'options' => ['class' => 'navbar-nav ms-auto'],
       'items' => $menuItems,
   ]);
   NavBar::end();
   ?>
</header>

<main role="main" class="flex-shrink-0">
   <div class="container">
       <?= Breadcrumbs::widget([
           'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
       ]) ?>
       <?= Alert::widget() ?>
       <?= $content ?>
   </div>
</main>

<footer class="footer mt-auto py-3 text-muted">
   <div class="container">
       <div class="row">
           <div class="col-md-4">
               <h5>PersonalPhotoBank</h5>
               <p>Twój osobisty bank zdjęć.</p>
               <div class="social-links">
                   <a href="<?= Yii::$app->params['socialMedia']['facebook'] ?? '#' ?>" target="_blank" class="me-2"><i class="fab fa-facebook"></i></a>
                   <a href="<?= Yii::$app->params['socialMedia']['instagram'] ?? '#' ?>" target="_blank" class="me-2"><i class="fab fa-instagram"></i></a>
                   <a href="<?= Yii::$app->params['socialMedia']['twitter'] ?? '#' ?>" target="_blank"><i class="fab fa-twitter"></i></a>
               </div>
           </div>
           <div class="col-md-4">
               <h5>Linki</h5>
               <ul class="list-unstyled">
                   <li><?= Html::a('Strona główna', ['/site/index']) ?></li>
                   <li><?= Html::a('Galeria', ['/gallery/index']) ?></li>
                   <li><?= Html::a('Wyszukiwanie', ['/search/index']) ?></li>
                   <li><?= Html::a('O nas', ['/site/about']) ?></li>
                   <li><?= Html::a('Kontakt', ['/site/contact']) ?></li>
               </ul>
           </div>
           <div class="col-md-4">
               <h5>Informacje prawne</h5>
               <ul class="list-unstyled">
                   <li><?= Html::a('Regulamin', ['/site/terms']) ?></li>
                   <li><?= Html::a('Polityka prywatności', ['/site/privacy']) ?></li>
               </ul>
           </div>
       </div>
       <hr>
       <p class="float-start">&copy; PersonalPhotoBank <?= date('Y') ?></p>
       <p class="float-end">Powered by <a href="https://www.yiiframework.com/" target="_blank">Yii Framework</a></p>
   </div>
</footer>

<!-- Modal dla powiększania zdjęć -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="imageModalLabel"></h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body text-center">
               <img src="" class="img-fluid" alt="Powiększone zdjęcie">
           </div>
       </div>
   </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();