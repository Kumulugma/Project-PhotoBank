<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
\backend\assets\AppAsset::registerControllerCss($this, 'categories');
/* @var $this yii\web\View */
/* @var $model common\models\Category */
/* @var $photosCount int */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Kategorie', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => $photosCount > 0 
                    ? "Ta kategoria zawiera {$photosCount} zdjęć. Czy na pewno chcesz ją usunąć?"
                    : 'Czy na pewno chcesz usunąć tę kategorię?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szczegóły kategorii
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
                            ],
                            [
                                'attribute' => 'name',
                                'label' => 'Nazwa kategorii',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'slug',
                                'label' => 'Slug URL',
                                'format' => 'raw',
                                'value' => '<code>' . Html::encode($model->slug) . '</code>',
                            ],
                            [
                                'attribute' => 'description',
                                'label' => 'Opis',
                                'format' => 'ntext',
                                'value' => $model->description ?: 'Brak opisu',
                            ],
                            [
                                'label' => 'Liczba zdjęć',
                                'format' => 'raw',
                                'value' => function($model) use ($photosCount) {
                                    if ($photosCount == 0) {
                                        return '<span class="badge bg-light text-dark">0</span>';
                                    }
                                    return Html::a(
                                        '<span class="badge bg-primary">' . $photosCount . '</span>',
                                        ['/photos/index', 'PhotoSearch[category]' => $model->id],
                                        ['title' => 'Pokaż zdjęcia w tej kategorii']
                                    );
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data utworzenia',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Ostatnia aktualizacja',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statystyki
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-primary mb-0"><?= $photosCount ?></h3>
                                <small class="text-muted">Zdjęć łącznie</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <?php
                            $publicPhotos = \common\models\Photo::find()
                                ->innerJoin('photo_category', 'photo.id = photo_category.photo_id')
                                ->where(['photo_category.category_id' => $model->id, 'photo.is_public' => 1])
                                ->count();
                            ?>
                            <h3 class="text-success mb-0"><?= $publicPhotos ?></h3>
                            <small class="text-muted">Publicznych</small>
                        </div>
                    </div>
                    
                    <?php if ($photosCount > 0): ?>
                        <hr class="my-3">
                        <div class="d-grid gap-2">
                            <?= Html::a('<i class="fas fa-images me-2"></i>Zobacz zdjęcia z tej kategorii', 
                                ['/photos/index', 'PhotoSearch[category]' => $model->id], 
                                ['class' => 'btn btn-outline-primary']) ?>
                            <?= Html::a('<i class="fas fa-eye me-2"></i>Podgląd na froncie', 
                                '/category/' . $model->slug, 
                                ['class' => 'btn btn-outline-info', 'target' => '_blank']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>Ostatnie zdjęcia
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $recentPhotos = \common\models\Photo::find()
                        ->innerJoin('photo_category', 'photo.id = photo_category.photo_id')
                        ->where(['photo_category.category_id' => $model->id])
                        ->orderBy(['photo.created_at' => SORT_DESC])
                        ->limit(6)
                        ->all();
                    
                    if (!empty($recentPhotos)): ?>
                        <div class="row g-2">
                            <?php foreach ($recentPhotos as $photo): 
                                $thumbnailSize = \common\models\ThumbnailSize::findOne(['name' => 'small']);
                                if ($thumbnailSize) {
                                    $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $thumbnailSize->name . '_' . $photo->file_name);
                                }
                            ?>
                                <div class="col-4">
                                    <a href="<?= yii\helpers\Url::to(['/photos/view', 'id' => $photo->id]) ?>" 
                                       class="d-block text-decoration-none">
                                        <?php if (isset($thumbnailUrl)): ?>
                                            <img src="<?= $thumbnailUrl ?>" 
                                                 class="img-fluid rounded" 
                                                 style="aspect-ratio: 1; object-fit: cover; width: 100%;"
                                                 title="<?= Html::encode($photo->title) ?>">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="aspect-ratio: 1;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($photosCount > 6): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Pokazano 6 z <?= $photosCount ?> zdjęć
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center p-3">
                            <i class="fas fa-image fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Brak zdjęć w tej kategorii</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-link me-2"></i>Powiązane kategorii
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $relatedCategories = \common\models\Category::find()
                        ->where(['!=', 'id', $model->id])
                        ->orderBy(['name' => SORT_ASC])
                        ->limit(5)
                        ->all();
                    
                    if (!empty($relatedCategories)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($relatedCategories as $category): 
                                $categoryPhotoCount = \common\models\PhotoCategory::find()
                                    ->where(['category_id' => $category->id])
                                    ->count();
                            ?>
                                <a href="<?= yii\helpers\Url::to(['view', 'id' => $category->id]) ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span><?= Html::encode($category->name) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $categoryPhotoCount ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Brak innych kategorii</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>