<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
\backend\assets\AppAsset::registerControllerCss($this, 'queue');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
/* @var $this yii\web\View */
/* @var $model common\models\QueuedJob */

$this->title = 'Job #' . $model->id;
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
            <?= Html::a('Retry', ['retry', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'data' => [
                    'confirm' => 'Are you sure you want to retry this job?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php elseif ($model->status === \common\models\QueuedJob::STATUS_PENDING): ?>
            <?= Html::a('Process Now', ['process', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'data' => [
                    'confirm' => 'Are you sure you want to process this job now?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this job?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Job Details</h3>
                </div>
                <div class="panel-body">
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
                                            $class = 'label label-default';
                                            break;
                                        case \common\models\QueuedJob::STATUS_PROCESSING:
                                            $class = 'label label-primary';
                                            break;
                                        case \common\models\QueuedJob::STATUS_COMPLETED:
                                            $class = 'label label-success';
                                            break;
                                        case \common\models\QueuedJob::STATUS_FAILED:
                                            $class = 'label label-danger';
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
                                'value' => $model->started_at ? date('Y-m-d H:i:s', $model->started_at) : 'Not started',
                            ],
                            [
                                'attribute' => 'finished_at',
                                'value' => $model->finished_at ? date('Y-m-d H:i:s', $model->finished_at) : 'Not completed',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Job Parameters</h3>
                </div>
                <div class="panel-body">
                    <?php
                    $params = json_decode($model->data, true);
                    if (empty($params)): ?>
                        <p class="text-muted">No parameters provided</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
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
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title">Error Message</h3>
                    </div>
                    <div class="panel-body">
                        <pre><?= Html::encode($model->error_message) ?></pre>
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Wyniki importu zdjęć</h3>
                </div>
                <div class="panel-body">
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