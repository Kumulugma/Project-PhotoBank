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
$this->params['breadcrumbs'][] = ['label' => 'Photos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'In Queue',
    \common\models\Photo::STATUS_ACTIVE => 'Active',
    \common\models\Photo::STATUS_DELETED => 'Deleted',
];
?>
<div class="photo-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="d-flex gap-2 mb-4">
        <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        
        <?php if ($model->status === \common\models\Photo::STATUS_QUEUE): ?>
            <?= Html::a('<i class="fas fa-check"></i> Approve', ['approve', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'Are you sure you want to approve this photo? The original will be moved to S3 storage.',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        
        <?= Html::a('<i class="fas fa-robot"></i> AI Analysis', ['/ai/analyze-photo', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
        
        <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this photo?',
                'method' => 'post',
            ],
        ]) ?>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Photo Details</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            'id',
                            'title',
                            'description:ntext',
                            [
                                'attribute' => 'file_name',
                                'format' => 'text',
                            ],
                            [
                                'label' => 'Dimensions',
                                'value' => $model->width . ' x ' . $model->height . ' px',
                            ],
                            [
                                'attribute' => 'file_size',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asShortSize($model->file_size, 2);
                                },
                            ],
                            'mime_type',
                            [
                                'attribute' => 'status',
                                'value' => $statusOptions[$model->status] ?? 'Unknown',
                                'format' => 'raw',
                                'value' => function($model) use ($statusOptions) {
                                    $status = $statusOptions[$model->status] ?? 'Unknown';
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
                                'value' => $model->is_public ? 'Yes' : 'No',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $class = $model->is_public ? 'bg-success' : 'bg-secondary';
                                    $text = $model->is_public ? 'Yes' : 'No';
                                    return '<span class="badge ' . $class . '">' . $text . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                            [
                                'attribute' => 'created_by',
                                'value' => function ($model) {
                                    $user = \common\models\User::findOne($model->created_by);
                                    return $user ? $user->username : 'Unknown';
                                },
                            ],
                            [
                                'attribute' => 's3_path',
                                'format' => 'ntext',
                                'visible' => !empty($model->s3_path),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preview</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (isset($thumbnails['medium'])): ?>
                        <img src="<?= $thumbnails['medium'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid rounded">
                    <?php elseif (isset($thumbnails['small'])): ?>
                        <img src="<?= $thumbnails['small'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid rounded">
                    <?php else: ?>
                        <div class="text-muted">
                            <i class="fas fa-image fa-4x mb-3"></i>
                            <p>Preview not available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Categories</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted">No categories assigned</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($categories as $category): ?>
                                <span class="badge bg-primary"><?= Html::encode($category->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tags</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tags)): ?>
                        <p class="text-muted">No tags assigned</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-info"><?= Html::encode($tag->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Available Thumbnails</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($thumbnails)): ?>
                        <p class="text-muted">No thumbnails available</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($thumbnails as $size => $url): ?>
                                <a href="<?= $url ?>" target="_blank" class="list-group-item list-group-item-action">
                                    <i class="fas fa-external-link-alt me-2"></i><?= ucfirst($size) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
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
    background-color: var(--bs-light);
}

.badge {
    font-size: 0.875em;
}

.card {
    border: 1px solid var(--bs-border-color);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.list-group-item-action:hover {
    background-color: var(--bs-light);
}
</style>