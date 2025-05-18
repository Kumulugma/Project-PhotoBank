<?php
/* @var $this yii\web\View */
/* @var $randomPhotos common\models\Photo[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Zasobnik B';
?>
<div class="site-index">
    <!-- Hero section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Zasobnik B</h1>
            <p class="hero-subtitle">Twój osobisty bank zdjęć</p>
            <?php if (Yii::$app->user->isGuest): ?>
                <div class="hero-actions">
                    <?= Html::a('Zaloguj się', ['/site/login'], ['class' => 'btn btn-primary btn-lg']) ?>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <?= Html::a('Przeglądaj galerię', ['/gallery/index'], ['class' => 'btn btn-primary btn-lg']) ?>
                    <?= Html::a('Wyszukaj zdjęcia', ['/search/index'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($randomPhotos)): ?>
        <!-- Photo tiles section -->
        <section class="photo-tiles-section">
            <div class="container">
                <h2 class="section-title">Najnowsze zdjęcia</h2>
                <div class="photo-tiles">
                    <?php foreach ($randomPhotos as $index => $photo): ?>
                        <div class="photo-tile <?= $index === 0 ? 'photo-tile-large' : '' ?>" 
                             style="background-image: url(<?= $photo->thumbnails['medium'] ?>);">
                            <div class="photo-tile-overlay">
                                <div class="photo-tile-content">
                                    <h3 class="photo-tile-title"><?= Html::encode($photo->title) ?></h3>
                                    <?php if ($photo->description): ?>
                                        <p class="photo-tile-description">
                                            <?= Html::encode(mb_substr($photo->description, 0, 100)) ?>
                                            <?= mb_strlen($photo->description) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    <a href="<?= Url::to(['/gallery/view', 'id' => $photo->id]) ?>" 
                                       class="photo-tile-link">
                                        <i class="fas fa-eye"></i> Zobacz
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="text-center mt-5">
                <?= Html::a('Zobacz wszystkie zdjęcia', ['/gallery/index'], 
                    ['class' => 'btn btn-outline-primary btn-lg']) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>