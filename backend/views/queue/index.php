<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\QueuedJobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $stats array */

$this->title = 'Kolejka zadań';
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\QueuedJob::STATUS_PENDING => 'Oczekuje',
    \common\models\QueuedJob::STATUS_PROCESSING => 'Przetwarzanie',
    \common\models\QueuedJob::STATUS_COMPLETED => 'Zakończone',
    \common\models\QueuedJob::STATUS_FAILED => 'Błąd',
];

// Job type options
$jobTypeOptions = [
    's3_sync' => 'Synchronizacja S3',
    'regenerate_thumbnails' => 'Regeneracja miniatur',
    'analyze_photo' => 'Analiza zdjęcia',
    'analyze_batch' => 'Analiza wsadowa',
    'import_photos' => 'Import zdjęć',
];
?>
<div class="queued-job-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-plus me-2"></i>Nowe zadanie', ['create'], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('<i class="fas fa-play me-2"></i>Uruchom procesor', ['run', 'limit' => 5], [
                'class' => 'btn btn-primary',
                'data-confirm' => 'Czy chcesz przetworzyć do 5 oczekujących zadań?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-broom me-2"></i>Wyczyść zakończone', ['clear-completed'], [
                'class' => 'btn btn-outline-secondary',
                'data-confirm' => 'Czy na pewno usunąć wszystkie zakończone zadania?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-trash me-2"></i>Wyczyść błędne', ['clear-failed'], [
                'class' => 'btn btn-outline-danger',
                'data-confirm' => 'Czy na pewno usunąć wszystkie błędne zadania?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body text-center">
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $stats['pending'] ?></h3>
                    <small class="text-muted">Oczekujące</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body text-center">
                    <div class="text-primary">
                        <i class="fas fa-cog fa-spin fa-2x"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $stats['processing'] ?></h3>
                    <small class="text-muted">Przetwarzane</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-success border-4">
                <div class="card-body text-center">
                    <div class="text-success">
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $stats['completed'] ?></h3>
                    <small class="text-muted">Zakończone</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-danger border-4">
                <div class="card-body text-center">
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $stats['failed'] ?></h3>
                    <small class="text-muted">Błędne</small>
                </div>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'queue-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'fw-bold'],
            ],
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function ($model) use ($jobTypeOptions) {
                    $type = $jobTypeOptions[$model->type] ?? $model->type;
                    $iconClass = match($model->type) {
                        's3_sync' => 'fab fa-aws',
                        'regenerate_thumbnails' => 'fas fa-image',
                        'analyze_photo' => 'fas fa-robot',
                        'analyze_batch' => 'fas fa-robots',
                        'import_photos' => 'fas fa-download',
                        default => 'fas fa-cog'
                    };
                    return '<i class="' . $iconClass . ' me-2"></i>' . Html::encode($type);
                },
                'filter' => Html::activeDropDownList($searchModel, 'type', $jobTypeOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie typy'
                ]),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) use ($statusOptions) {
                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                    $badgeClass = match($model->status) {
                        \common\models\QueuedJob::STATUS_PENDING => 'bg-warning text-dark',
                        \common\models\QueuedJob::STATUS_PROCESSING => 'bg-primary',
                        \common\models\QueuedJob::STATUS_COMPLETED => 'bg-success',
                        \common\models\QueuedJob::STATUS_FAILED => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    
                    $icon = match($model->status) {
                        \common\models\QueuedJob::STATUS_PENDING => 'fa-clock',
                        \common\models\QueuedJob::STATUS_PROCESSING => 'fa-cog fa-spin',
                        \common\models\QueuedJob::STATUS_COMPLETED => 'fa-check',
                        \common\models\QueuedJob::STATUS_FAILED => 'fa-times',
                        default => 'fa-question'
                    };
                    
                    return '<span class="badge ' . $badgeClass . '"><i class="fas ' . $icon . ' me-1"></i>' . $status . '</span>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'status', $statusOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie statusy'
                ]),
                'headerOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'attribute' => 'attempts',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => function($model) {
                    if ($model->attempts > 3) {
                        return ['class' => 'text-danger fw-bold text-center'];
                    } elseif ($model->attempts > 1) {
                        return ['class' => 'text-warning fw-bold text-center'];
                    }
                    return ['class' => 'text-center'];
                },
            ],
            [
                'label' => 'Czas wykonania',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->started_at && $model->completed_at) {
                        $duration = $model->completed_at - $model->started_at;
                        if ($duration < 60) {
                            return '<span class="text-success">' . $duration . 's</span>';
                        } elseif ($duration < 3600) {
                            return '<span class="text-warning">' . round($duration/60, 1) . 'm</span>';
                        } else {
                            return '<span class="text-danger">' . round($duration/3600, 1) . 'h</span>';
                        }
                    } elseif ($model->started_at && $model->status === \common\models\QueuedJob::STATUS_PROCESSING) {
                        $duration = time() - $model->started_at;
                        return '<span class="text-info">Trwa: ' . round($duration/60, 1) . 'm</span>';
                    }
                    return '<span class="text-muted">-</span>';
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 100px;'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<span title="' . date('Y-m-d H:i:s', $model->created_at) . '">' . 
                           Yii::$app->formatter->asRelativeTime($model->created_at) . '</span>';
                },
                'filter' => Html::activeTextInput($searchModel, 'created_at', [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD'
                ]),
                'headerOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {retry} {process} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Zobacz',
                            'data-pjax' => 0,
                        ]);
                    },
                    'retry' => function ($url, $model, $key) {
                        if ($model->status === \common\models\QueuedJob::STATUS_FAILED) {
                            return Html::a('<i class="fas fa-redo"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-warning',
                                'title' => 'Ponów',
                                'data-method' => 'post',
                                'data-pjax' => 0,
                            ]);
                        }
                        return '';
                    },
                    'process' => function ($url, $model, $key) {
                        if ($model->status === \common\models\QueuedJob::STATUS_PENDING) {
                            return Html::a('<i class="fas fa-play"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-success',
                                'title' => 'Przetwórz teraz',
                                'data-method' => 'post',
                                'data-pjax' => 0,
                                'data-confirm' => 'Czy na pewno uruchomić to zadanie teraz?'
                            ]);
                        }
                        return '';
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Usuń',
                            'data-confirm' => 'Czy na pewno usunąć to zadanie?',
                            'data-method' => 'post',
                            'data-pjax' => 0,
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width: 150px;'],
                'contentOptions' => ['class' => 'text-end'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    
    <div class="mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>O kolejce zadań
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p>Kolejka zadań obsługuje operacje wykonywane w tle, takie jak:</p>
                        <ul class="row list-unstyled">
                            <li class="col-md-6">
                                <i class="fab fa-aws text-info me-2"></i>Synchronizacja z S3
                            </li>
                            <li class="col-md-6">
                                <i class="fas fa-image text-primary me-2"></i>Regeneracja miniatur
                            </li>
                            <li class="col-md-6">
                                <i class="fas fa-robot text-success me-2"></i>Analiza AI zdjęć
                            </li>
                            <li class="col-md-6">
                                <i class="fas fa-download text-warning me-2"></i>Import zdjęć
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <h6><i class="fas fa-cogs me-2"></i>Procesor kolejki</h6>
                            <p class="mb-2">Aby automatycznie przetwarzać zadania, skonfiguruj cron:</p>
                            <code class="small">*/5 * * * * php /path/to/yii queue/run</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>