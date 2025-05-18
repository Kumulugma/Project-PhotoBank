<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QueuedJob */

$this->title = 'Job #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Job Queue', 'url' => ['index']];
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
                                'attribute' => 'completed_at',
                                'value' => $model->completed_at ? date('Y-m-d H:i:s', $model->completed_at) : 'Not completed',
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
                    $params = json_decode($model->params, true);
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
</div>