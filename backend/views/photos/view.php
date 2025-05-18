<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Photo */
/* @var $thumbnails array */
/* @var $tags array */
/* @var $categories array */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'W kolejce',
    \common\models\Photo::STATUS_ACTIVE => 'Aktywne',
    \common\models\Photo::STATUS_DELETED => 'Usunięte',
];
?>
<div class="photo-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista zdjęć', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="mb-4">
        <div class="btn-group me-2">
            <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>
            
            <?php if ($model->status === \common\models\Photo::STATUS_QUEUE): ?>
                <?= Html::a('<i class="fas fa-check me-2"></i>Zatwierdź', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'Czy na pewno chcesz zatwierdzić to zdjęcie? Zostanie przeniesione do magazynu S3.',
                    'data-method' => 'post',
                ]) ?>
            <?php endif; ?>
            
            <?= Html::a('<i class="fas fa-robot me-2"></i>Analiza AI', ['/ai/analyze-photo', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data-method' => 'post',
                'title' => 'Uruchom analizę AI tego zdjęcia',
            ]) ?>
        </div>
        
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Czy na pewno chcesz usunąć to zdjęcie?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <!-- Main content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szczegóły zdjęcia
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
                                'attribute' => 'title',
                                'label' => 'Tytuł',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'description',
                                'label' => 'Opis',
                                'format' => 'ntext',
                                'value' => $model->description ?: 'Brak opisu',
                            ],
                            [
                                'attribute' => 'file_name',
                                'label' => 'Nazwa pliku',
                                'format' => 'text',
                            ],
                            [
                                'label' => 'Wymiary',
                                'value' => $model->width . ' × ' . $model->height . ' px',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'file_size',
                                'label' => 'Rozmiar pliku',
                                'value' => Yii::$app->formatter->asShortSize($model->file_size, 2),
                            ],
                            [
                                'attribute' => 'mime_type',
                                'label' => 'Typ MIME',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Status',
                                'format' => 'raw',
                                'value' => function($model) use ($statusOptions) {
                                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                                    $badgeClass = match($model->status) {
                                        \common\models\Photo::STATUS_QUEUE => 'bg-warning',
                                        \common\models\Photo::STATUS_ACTIVE => 'bg-success',
                                        \common\models\Photo::STATUS_DELETED => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'is_public',
                                'label' => 'Widoczność',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $class = $model->is_public ? 'bg-success' : 'bg-secondary';
                                    $text = $model->is_public ? 'Publiczne' : 'Prywatne';
                                    $icon = $model->is_public ? 'fa-eye' : 'fa-eye-slash';
                                    return '<span class="badge ' . $class . '"><i class="fas ' . $icon . ' me-1"></i>' . $text . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data utworzenia',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Data modyfikacji',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                            [
                                'attribute' => 'created_by',
                                'label' => 'Utworzone przez',
                                'value' => function ($model) {
                                    $user = \common\models\User::findOne($model->created_by);
                                    return $user ? $user->username : 'Nieznany użytkownik';
                                },
                            ],
                            [
                                'attribute' => 's3_path',
                                'label' => 'Ścieżka S3',
                                'format' => 'ntext',
                                'visible' => !empty($model->s3_path),
                                'value' => $model->s3_path ?: 'Nie przesłano do S3',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>Podgląd
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if (isset($thumbnails['medium'])): ?>
                        <img src="<?= $thumbnails['medium'] ?>" alt="<?= Html::encode($model->title) ?>" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px; cursor: pointer;"
                             data-bs-toggle="modal" data-bs-target="#imageModal">
                    <?php elseif (isset($thumbnails['small'])): ?>
                        <img src="<?= $thumbnails['small'] ?>" alt="<?= Html::encode($model->title) ?>" 
                             class="img-fluid rounded shadow-sm"
                             style="max-height: 300px; cursor: pointer;"
                             data-bs-toggle="modal" data-bs-target="#imageModal">
                    <?php else: ?>
                        <div class="text-muted p-5">
                            <i class="fas fa-image fa-4x mb-3"></i>
                            <p>Podgląd niedostępny</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Categories -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder me-2"></i>Kategorie
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted mb-0">Brak przypisanych kategorii</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($categories as $category): ?>
                                <span class="badge bg-primary">
                                    <i class="fas fa-folder me-1"></i><?= Html::encode($category->name) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Tagi
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tags)): ?>
                        <p class="text-muted mb-0">Brak przypisanych tagów</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-tag me-1"></i><?= Html::encode($tag->name) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Available Thumbnails -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>Dostępne miniatury
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($thumbnails)): ?>
                        <p class="text-muted mb-0">Brak dostępnych miniatur</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($thumbnails as $size => $url): ?>
                                <a href="<?= $url ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="fas fa-external-link-alt me-2 text-primary"></i>
                                    <span class="flex-grow-1"><?= ucfirst($size) ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-download"></i>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Html::encode($model->title) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <?php if (isset($thumbnails['large'])): ?>
                    <img src="<?= $thumbnails['large'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid">
                <?php elseif (isset($thumbnails['medium'])): ?>
                    <img src="<?= $thumbnails['medium'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid">
                <?php endif; ?>
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

.list-group-item-action:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.875em;
}

.img-fluid {
    transition: transform 0.2s ease;
}

.img-fluid:hover {
    transform: scale(1.02);
}
</style>