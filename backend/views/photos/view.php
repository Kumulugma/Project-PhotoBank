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
\yii\web\YiiAsset::register($this);

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'In Queue',
    \common\models\Photo::STATUS_ACTIVE => 'Active',
    \common\models\Photo::STATUS_DELETED => 'Deleted',
];
?>
<div class="photo-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= $model->status === \common\models\Photo::STATUS_QUEUE ? 
            Html::a('Approve', ['approve', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'Are you sure you want to approve this photo? The original will be moved to S3 storage.',
                    'method' => 'post',
                ],
            ]) : '' ?>
        <?= Html::a('AI Analysis', ['/ai/analyze-photo', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this photo?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Photo Details</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'title',
                            'description:ntext',
                            [
                                'attribute' => 'file_name',
                                'format' => 'text',
                            ],
                            'width',
                            'height',
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
                            ],
                            [
                                'attribute' => 'is_public',
                                'value' => $model->is_public ? 'Yes' : 'No',
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
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Preview</h3>
                </div>
                <div class="panel-body text-center">
                    <?php if (isset($thumbnails['medium'])): ?>
                        <img src="<?= $thumbnails['medium'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-responsive center-block">
                    <?php elseif (isset($thumbnails['small'])): ?>
                        <img src="<?= $thumbnails['small'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-responsive center-block">
                    <?php else: ?>
                        <p class="text-muted">Preview not available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Categories</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted">No categories assigned</p>
                    <?php else: ?>
                        <ul class="list-unstyled">
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <span class="label label-primary"><?= Html::encode($category->name) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Tags</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($tags)): ?>
                        <p class="text-muted">No tags assigned</p>
                    <?php else: ?>
                        <div class="tag-list">
                            <?php foreach ($tags as $tag): ?>
                                <span class="label label-info"><?= Html::encode($tag->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Available Thumbnails</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($thumbnails)): ?>
                        <p class="text-muted">No thumbnails available</p>
                    <?php else: ?>
                        <ul class="list-unstyled">
                            <?php foreach ($thumbnails as $size => $url): ?>
                                <li>
                                    <a href="<?= $url ?>" target="_blank"><?= ucfirst($size) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>