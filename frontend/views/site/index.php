<?php
/* @var $this yii\web\View */
/* @var $latestPhotos common\models\Photo[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Strona główna';
?>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">PersonalPhotoBank</h1>
        <p class="lead">Twój osobisty bank zdjęć. Przechowuj, organizuj i udostępniaj swoje zdjęcia w jednym miejscu.</p>
        <p>
            <?= Html::a('Przeglądaj galerię', ['/gallery/index'], ['class' => 'btn btn-lg btn-primary']) ?>
            <?= Html::a('Wyszukaj zdjęcia', ['/search/index'], ['class' => 'btn btn-lg btn-outline-secondary ms-2']) ?>
        </p>
    </div>

    <div class="body-content">
        <?php if (!empty($latestPhotos)): ?>
            <h2 class="mb-4">Najnowsze zdjęcia</h2>
            <div class="row">
                <?php foreach ($latestPhotos as $photo): ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card h-100">
                            <a href="<?= Url::to(['/gallery/view', 'id' => $photo->id]) ?>">
                                <img src="<?= $photo->thumbnails['medium'] ?>" class="card-img-top" alt="<?= Html::encode($photo->title) ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?= Html::encode($photo->title) ?></h5>
                                <?php if ($photo->description): ?>
                                    <p class="card-text"><?= Html::encode(mb_substr($photo->description, 0, 100)) . (mb_strlen($photo->description) > 100 ? '...' : '') ?></p>
                                <?php endif; ?>
                                <a href="<?= Url::to(['/gallery/view', 'id' => $photo->id]) ?>" class="btn btn-sm btn-primary">Zobacz więcej</a>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Dodano: <?= Yii::$app->formatter->asDate($photo->created_at) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <?= Html::a('Zobacz więcej zdjęć', ['/gallery/index'], ['class' => 'btn btn-outline-primary']) ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>Brak publicznych zdjęć w galerii.</p>
            </div>
        <?php endif; ?>
        
        <hr class="my-5">
        
        <div class="row mt-5">
            <div class="col-md-4">
                <h2><i class="fas fa-cloud-upload-alt"></i> Przesyłaj</h2>
                <p>Łatwo przesyłaj swoje zdjęcia do banku. Obsługujemy różne formaty i duże pliki. Wgraj nawet setki zdjęć jednocześnie.</p>
            </div>
            <div class="col-md-4">
                <h2><i class="fas fa-tags"></i> Organizuj</h2>
                <p>Kategoryzuj zdjęcia za pomocą tagów i albumów. Automatyczne tagowanie z wykorzystaniem AI pomoże Ci szybko uporządkować kolekcję.</p>
            </div>
            <div class="col-md-4">
                <h2><i class="fas fa-share-alt"></i> Udostępniaj</h2>
                <p>Wybierz, które zdjęcia chcesz udostępnić publicznie. Kontroluj swoją prywatność i bezpiecznie dziel się wspomnieniami z innymi.</p>
            </div>
        </div>
    </div>
</div>