<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PhotoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Active Photos';
$this->params['breadcrumbs'][] = $this->title;

// Get all tags and categories for filter dropdowns
$tags = ArrayHelper::map(Tag::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$categories = ArrayHelper::map(Category::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'In Queue',
    \common\models\Photo::STATUS_ACTIVE => 'Active',
    \common\models\Photo::STATUS_DELETED => 'Deleted',
];
?>
<div class="photo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Upload Photo', ['upload'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Batch Update Selected', '#', [
            'class' => 'btn btn-primary batch-update-btn',
            'style' => 'display: none;',
            'data-toggle' => 'modal',
            'data-target' => '#batchUpdateModal',
        ]) ?>
        <?= Html::a('Analyze Selected with AI', '#', [
            'class' => 'btn btn-info batch-analyze-btn',
            'style' => 'display: none;',
            'data-toggle' => 'modal',
            'data-target' => '#batchAnalyzeModal',
        ]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'grid-view photo-grid'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model) {
                    return ['class' => 'photo-checkbox', 'value' => $model->id];
                }
            ],
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'thumbnail',
                'label' => 'Thumbnail',
                'format' => 'raw',
                'value' => function ($model) {
                    // Get thumbnail URL (small size)
                    $thumbnailSize = \common\models\ThumbnailSize::findOne(['name' => 'small']);
                    $thumbnailUrl = \yii\helpers\Url::base(true) . '/uploads/thumbnails/' . ($thumbnailSize ? $thumbnailSize->name : 'small') . '_' . $model->file_name;
                    
                    return Html::img($thumbnailUrl, [
                        'class' => 'img-responsive',
                        'style' => 'max-width: 100px; max-height: 100px;',
                        'alt' => $model->title,
                    ]);
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            'title',
            [
                'attribute' => 'status',
                'value' => function ($model) use ($statusOptions) {
                    return $statusOptions[$model->status] ?? 'Unknown';
                },
                'filter' => $statusOptions,
            ],
            [
                'attribute' => 'is_public',
                'value' => function ($model) {
                    return $model->is_public ? 'Yes' : 'No';
                },
                'filter' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
            ],
            [
                'attribute' => 'tag',
                'value' => function ($model) {
                    $tags = $model->getTags()->all();
                    if (empty($tags)) {
                        return '<span class="not-set">(not set)</span>';
                    }
                    
                    $tagNames = ArrayHelper::getColumn($tags, 'name');
                    return implode(', ', $tagNames);
                },
                'format' => 'raw',
                'filter' => $tags,
            ],
            [
                'attribute' => 'category',
                'value' => function ($model) {
                    $categories = $model->getCategories()->all();
                    if (empty($categories)) {
                        return '<span class="not-set">(not set)</span>';
                    }
                    
                    $categoryNames = ArrayHelper::getColumn($categories, 'name');
                    return implode(', ', $categoryNames);
                },
                'format' => 'raw',
                'filter' => $categories,
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return date('Y-m-d H:i', $model->created_at);
                },
                'filter' => \yii\jui\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'from_date',
                    'language' => 'en',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control', 'placeholder' => 'From Date'],
                ]) . '<br>' .
                \yii\jui\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'to_date',
                    'language' => 'en',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control', 'placeholder' => 'To Date'],
                ]),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                            'title' => Yii::t('app', 'View'),
                            'data-pjax' => 0,
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('app', 'Update'),
                            'data-pjax' => 0,
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this photo?'),
                            'data-method' => 'post',
                            'data-pjax' => 0,
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<!-- Batch Update Modal -->
<div class="modal fade" id="batchUpdateModal" tabindex="-1" role="dialog" aria-labelledby="batchUpdateModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="batchUpdateModalLabel">Batch Update Photos</h4>
            </div>
            <div class="modal-body">
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'id' => 'batch-update-form',
                    'action' => ['batch-update'],
                    'options' => ['class' => 'form-horizontal'],
                ]); ?>
                
                <input type="hidden" name="ids" id="selected-photo-ids">
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Status:</label>
                    <div class="col-sm-8">
                        <?= Html::dropDownList('status', null, $statusOptions, [
                            'prompt' => '- No Change -',
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Public:</label>
                    <div class="col-sm-8">
                        <?= Html::dropDownList('is_public', null, ['0' => 'No', '1' => 'Yes'], [
                            'prompt' => '- No Change -',
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Categories:</label>
                    <div class="col-sm-8">
                        <?= Html::dropDownList('categories[]', null, $categories, [
                            'class' => 'form-control select2',
                            'multiple' => true,
                        ]) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Tags:</label>
                    <div class="col-sm-8">
                        <?= Html::dropDownList('tags[]', null, $tags, [
                            'class' => 'form-control select2',
                            'multiple' => true,
                        ]) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="replace" value="1"> Replace existing categories and tags
                            </label>
                        </div>
                    </div>
                </div>
                
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="batch-update-submit">Update Photos</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Analyze Modal -->
<div class="modal fade" id="batchAnalyzeModal" tabindex="-1" role="dialog" aria-labelledby="batchAnalyzeModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="batchAnalyzeModalLabel">Analyze Photos with AI</h4>
            </div>
            <div class="modal-body">
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'id' => 'batch-analyze-form',
                    'action' => ['/ai/analyze-batch'],
                    'options' => ['class' => 'form-horizontal'],
                ]); ?>
                
                <input type="hidden" name="ids" id="analyze-photo-ids">
                
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="analyze_tags" value="1" checked> Generate tags
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="analyze_description" value="1" checked> Generate descriptions
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    This action will queue the selected photos for AI analysis. The process will run in the background and may take some time to complete.
                </div>
                
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="batch-analyze-submit">Analyze Photos</button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Initialize select2
    $('.select2').select2();
    
    // Handle checkbox selection
    $('.photo-checkbox').on('change', function() {
        var checkedBoxes = $('.photo-checkbox:checked');
        if (checkedBoxes.length > 0) {
            $('.batch-update-btn, .batch-analyze-btn').show();
        } else {
            $('.batch-update-btn, .batch-analyze-btn').hide();
        }
    });
    
    // Handle batch update form submission
    $('#batch-update-submit').on('click', function() {
        var checkedBoxes = $('.photo-checkbox:checked');
        var ids = $.map(checkedBoxes, function(element) {
            return $(element).val();
        });
        $('#selected-photo-ids').val(ids.join(','));
        $('#batch-update-form').submit();
    });
    
    // Handle batch analyze form submission
    $('#batch-analyze-submit').on('click', function() {
        var checkedBoxes = $('.photo-checkbox:checked');
        var ids = $.map(checkedBoxes, function(element) {
            return $(element).val();
        });
        $('#analyze-photo-ids').val(ids.join(','));
        $('#batch-analyze-form').submit();
    });
    
    // Select all/none when clicking on header checkbox
    $('.select-on-check-all').on('change', function() {
        $('.photo-checkbox').trigger('change');
    });
");
?>