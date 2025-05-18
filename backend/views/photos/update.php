<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Tag;
use common\models\Category;

/* @var $this yii\web\View */
/* @var $model common\models\Photo */
/* @var $form yii\widgets\ActiveForm */
/* @var $allTags array */
/* @var $allCategories array */
/* @var $selectedTags array */
/* @var $selectedCategories array */

$this->title = 'Update Photo: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Photos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'In Queue',
    \common\models\Photo::STATUS_ACTIVE => 'Active',
    \common\models\Photo::STATUS_DELETED => 'Deleted',
];

// Register Select2 assets
\backend\assets\Select2Asset::register($this);
?>
<div class="photo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Photo Details</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions) ?>

                    <?= $form->field($model, 'is_public')->checkbox() ?>
                    
                    <div class="form-group field-tags">
                        <label class="control-label" for="tags">Tags</label>
                        <?= Html::dropDownList('tags', $selectedTags, ArrayHelper::map($allTags, 'id', 'name'), [
                            'class' => 'form-control select2-tags',
                            'multiple' => true,
                        ]) ?>
                        <div class="help-block">Select or type to create new tags</div>
                    </div>
                    
                    <div class="form-group field-categories">
                        <label class="control-label" for="categories">Categories</label>
                        <?= Html::dropDownList('categories', $selectedCategories, ArrayHelper::map($allCategories, 'id', 'name'), [
                            'class' => 'form-control select2-categories',
                            'multiple' => true,
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Preview</h3>
                </div>
                <div class="panel-body text-center">
                    <?php
                    // Get thumbnail URL (medium size)
                    $thumbnailSize = \common\models\ThumbnailSize::findOne(['name' => 'medium']);
                    if (!$thumbnailSize) {
                        $thumbnailSize = \common\models\ThumbnailSize::find()->one();
                    }
                    
                    if ($thumbnailSize) {
                        $thumbnailUrl = \yii\helpers\Url::base(true) . '/uploads/thumbnails/' . $thumbnailSize->name . '_' . $model->file_name;
                        echo Html::img($thumbnailUrl, [
                            'class' => 'img-responsive center-block',
                            'alt' => $model->title,
                        ]);
                    } else {
                        echo '<p class="text-muted">Preview not available</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">File Information</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tr>
                            <th>Filename:</th>
                            <td><?= Html::encode($model->file_name) ?></td>
                        </tr>
                        <tr>
                            <th>Size:</th>
                            <td><?= Yii::$app->formatter->asShortSize($model->file_size, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Dimensions:</th>
                            <td><?= $model->width ?> x <?= $model->height ?> px</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><?= $model->mime_type ?></td>
                        </tr>
                        <tr>
                            <th>Uploaded:</th>
                            <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><?= $statusOptions[$model->status] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Initialize Select2 for tags
    $('.select2-tags').select2({
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Select or type to add tags'
    });
    
    // Initialize Select2 for categories
    $('.select2-categories').select2({
        placeholder: 'Select categories'
    });
");
?>