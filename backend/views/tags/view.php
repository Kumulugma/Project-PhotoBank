<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
\backend\assets\AppAsset::registerControllerCss($this, 'tags');
/* @var $this yii\web\View */
/* @var $model common\models\Tag */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tagi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="fas fa-hashtag me-2"></i><?= Html::encode($this->title) ?>
        </h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => $model->frequency > 0 
                    ? "Ten tag jest używany w {$model->frequency} zdjęciach. Czy na pewno chcesz go usunąć?"
                    : 'Czy na pewno chcesz usunąć ten tag?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szczegóły tagu
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
                                'label' => 'Nazwa tagu',
                                'format' => 'raw',
                                'value' => '<span class="badge bg-info fs-6">#' . Html::encode($model->name) . '</span>',
                            ],
                            [
                                'attribute' => 'frequency',
                                'label' => 'Popularność',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $frequency = $model->frequency;
                                    if ($frequency == 0) {
                                        return '<span class="badge bg-light text-dark">' . $frequency . ' użyć</span>';
                                    } elseif ($frequency < 5) {
                                        return '<span class="badge bg-warning text-dark">' . $frequency . ' użyć</span>';
                                    } elseif ($frequency < 20) {
                                        return '<span class="badge bg-primary">' . $frequency . ' użyć</span>';
                                    } else {
                                        return '<span class="badge bg-success">' . $frequency . ' użyć</span>';
                                    }
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
                        <i class="fas fa-chart-bar me-2"></i>Statystyki tagu
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-primary mb-0"><?= $model->frequency ?></h3>
                                <small class="text-muted">Użyć łącznie</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <?php
                            $publicPhotos = \common\models\Photo::find()
                                ->innerJoin('photo_tag', 'photo.id = photo_tag.photo_id')
                                ->where(['photo_tag.tag_id' => $model->id, 'photo.is_public' => 1])
                                ->count();
                            ?>
                            <h3 class="text-success mb-0"><?= $publicPhotos ?></h3>
                            <small class="text-muted">Publicznych</small>
                        </div>
                    </div>
                    
                    <?php if ($model->frequency > 0): ?>
                        <hr class="my-3">
                        <div class="d-grid gap-2">
                            <?= Html::a('<i class="fas fa-images me-2"></i>Zobacz zdjęcia z tym tagiem', 
                                ['/photos/index', 'PhotoSearch[tag]' => $model->id], 
                                ['class' => 'btn btn-outline-primary']) ?>
                            <?= Html::a('<i class="fas fa-eye me-2"></i>Podgląd na froncie', 
                                '/tag/' . urlencode($model->name), 
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
                        ->innerJoin('photo_tag', 'photo.id = photo_tag.photo_id')
                        ->where(['photo_tag.tag_id' => $model->id])
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
                        
                        <?php if ($model->frequency > 6): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Pokazano 6 z <?= $model->frequency ?> zdjęć
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center p-3">
                            <i class="fas fa-image fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Brak zdjęć z tym tagiem</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Popularne tagi
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $popularTags = \common\models\Tag::find()
                        ->where(['!=', 'id', $model->id])
                        ->orderBy(['frequency' => SORT_DESC])
                        ->limit(8)
                        ->all();
                    
                    if (!empty($popularTags)): ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($popularTags as $tag): ?>
                                <a href="<?= yii\helpers\Url::to(['view', 'id' => $tag->id]) ?>" 
                                   class="badge bg-secondary text-decoration-none">
                                    #<?= Html::encode($tag->name) ?> (<?= $tag->frequency ?>)
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Brak innych tagów</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.detail-view th {
    width: 200px;
    font-weight: 600;
    background-color: #f8f9fa;
}

.detail-view td {
    word-break: break-word;
}

.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.img-fluid:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.badge {
    transition: transform 0.1s ease;
}

.badge:hover {
    transform: scale(1.05);
}
</style>