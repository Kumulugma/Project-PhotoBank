<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\QueuedJob;

\backend\assets\AppAsset::registerControllerCss($this, 'queue');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
\backend\assets\AppAsset::registerComponentCss($this, 'modals');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Kolejka zadań';
$this->params['breadcrumbs'][] = $this->title;

// Status options for filters and display
$statusOptions = [
    QueuedJob::STATUS_PENDING => 'Oczekuje',
    QueuedJob::STATUS_PROCESSING => 'Przetwarzanie',
    QueuedJob::STATUS_COMPLETED => 'Zakończone',
    QueuedJob::STATUS_FAILED => 'Błąd',
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
        
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group me-2" role="group">
                <?= Html::a('<i class="fas fa-plus me-2"></i>Nowe zadanie', ['create'], [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="fas fa-play me-2"></i>Uruchom procesor', ['run-processor'], [
                    'class' => 'btn btn-primary',
                    'data-confirm' => 'Czy na pewno uruchomić procesor kolejki?',
                    'data-method' => 'post',
                ]) ?>
            </div>
            
            <div class="btn-group" role="group">
                <?= Html::a('<i class="fas fa-check me-2"></i>Wyczyść zakończone', ['clear-completed'], [
                    'class' => 'btn btn-outline-success',
                    'data-confirm' => 'Czy na pewno usunąć wszystkie zakończone zadania?',
                    'data-method' => 'post',
                ]) ?>
                <?= Html::a('<i class="fas fa-times me-2"></i>Wyczyść błędne', ['clear-failed'], [
                    'class' => 'btn btn-outline-danger',
                    'data-confirm' => 'Czy na pewno usunąć wszystkie błędne zadania?',
                    'data-method' => 'post',
                ]) ?>
                <?= Html::a('<i class="fas fa-stop me-2"></i>Wyczyść przetwarzane', ['clear-processing'], [
                    'class' => 'btn btn-outline-warning',
                    'data-confirm' => 'Czy na pewno usunąć wszystkie zadania w trakcie przetwarzania? To może spowodować niespójność danych.',
                    'data-method' => 'post',
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Queue Statistics Cards -->
    <div class="row queue-stats-cards mb-4">
        <div class="col-md-3">
            <div class="stat-card pending">
                <h3 class="text-warning"><?= QueuedJob::find()->where(['status' => QueuedJob::STATUS_PENDING])->count() ?></h3>
                <p class="mb-0">Oczekujące</p>
                <small class="text-muted">Gotowe do przetworzenia</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card processing">
                <h3 class="text-primary"><?= QueuedJob::find()->where(['status' => QueuedJob::STATUS_PROCESSING])->count() ?></h3>
                <p class="mb-0">Przetwarzane</p>
                <small class="text-muted">Aktualnie w trakcie</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card completed">
                <h3 class="text-success"><?= QueuedJob::find()->where(['status' => QueuedJob::STATUS_COMPLETED])->count() ?></h3>
                <p class="mb-0">Zakończone</p>
                <small class="text-muted">Pomyślnie wykonane</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card failed">
                <h3 class="text-danger"><?= QueuedJob::find()->where(['status' => QueuedJob::STATUS_FAILED])->count() ?></h3>
                <p class="mb-0">Błędne</p>
                <small class="text-muted">Wymagają uwagi</small>
            </div>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'text-center fw-bold'],
            ],
            [
                'attribute' => 'type',
                'label' => 'Typ zadania',
                'format' => 'raw',
                'value' => function ($model) use ($jobTypeOptions) {
                    $icons = [
                        's3_sync' => 'fas fa-cloud',
                        'regenerate_thumbnails' => 'fas fa-images',
                        'analyze_photo' => 'fas fa-search',
                        'analyze_batch' => 'fas fa-layer-group',
                        'import_photos' => 'fas fa-file-import',
                    ];
                    
                    $icon = $icons[$model->type] ?? 'fas fa-cog';
                    $typeName = $jobTypeOptions[$model->type] ?? $model->type;
                    
                    return '<span class="job-type-icon me-2"><i class="' . $icon . '"></i></span>' . Html::encode($typeName);
                },
                'headerOptions' => ['style' => 'width: 200px;'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $badges = [
                        QueuedJob::STATUS_PENDING => '<span class="badge bg-warning text-dark job-status-badge"><i class="fas fa-clock me-1"></i>' . Yii::t('app', 'Pending') . '</span>',
                        QueuedJob::STATUS_PROCESSING => '<span class="badge bg-primary job-status-badge"><i class="fas fa-spinner spinning-icon me-1"></i>' . Yii::t('app', 'Processing') . '</span>',
                        QueuedJob::STATUS_COMPLETED => '<span class="badge bg-success job-status-badge"><i class="fas fa-check me-1"></i>' . Yii::t('app', 'Completed') . '</span>',
                        QueuedJob::STATUS_FAILED => '<span class="badge bg-danger job-status-badge"><i class="fas fa-times me-1"></i>' . Yii::t('app', 'Failed') . '</span>',
                    ];
                    
                    return $badges[$model->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                },
                'headerOptions' => ['style' => 'width: 130px;'],
            ],
            [
                'attribute' => 'attempts',
                'label' => 'Próby',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'text-center'],
                'value' => function ($model) {
                    if ($model->attempts > 3) {
                        return Html::tag('span', $model->attempts, ['class' => 'text-danger fw-bold']);
                    } elseif ($model->attempts > 1) {
                        return Html::tag('span', $model->attempts, ['class' => 'text-warning fw-bold']);
                    }
                    return $model->attempts;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Utworzono',
                'format' => 'raw',
                'value' => function ($model) {
                    $date = date('Y-m-d H:i:s', $model->created_at);
                    $relative = Yii::$app->formatter->asRelativeTime($model->created_at);
                    return '<span title="' . $date . '" class="text-muted">' . $relative . '</span>';
                },
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Zaktualizowano',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!$model->updated_at) {
                        return '<span class="text-muted">-</span>';
                    }
                    $date = date('Y-m-d H:i:s', $model->updated_at);
                    $relative = Yii::$app->formatter->asRelativeTime($model->updated_at);
                    return '<span title="' . $date . '" class="text-muted">' . $relative . '</span>';
                },
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'attribute' => 'error_message',
                'label' => 'Błąd',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->error_message)) {
                        return '<span class="text-muted">-</span>';
                    }
                    
                    $shortMessage = strlen($model->error_message) > 50 
                        ? substr($model->error_message, 0, 50) . '...' 
                        : $model->error_message;
                    
                    return '<span class="text-danger small" title="' . Html::encode($model->error_message) . '">' . 
                           Html::encode($shortMessage) . '</span>';
                },
                'headerOptions' => ['style' => 'width: 200px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Akcje',
                'template' => '{view} {retry} {process} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary me-1',
                            'title' => 'Zobacz szczegóły',
                        ]);
                    },
                    'retry' => function ($url, $model, $key) {
                        if ($model->status == QueuedJob::STATUS_FAILED) {
                            return Html::a('<i class="fas fa-redo"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-warning me-1',
                                'title' => 'Ponów zadanie',
                                'data-confirm' => 'Czy na pewno ponowić to zadanie?',
                                'data-method' => 'post',
                            ]);
                        }
                        return '';
                    },
                    'process' => function ($url, $model, $key) {
                        if (in_array($model->status, [QueuedJob::STATUS_PENDING, QueuedJob::STATUS_FAILED])) {
                            return Html::a('<i class="fas fa-play"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-success me-1',
                                'title' => 'Przetwórz teraz',
                                'data-confirm' => 'Czy na pewno przetworzyć to zadanie teraz?',
                                'data-method' => 'post',
                            ]);
                        }
                        return '';
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Usuń zadanie',
                            'data-confirm' => 'Czy na pewno usunąć to zadanie?',
                            'data-method' => 'post',
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width: 160px;'],
                'contentOptions' => ['class' => 'text-nowrap'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <!-- Queue Processor Info -->
    <div class="queue-processor-info mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="fas fa-info-circle me-2"></i>Informacje o procesorze kolejki</h5>
                <p class="mb-0">
                    Procesor kolejki przetwarza zadania automatycznie. 
                    Możesz też uruchomić go ręcznie przyciskiem powyżej.
                </p>
            </div>
            <div class="text-end">
                <code>php yii queue/run</code>
                <br>
                <small class="text-light">Komenda konsoli</small>
            </div>
        </div>
    </div>

</div>