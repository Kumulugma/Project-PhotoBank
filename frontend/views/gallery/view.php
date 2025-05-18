<?php
/* @var $this yii\web\View */
/* @var $model common\models\Photo */
/* @var $prevPhoto common\models\Photo */
/* @var $nextPhoto common\models\Photo */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Galeria', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="photo-view">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="photo-main text-center">
                <img src="<?= $model->thumbnails['large'] ?>" class="img-fluid mb-3" alt="<?= Html::encode($model->title) ?>">
                
                <div class="navigation-buttons">
                    <?php if ($prevPhoto): ?>
                        <?= Html::a('<i class="fas fa-chevron-left"></i> Poprzednie', ['view', 'id' => $prevPhoto->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled><i class="fas fa-chevron-left"></i> Poprzednie</button>
                    <?php endif; ?>
                    
                    <?= Html::a('Wróć do galerii', ['index'], ['class' => 'btn btn-outline-secondary mx-2']) ?>
                    
                    <?php if ($nextPhoto): ?>
                        <?= Html::a('Następne <i class="fas fa-chevron-right"></i>', ['view', 'id' => $nextPhoto->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled>Następne <i class="fas fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informacje o zdjęciu</h5>
                </div>
                <div class="card-body">
                    <?php if ($model->description): ?>
                        <h6>Opis</h6>
                        <p><?= nl2br(Html::encode($model->description)) ?></p>
                        <hr>
                    <?php endif; ?>
                    
                    <h6>Szczegóły</h6>
                    <ul class="list-unstyled">
                        <li><strong>Wymiary:</strong> <?= $model->width ?> x <?= $model->height ?> px</li>
                        <li><strong>Data dodania:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at) ?></li>
                    </ul>
                    
                    <?php if ($model->categories): ?>
                        <h6>Kategorie</h6>
                        <div class="category-list mb-3">
                            <?php foreach ($model->categories as $category): ?>
                                <?= Html::a(Html::encode($category->name), ['/gallery/category', 'slug' => $category->slug], ['class' => 'category']) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->tags): ?>
                        <h6>Tagi</h6>
                        <div class="tag-list">
                            <?php foreach ($model->tags as $tag): ?>
                                <?= Html::a(Html::encode($tag->name), ['/gallery/tag', 'name' => $tag->name], ['class' => 'tag']) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>