<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */

$this->title = 'Rozmiar: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rozmiary miniatur', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="thumbnail-size-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#regenerateModal">
                <i class="fas fa-sync me-2"></i>Regeneruj
            </button>
            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Czy na pewno usunąć ten rozmiar? Wszystkie miniatury tego rozmiaru zostaną utracone.',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szczegóły rozmiaru
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
                                'label' => 'Nazwa rozmiaru',
                                'format' => 'raw',
                                'value' => '<code>' . Html::encode($model->name) . '</code>',
                            ],
                            [
                                'label' => 'Wymiary',
                                'format' => 'raw',
                                'value' => '<strong>' . $model->width . ' × ' . $model->height . '</strong> pikseli',
                            ],
                            [
                                'attribute' => 'crop',
                                'label' => 'Tryb kadrowania',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->crop) {
                                        return '<span class="badge bg-warning text-dark"><i class="fas fa-crop me-1"></i>Kadrowanie</span>';
                                    } else {
                                        return '<span class="badge bg-info"><i class="fas fa-expand-arrows-alt me-1"></i>Dopasowanie</span>';
                                    }
                                },
                            ],
                            [
                                'attribute' => 'watermark',
                                'label' => 'Znak wodny',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->watermark) {
                                        return '<span class="badge bg-success"><i class="fas fa-tint me-1"></i>Włączony</span>';
                                    } else {
                                        return '<span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Wyłączony</span>';
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
        
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statystyki użycia
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Count existing thumbnails
                    $thumbnailsDir = Yii::getAlias('@webroot/uploads/thumbnails/');
                    $pattern = $thumbnailsDir . $model->name . '_*';
                    $thumbnailFiles = glob($pattern);
                    $thumbnailCount = count($thumbnailFiles);
                    
                    // Total photos
                    $totalPhotos = \common\models\Photo::find()->count();
                    
                    // Calculate coverage
                    $coverage = $totalPhotos > 0 ? round(($thumbnailCount / $totalPhotos) * 100, 1) : 0;
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-primary mb-0"><?= $thumbnailCount ?></h3>
                            <small class="text-muted">Miniatur</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success mb-0"><?= $totalPhotos ?></h3>
                            <small class="text-muted">Zdjęć</small>
                        </div>
                        <div class="col-4">
                            <h3 class="<?= $coverage >= 90 ? 'text-success' : ($coverage >= 50 ? 'text-warning' : 'text-danger') ?> mb-0"><?= $coverage ?>%</h3>
                            <small class="text-muted">Pokrycie</small>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar <?= $coverage >= 90 ? 'bg-success' : ($coverage >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                             style="width: <?= $coverage ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Pokrycie miniatur</small>
                        <small class="text-muted"><?= $coverage ?>%</small>
                    </div>
                    
                    <?php if ($coverage < 100): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Nie wszystkie zdjęcia mają miniatury tego rozmiaru. Rozważ regenerację.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Podgląd rozmiaru
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="border rounded p-3" style="min-height: 200px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                        <?php
                        // Calculate preview size (max 200px)
                        $maxPreviewSize = 200;
                        $scale = min($maxPreviewSize / $model->width, $maxPreviewSize / $model->height);
                        $previewWidth = $model->width * $scale;
                        $previewHeight = $model->height * $scale;
                        ?>
                        <div style="width: <?= $previewWidth ?>px; height: <?= $previewHeight ?>px; border: 2px dashed #007bff; background: rgba(0, 123, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                            <span class="text-primary fw-bold"><?= $model->width ?>×<?= $model->height ?>px</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Proporcjonalny podgląd wymiarów</small>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Operacje
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#regenerateModal">
                            <i class="fas fa-sync me-2"></i>Regeneruj wszystkie miniatury
                        </button>
                        
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#regeneratePartialModal">
                            <i class="fas fa-play me-2"></i>Regeneruj brakujące
                        </button>
                        
                        <?= Html::a('<i class="fas fa-download me-2"></i>Export konfiguracji', ['export', 'id' => $model->id], [
                            'class' => 'btn btn-outline-secondary'
                        ]) ?>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <h6><i class="fas fa-info-circle me-2"></i>Informacja</h6>
                        <p class="mb-0">Regeneracja miniatur może zająć dużo czasu dla dużych kolekcji. 
                        Operacja zostanie wykonana w tle.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Regenerate All Modal -->
<div class="modal fade" id="regenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Regeneruj wszystkie miniatury</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'action' => ['regenerate'],
                'method' => 'post',
            ]); ?>
            <div class="modal-body">
                <input type="hidden" name="size_id" value="<?= $model->id ?>">
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Uwaga!</h6>
                    <p class="mb-0">Ta operacja usunie wszystkie istniejące miniatury rozmiaru "<?= $model->name ?>" 
                    i wygeneruje je ponownie dla wszystkich zdjęć.</p>
                </div>
                
                <p>Czy na pewno chcesz regenerować wszystkie miniatury rozmiaru <?= $model->width ?>×<?= $model->height ?>px?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync me-1"></i>Regeneruj wszystkie
                </button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Regenerate Partial Modal -->
<div class="modal fade" id="regeneratePartialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Regeneruj brakujące miniatury</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'action' => ['regenerate'],
                'method' => 'post',
            ]); ?>
            <div class="modal-body">
                <input type="hidden" name="size_id" value="<?= $model->id ?>">
                <input type="hidden" name="partial" value="1">
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Informacja</h6>
                    <p class="mb-0">Ta operacja wygeneruje miniatury tylko dla zdjęć, które jeszcze ich nie mają w tym rozmiarze.</p>
                </div>
                
                <p>Wygenerować brakujące miniatury rozmiaru <?= $model->width ?>×<?= $model->height ?>px?</p>
                
                <?php if ($coverage >= 100): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check me-2"></i>Wszystkie zdjęcia mają już miniatury tego rozmiaru.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-info" <?= $coverage >= 100 ? 'disabled' : '' ?>>
                    <i class="fas fa-play me-1"></i>Generuj brakujące
                </button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
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
</style>