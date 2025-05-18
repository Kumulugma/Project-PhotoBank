<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\QueuedJobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $stats array */

$this->title = 'Job Queue';
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\QueuedJob::STATUS_PENDING => 'Pending',
    \common\models\QueuedJob::STATUS_PROCESSING => 'Processing',
    \common\models\QueuedJob::STATUS_COMPLETED => 'Completed',
    \common\models\QueuedJob::STATUS_FAILED => 'Failed',
];

// Job type options
$jobTypeOptions = [
    's3_sync' => 'S3 Synchronization',
    'regenerate_thumbnails' => 'Regenerate Thumbnails',
    'analyze_photo' => 'Analyze Photo',
    'analyze_batch' => 'Analyze Photo Batch',
    'import_photos' => 'Import Photos',
];
?>
<div class="queued-job-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-8">
            <p>
                <?= Html::a('Create Job', ['create'], ['class' => 'btn btn-success']) ?>
                <?= Html::a('Run Queue Processor', ['run', 'limit' => 5], [
                    'class' => 'btn btn-primary',
                    'data' => [
                        'confirm' => 'This will process up to 5 pending jobs. Continue?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('Clear Completed Jobs', ['clear-completed'], [
                    'class' => 'btn btn-default',
                    'data' => [
                        'confirm' => 'Are you sure you want to clear all completed jobs?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('Clear Failed Jobs', ['clear-failed'], [
                    'class' => 'btn btn-default',
                    'data' => [
                        'confirm' => 'Are you sure you want to clear all failed jobs?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>

            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'id',
                    [
                        'attribute' => 'type',
                        'value' => function ($model) use ($jobTypeOptions) {
                            return $jobTypeOptions[$model->type] ?? $model->type;
                        },
                        'filter' => $jobTypeOptions,
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) use ($statusOptions) {
                            return $statusOptions[$model->status] ?? 'Unknown';
                        },
                        'filter' => $statusOptions,
                        'contentOptions' => function ($model) {
                            switch ($model->status) {
                                case \common\models\QueuedJob::STATUS_PENDING:
                                    return ['class' => 'text-muted'];
                                case \common\models\QueuedJob::STATUS_PROCESSING:
                                    return ['class' => 'text-primary'];
                                case \common\models\QueuedJob::STATUS_COMPLETED:
                                    return ['class' => 'text-success'];
                                case \common\models\QueuedJob::STATUS_FAILED:
                                    return ['class' => 'text-danger'];
                                default:
                                    return [];
                            }
                        },
                    ],
                    [
                        'attribute' => 'attempts',
                        'headerOptions' => ['style' => 'width: 80px;'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function ($model) {
                            return date('Y-m-d H:i:s', $model->created_at);
                        },
                        'filter' => false,
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => function ($model) {
                            return date('Y-m-d H:i:s', $model->updated_at);
                        },
                        'filter' => false,
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {retry} {process} {delete}',
                        'buttons' => [
                            'retry' => function ($url, $model, $key) {
                                if ($model->status === \common\models\QueuedJob::STATUS_FAILED) {
                                    return Html::a('<span class="glyphicon glyphicon-repeat"></span>', $url, [
                                        'title' => Yii::t('app', 'Retry'),
                                        'data-method' => 'post',
                                        'data-pjax' => 0,
                                    ]);
                                }
                                return '';
                            },
                            'process' => function ($url, $model, $key) {
                                if ($model->status === \common\models\QueuedJob::STATUS_PENDING) {
                                    return Html::a('<span class="glyphicon glyphicon-play"></span>', $url, [
                                        'title' => Yii::t('app', 'Process Now'),
                                        'data-method' => 'post',
                                        'data-pjax' => 0,
                                    ]);
                                }
                                return '';
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Queue Statistics</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="panel panel-info">
                                <div class="panel-heading text-center">
                                    <h4><?= $stats['pending'] ?></h4>
                                </div>
                                <div class="panel-body text-center">
                                    <strong>Pending</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading text-center">
                                    <h4><?= $stats['processing'] ?></h4>
                                </div>
                                <div class="panel-body text-center">
                                    <strong>Processing</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="panel panel-success">
                                <div class="panel-heading text-center">
                                    <h4><?= $stats['completed'] ?></h4>
                                </div>
                                <div class="panel-body text-center">
                                    <strong>Completed</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="panel panel-danger">
                                <div class="panel-heading text-center">
                                    <h4><?= $stats['failed'] ?></h4>
                                </div>
                                <div class="panel-body text-center">
                                    <strong>Failed</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">About the Job Queue</h3>
                </div>
                <div class="panel-body">
                    <p>The job queue handles background processing tasks such as:</p>
                    <ul>
                        <li>Synchronizing photos with S3 storage</li>
                        <li>Regenerating thumbnails</li>
                        <li>Analyzing photos with AI services</li>
                        <li>Importing photos from directories</li>
                    </ul>
                    <p><strong>Status Definitions:</strong></p>
                    <ul>
                        <li><strong>Pending:</strong> Job is waiting to be processed</li>
                        <li><strong>Processing:</strong> Job is currently being executed</li>
                        <li><strong>Completed:</strong> Job has finished successfully</li>
                        <li><strong>Failed:</strong> Job encountered an error during processing</li>
                    </ul>
                    <p>Failed jobs can be retried by clicking the retry button.</p>
                </div>
            </div>
        </div>
    </div>
</div>