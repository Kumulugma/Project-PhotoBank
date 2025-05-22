<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ThumbnailSizeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Thumbnail Sizes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="thumbnail-size-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-8">
            <p>
                <?= Html::a('Create Thumbnail Size', ['create'], ['class' => 'btn btn-success']) ?>
                <?= Html::a('Regenerate All Thumbnails', '#', [
                    'class' => 'btn btn-warning',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#regenerateModal',
                ]) ?>
            </p>

            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'id',
                    'name',
                    'width',
                    'height',
                    [
                        'attribute' => 'crop',
                        'value' => function ($model) {
                            return $model->crop ? 'Yes' : 'No';
                        },
                        'filter' => [
                            0 => 'No',
                            1 => 'Yes',
                        ],
                    ],
                    [
                        'attribute' => 'watermark',
                        'value' => function ($model) {
                            return $model->watermark ? 'Yes' : 'No';
                        },
                        'filter' => [
                            0 => 'No',
                            1 => 'Yes',
                        ],
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function ($model) {
                            return date('Y-m-d H:i', $model->created_at);
                        },
                        'filter' => false,
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<span class="fas fa-trash"></span>', $url, [
                                    'title' => Yii::t('app', 'Delete'),
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to delete this thumbnail size? All thumbnails of this size will be lost.'),
                                    'data-method' => 'post',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">About Thumbnails</h5>
                </div>
                <div class="card-body">
                    <p>Thumbnails are smaller versions of original photos used for:</p>
                    <ul>
                        <li>Gallery previews</li>
                        <li>Search results</li>
                        <li>Social media sharing</li>
                        <li>Reducing load times</li>
                    </ul>
                    <p><strong>Crop Mode:</strong></p>
                    <ul>
                        <li><strong>Enabled:</strong> Images will be cropped to exactly fit the specified dimensions, which may cut off parts of the image</li>
                        <li><strong>Disabled:</strong> Images will be resized to fit within the specified dimensions while maintaining the original aspect ratio</li>
                    </ul>
                    <p><strong>Watermark:</strong></p>
                    <ul>
                        <li>When enabled, the configured watermark will be applied to thumbnails of this size</li>
                        <li>Configure watermark settings in the <a href="<?= Url::to(['/watermark/index']) ?>">Watermark Settings</a> section</li>
                    </ul>
                    <p>It's recommended to create thumbnails in various sizes for different purposes:</p>
                    <ul>
                        <li><strong>Small:</strong> 150x150px for grids and lists</li>
                        <li><strong>Medium:</strong> 300x300px for preview pages</li>
                        <li><strong>Large:</strong> 600x600px for detailed views</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Thumbnail Storage</h5>
                </div>
                <div class="card-body">
                    <p>Thumbnails are stored locally on the server for faster access, even when original photos are stored in S3.</p>
                    <p>When you regenerate thumbnails:</p>
                    <ul>
                        <li>All existing thumbnails of the selected size will be deleted</li>
                        <li>New thumbnails will be generated from original photos</li>
                        <li>If the original is only available on S3, it will be temporarily downloaded</li>
                    </ul>
                    <p><strong>Storage path:</strong> <code>/uploads/thumbnails/</code></p>
                    <p><strong>Naming convention:</strong> <code>[size_name]_[filename]</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Regenerate Thumbnails Modal -->
<div class="modal fade" id="regenerateModal" tabindex="-1" role="dialog" aria-labelledby="regenerateModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="regenerateModalLabel">Regenerate Thumbnails</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'id' => 'regenerate-form',
                'action' => ['regenerate'],
                'options' => ['class' => 'form-horizontal'],
            ]); ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Photo ID (optional):</label>
                    <input type="number" class="form-control" name="photo_id" placeholder="Leave empty to regenerate all">
                    <div class="form-text">Enter a specific photo ID or leave empty to regenerate all photos</div>
                </div>
                
                <div class="alert alert-warning">
                    <p><strong>Warning:</strong> This operation can be resource-intensive and may take a long time for large galleries.</p>
                    <p>The process will run in the background. You can check the status in the job queue.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Regenerate Thumbnails</button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Modal już nie potrzebuje dodatkowego JavaScript - Bootstrap 5 obsługuje to automatycznie
");
?>