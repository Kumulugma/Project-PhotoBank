<?php
/* @var $model common\models\Photo */
/* @var $index integer */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<article class="photo-item reveal-on-scroll" 
         data-photo-id="<?= $model->id ?>"
         itemscope 
         itemtype="https://schema.org/Photograph">
    
    <!-- Photo Image Container -->
    <div class="photo-image">
        <img src="<?= $model->thumbnails['medium'] ?>" 
             data-large="<?= $model->thumbnails['large'] ?>"
             alt="<?= Html::encode($model->title) ?>"
             loading="lazy"
             class="photo-main-image"
             itemprop="image"
             width="<?= $model->width ?>"
             height="<?= $model->height ?>" />
        
        <!-- Overlay with Actions -->
        <div class="photo-overlay">
            <div class="photo-actions">
                <?= Html::a(
                    '<i class="fas fa-eye" aria-hidden="true"></i><span class="sr-only">Zobacz szczegóły</span>', 
                    ['/gallery/view', 'id' => $model->id], 
                    [
                        'class' => 'btn btn-primary btn-sm',
                        'title' => 'Zobacz szczegóły zdjęcia',
                        'aria-label' => 'Zobacz szczegóły zdjęcia: ' . Html::encode($model->title),
                        'encode' => false
                    ]
                ) ?>
                
                <button type="button" 
                        class="btn btn-secondary btn-sm photo-modal-trigger" 
                        title="Podgląd zdjęcia"
                        aria-label="Pokaż podgląd zdjęcia: <?= Html::encode($model->title) ?>">
                    <i class="fas fa-expand" aria-hidden="true"></i>
                    <span class="sr-only">Podgląd</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Photo Content -->
    <div class="photo-content">
        <!-- Title -->
        <header class="photo-header">
            <h3 class="photo-title" itemprop="name">
                <?= Html::a(
                    Html::encode($model->title), 
                    ['/gallery/view', 'id' => $model->id],
                    ['itemprop' => 'url']
                ) ?>
            </h3>
        </header>
        
        <!-- Description -->
        <?php if ($model->description): ?>
            <div class="photo-description" itemprop="description">
                <?= Html::encode(mb_substr($model->description, 0, 120)) ?>
                <?= mb_strlen($model->description) > 120 ? '...' : '' ?>
            </div>
        <?php endif; ?>
        
        <!-- Categories -->
        <?php if ($model->categories): ?>
            <nav class="photo-categories" aria-label="Kategorie zdjęcia">
                <?php foreach ($model->categories as $category): ?>
                    <?= Html::a(
                        Html::encode($category->name), 
                        ['/gallery/category', 'slug' => $category->slug], 
                        [
                            'class' => 'category',
                            'rel' => 'tag',
                            'title' => 'Zobacz więcej zdjęć w kategorii: ' . Html::encode($category->name)
                        ]
                    ) ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        
        <!-- Tags -->
        <?php if ($model->tags): ?>
            <nav class="photo-tags" aria-label="Tagi zdjęcia">
                <div class="tags">
                    <?php foreach ($model->tags as $tag): ?>
                        <?= Html::a(
                            Html::encode($tag->name), 
                            ['/gallery/tag', 'name' => $tag->name], 
                            [
                                'class' => 'tag',
                                'rel' => 'tag',
                                'title' => 'Zobacz więcej zdjęć z tagiem: ' . Html::encode($tag->name)
                            ]
                        ) ?>
                    <?php endforeach; ?>
                </div>
            </nav>
        <?php endif; ?>
        
        <!-- Metadata -->
        <footer class="photo-meta">
            <div class="photo-date">
                <i class="fas fa-calendar" aria-hidden="true"></i>
                <time datetime="<?= Yii::$app->formatter->asDatetime($model->created_at, 'php:Y-m-d') ?>" 
                      itemprop="dateCreated">
                    <?= Yii::$app->formatter->asDate($model->created_at) ?>
                </time>
            </div>
            
            <div class="photo-dimensions">
                <i class="fas fa-expand-arrows-alt" aria-hidden="true"></i>
                <span itemprop="width"><?= $model->width ?></span> × 
                <span itemprop="height"><?= $model->height ?></span>
            </div>
        </footer>
    </div>

    <!-- Structured Data -->
    <meta itemprop="author" content="Kumulugma">
    <meta itemprop="copyrightHolder" content="Kumulugma">
    <meta itemprop="uploadDate" content="<?= Yii::$app->formatter->asDatetime($model->created_at, 'php:Y-m-d') ?>">
</article>