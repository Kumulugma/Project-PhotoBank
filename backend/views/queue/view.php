<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
\backend\assets\AppAsset::registerControllerCss($this, 'queue');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
/* @var $this yii\web\View */
/* @var $model common\models\QueuedJob */

$this->title = 'Zadanie #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Kolejka zadań', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\QueuedJob::STATUS_PENDING => 'Oczekujące',
    \common\models\QueuedJob::STATUS_PROCESSING => 'Przetwarzanie',
    \common\models\QueuedJob::STATUS_COMPLETED => 'Zakończone',
    \common\models\QueuedJob::STATUS_FAILED => 'Błąd',
];

// Job type options
$jobTypeOptions = [
    's3_sync' => 'Synchronizacja S3',
    'regenerate_thumbnails' => 'Regeneracja Miniatur',
    'analyze_photo' => 'Analiza Zdjęcia',
    'analyze_batch' => 'Analiza Wsadowa',
    'import_photos' => 'Import Zdjęć',
];
?>
<div class="queued-job-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status === \common\models\QueuedJob::STATUS_FAILED): ?>
            <?= Html::a('Ponów', ['retry', 'id' => $model->id], [
    'class' => 'btn btn-primary',
    'data' => [
        'confirm' => 'Czy na pewno chcesz ponowić to zadanie?',
        'method' => 'post',
    ],
]) ?>
        <?php elseif ($model->status === \common\models\QueuedJob::STATUS_PENDING): ?>
            <?= Html::a('Przetwórz teraz', ['process', 'id' => $model->id], [
    'class' => 'btn btn-primary',
    'data' => [
        'confirm' => 'Czy na pewno chcesz przetworzyć to zadanie teraz?',
        'method' => 'post',
    ],
]) ?>
        <?php endif; ?>
        
        <?= Html::a('Usuń', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
        'confirm' => 'Czy na pewno chcesz usunąć to zadanie?',
        'method' => 'post',
    ],
]) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Szczegóły zadania</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'type',
                                'value' => $jobTypeOptions[$model->type] ?? $model->type,
                            ],
                            [
                                'attribute' => 'status',
                                'value' => $statusOptions[$model->status] ?? 'Unknown',
                                'format' => 'raw',
                                'value' => function ($model) use ($statusOptions) {
                                    $value = $statusOptions[$model->status] ?? 'Unknown';
                                    $class = '';
                                    switch ($model->status) {
                                        case \common\models\QueuedJob::STATUS_PENDING:
                                            $class = 'badge bg-warning text-dark';
                                            break;
                                        case \common\models\QueuedJob::STATUS_PROCESSING:
                                            $class = 'badge bg-primary';
                                            break;
                                        case \common\models\QueuedJob::STATUS_COMPLETED:
                                            $class = 'badge bg-success';
                                            break;
                                        case \common\models\QueuedJob::STATUS_FAILED:
                                            $class = 'badge bg-danger';
                                            break;
                                    }
                                    return '<span class="' . $class . '">' . $value . '</span>';
                                },
                            ],
                            'attempts',
                            [
                                'attribute' => 'created_at',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                            [
                                'attribute' => 'started_at',
                                'value' => $model->started_at ? date('Y-m-d H:i:s', $model->started_at) : 'Nie rozpoczęto',
                            ],
                            [
                                'attribute' => 'finished_at',
                                'value' => $model->finished_at ? date('Y-m-d H:i:s', $model->finished_at) : 'Nie zakończono',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Parametry zadania</h5>
                </div>
                <div class="card-body">
                    <?php
                    $params = json_decode($model->data, true);
                    if (empty($params)): ?>
                        <p class="text-muted mb-0">Brak parametrów</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped job-params-table">
                                <thead>
                                    <tr>
                                        <th>Parametr</th>
<th>Wartość</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($params as $key => $value): ?>
                                        <tr>
                                            <td><?= Html::encode($key) ?></td>
                                            <td>
                                                <?php if (is_array($value)): ?>
                                                    <pre><?= Html::encode(json_encode($value, JSON_PRETTY_PRINT)) ?></pre>
                                                <?php else: ?>
                                                    <?= Html::encode($value) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($model->status === \common\models\QueuedJob::STATUS_FAILED && !empty($model->error_message)): ?>
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Komunikat błędu</h5>
                    </div>
                    <div class="card-body">
                        <pre class="job-error-message"><?= Html::encode($model->error_message) ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php if ($model->type === 'import_photos' && property_exists($model, 'results') && !empty($model->results)):  
        $results = json_decode($model->results, true);
    ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Wyniki importu zdjęć</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Podsumowanie</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Katalog źródłowy
                                            <span class="badge bg-secondary"><?= isset($results['directory']) ? $results['directory'] : 'Nieznany' ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Importy rekurencyjny
                                            <span class="badge bg-info"><?= isset($results['recursive']) ? $results['recursive'] : 'Nie' ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Znaleziono plików
                                            <span class="badge bg-primary"><?= isset($results['files_found']) ? $results['files_found'] : 0 ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Zaimportowano
                                            <span class="badge bg-success"><?= isset($results['imported']) ? $results['imported'] : count($results['processed'] ?? []) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pominięto
                                            <span class="badge bg-warning"><?= isset($results['skipped']) ? $results['skipped'] : count($results['skipped'] ?? []) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Błędy
                                            <span class="badge bg-danger"><?= isset($results['errors']) ? (is_array($results['errors']) ? count($results['errors']) : $results['errors']) : 0 ?></span>
                                        </li>
                                        <?php if (isset($results['started_at'])): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Rozpoczęto
                                            <span class="badge bg-secondary"><?= $results['started_at'] ?></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($results['completed_at'])): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Zakończono
                                            <span class="badge bg-secondary"><?= $results['completed_at'] ?></span>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isset($results['error']) || isset($results['warning'])): ?>
                        <div class="col-md-6">
                            <?php if (isset($results['error'])): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Błąd</h5>
                                <p><?= $results['error'] ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($results['warning'])): ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-circle"></i> Ostrzeżenie</h5>
                                <p><?= $results['warning'] ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($results['processed'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5><i class="fas fa-check-circle text-success"></i> Pomyślnie zaimportowane pliki (<?= count($results['processed']) ?>)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID zdjęcia</th>
                                            <th>Plik źródłowy</th>
                                            <th>Nowa nazwa</th>
                                            <th>Wymiary</th>
                                            <th>Rozmiar</th>
                                            <th>Czas</th>
                                            <th>Akcje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['processed'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?= isset($item['photo_id']) ? $item['photo_id'] : 'N/A' ?>
                                            </td>
                                            <td>
                                                <?= isset($item['filename']) ? Html::encode($item['filename']) : (isset($item['source']) ? Html::encode(basename($item['source'])) : 'N/A') ?>
                                            </td>
                                            <td>
                                                <?= isset($item['new_filename']) ? Html::encode($item['new_filename']) : 'N/A' ?>
                                            </td>
                                            <td>
                                                <?php if (isset($item['width']) && isset($item['height'])): ?>
                                                    <?= $item['width'] ?> × <?= $item['height'] ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($item['file_size'])): ?>
                                                    <?= Yii::$app->formatter->asShortSize($item['file_size']) ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= isset($item['time']) ? $item['time'] : 'N/A' ?>
                                            </td>
                                            <td>
                                                <?php if (isset($item['photo_id'])): ?>
                                                    <?= Html::a('<i class="fas fa-eye"></i>', ['photos/view', 'id' => $item['photo_id']], [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'Zobacz zdjęcie',
                                                        'data-pjax' => 0,
                                                    ]) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($results['skipped'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5><i class="fas fa-step-forward text-warning"></i> Pominięte pliki (<?= count($results['skipped']) ?>)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Plik źródłowy</th>
                                            <th>Powód pominięcia</th>
                                            <th>Typ MIME</th>
                                            <th>Czas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['skipped'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?= isset($item['filename']) ? Html::encode($item['filename']) : (isset($item['source']) ? Html::encode(basename($item['source'])) : 'N/A') ?>
                                            </td>
                                            <td>
                                                <?= isset($item['reason']) ? Html::encode($item['reason']) : 'Nieznany powód' ?>
                                            </td>
                                            <td>
                                                <?= isset($item['mime_type']) ? Html::encode($item['mime_type']) : 'N/A' ?>
                                            </td>
                                            <td>
                                                <?= isset($item['time']) ? $item['time'] : 'N/A' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($results['errors'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5><i class="fas fa-times-circle text-danger"></i> Pliki z błędami (<?= count($results['errors']) ?>)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Plik źródłowy</th>
                                            <th>Błąd</th>
                                            <th>Czas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['errors'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?= isset($item['filename']) ? Html::encode($item['filename']) : (isset($item['source']) ? Html::encode(basename($item['source'])) : 'N/A') ?>
                                            </td>
                                            <td class="text-danger">
                                                <?= isset($item['error']) ? Html::encode($item['error']) : 'Nieznany błąd' ?>
                                            </td>
                                            <td>
                                                <?= isset($item['time']) ? $item['time'] : 'N/A' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>