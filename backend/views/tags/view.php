<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Photo;
use common\helpers\PathHelper;

\backend\assets\AppAsset::registerControllerCss($this, 'tags');

$this->title = '#' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tagi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Podstawowe statystyki
$publicPhotos = Photo::find()
    ->innerJoin('photo_tag', 'photo.id = photo_tag.photo_id')
    ->where(['photo_tag.tag_id' => $model->id, 'photo.is_public' => 1])
    ->count();

$recentPhotos = Photo::find()
    ->innerJoin('photo_tag', 'photo.id = photo_tag.photo_id')
    ->where(['photo_tag.tag_id' => $model->id])
    ->orderBy(['photo.created_at' => SORT_DESC])
    ->limit(8)
    ->all();

$daysSinceCreated = ceil((time() - $model->created_at) / 86400);
?>

<div class="tag-view">
    <!-- Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-hashtag me-3"></i>
                        <?= Html::encode($model->name) ?>
                    </h1>
                    <p class="subtitle mb-0">
                        <?php if ($model->frequency > 0): ?>
                            Używany w <?= $model->frequency ?> <?= $model->frequency == 1 ? 'zdjęciu' : 'zdjęciach' ?>
                        <?php else: ?>
                            Tag oczekuje na pierwsze użycie
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
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
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Statystyki -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value"><?= $model->frequency ?></div>
                <div class="stat-label">
                    <i class="fas fa-images me-2"></i>
                    Użyć łącznie
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $publicPhotos ?></div>
                <div class="stat-label">
                    <i class="fas fa-globe me-2"></i>
                    Publicznych
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $model->frequency - $publicPhotos ?></div>
                <div class="stat-label">
                    <i class="fas fa-lock me-2"></i>
                    Prywatnych
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $daysSinceCreated ?></div>
                <div class="stat-label">
                    <i class="fas fa-calendar me-2"></i>
                    Dni istnienia
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Główna zawartość -->
            <div class="col-lg-8">
                <!-- Szczegóły tagu -->
                <div class="content-card mb-4">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-info-circle me-2"></i>
                            Szczegóły tagu
                        </h4>
                    </div>
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table table-striped'],
                            'attributes' => [
                                [
                                    'attribute' => 'id',
                                    'label' => 'ID',
                                ],
                                [
                                    'attribute' => 'name',
                                    'label' => 'Nazwa',
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
                                            $badge = '<span class="badge bg-light text-dark">' . $frequency . ' użyć</span>';
                                        } elseif ($frequency < 5) {
                                            $badge = '<span class="badge bg-warning">' . $frequency . ' użyć</span>';
                                        } elseif ($frequency < 20) {
                                            $badge = '<span class="badge bg-primary">' . $frequency . ' użyć</span>';
                                        } else {
                                            $badge = '<span class="badge bg-success">' . $frequency . ' użyć</span>';
                                        }
                                        
                                        return $badge . '<div class="popularity-bar mt-2"><div class="popularity-fill" style="width: ' . min(($frequency / 50) * 100, 100) . '%"></div></div>';
                                    },
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'label' => 'Utworzono',
                                    'value' => date('Y-m-d H:i:s', $model->created_at),
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'label' => 'Zaktualizowano',
                                    'value' => date('Y-m-d H:i:s', $model->updated_at),
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>

                <!-- Zdjęcia z tagiem -->
                <?php if (!empty($recentPhotos)): ?>
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>
                            <i class="fas fa-images me-2"></i>
                            Zdjęcia z tym tagiem
                        </h4>
                        <?php if ($model->frequency > 8): ?>
                        <div>
                            <?= Html::a('Zobacz wszystkie', ['/photos/index', 'PhotoSearch[tag]' => $model->id], [
                                'class' => 'btn btn-outline-primary btn-sm'
                            ]) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="photo-grid">
                            
                            <?php foreach ($recentPhotos as $photo): 
                                $thumbnailSize = \common\models\ThumbnailSize::findOne(['name' => 'small']);
                                $thumbnailUrl = PathHelper::getAvailableThumbnail('medium', $photo->file_name);
                                $thumbnailUrl = is_array($thumbnailUrl)? $thumbnailUrl['url']: null;
                                
                            ?>
                                <div class="photo-card">
                                    <?php if ($thumbnailUrl): ?>
                                        <img src="<?= $thumbnailUrl ?>" alt="<?= Html::encode($photo->title ?: 'Bez tytułu') ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-2">
                                        <div class="d-flex gap-1">
                                            <a href="<?= yii\helpers\Url::to(['/photos/view', 'id' => $photo->id]) ?>" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= yii\helpers\Url::to(['/photos/update', 'id' => $photo->id]) ?>" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($model->frequency > 8): ?>
                            <div class="text-center mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    Pokazano 8 z <?= $model->frequency ?> zdjęć
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="content-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Brak zdjęć z tym tagiem</h5>
                        <p class="text-muted">Ten tag nie został jeszcze użyty</p>
                        <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj zdjęcie', ['/photos/create'], [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Szybkie akcje -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-bolt me-2"></i>
                        Akcje
                    </h5>
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj tag', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary'
                        ]) ?>
                        
                        <?php if ($model->frequency > 0): ?>
                            <?= Html::a('<i class="fas fa-images me-2"></i>Zobacz zdjęcia', 
                                ['/photos/index', 'PhotoSearch[tag]' => $model->id], 
                                ['class' => 'btn btn-success']) ?>
                        <?php endif; ?>
                        
                        <?= Html::a('<i class="fas fa-copy me-2"></i>Kopiuj nazwę', '#', [
                            'class' => 'btn btn-secondary',
                            'onclick' => 'copyToClipboard("#' . $model->name . '"); return false;'
                        ]) ?>
                    </div>
                </div>

                <!-- Statystyki -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-chart-bar me-2"></i>
                        Analityka
                    </h5>
                    
                    <?php if ($model->frequency > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Publiczne</small>
                            <small><?= $publicPhotos ?> (<?= round(($publicPhotos / $model->frequency) * 100) ?>%)</small>
                        </div>
                        <div class="popularity-bar">
                            <div class="popularity-fill" style="width: <?= ($publicPhotos / $model->frequency) * 100 ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="fw-bold text-info"><?= $daysSinceCreated ?></div>
                            <small class="text-muted">Dni istnienia</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-success">
                                <?= $model->frequency > 0 ? number_format($model->frequency / max($daysSinceCreated, 1), 2) : '0.00' ?>
                            </div>
                            <small class="text-muted">Użyć/dzień</small>
                        </div>
                    </div>
                </div>

                <!-- Informacje -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-info-circle me-2"></i>
                        Informacje
                    </h5>
                    
                    <?php if ($model->frequency > 0): ?>
                    <div class="alert alert-info">
                        <h6>Status: Aktywny</h6>
                        <p class="mb-0">Tag jest używany w systemie</p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <h6>Status: Nieaktywny</h6>
                        <p class="mb-0">Tag nie jest jeszcze używany</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="small text-muted">
                        <div><strong>Utworzono:</strong> <?= date('Y-m-d H:i', $model->created_at) ?></div>
                        <div><strong>Zaktualizowano:</strong> <?= date('Y-m-d H:i', $model->updated_at) ?></div>
                        <div><strong>URL:</strong> /tag/<?= urlencode($model->name) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funkcja kopiowania do schowka
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showSimpleToast('Skopiowano: ' + text, 'success');
        });
    } else {
        // Fallback dla starszych przeglądarek
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showSimpleToast('Skopiowano: ' + text, 'success');
    }
}

// Animacja pasków po załadowaniu
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const bars = document.querySelectorAll('.popularity-fill');
        bars.forEach(function(bar) {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function() {
                bar.style.width = width;
            }, 100);
        });
    }, 500);
});
</script>