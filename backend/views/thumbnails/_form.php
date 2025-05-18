<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="thumbnail-size-form">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Thumbnail Settings</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'width')->textInput(['type' => 'number', 'min' => 1]) ?>

                    <?= $form->field($model, 'height')->textInput(['type' => 'number', 'min' => 1]) ?>

                    <?= $form->field($model, 'crop')->checkbox(['uncheck' => 0, 'value' => 1]) ?>

                    <?= $form->field($model, 'watermark')->checkbox(['uncheck' => 0, 'value' => 1]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Thumbnail Size Information</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Name:</strong> A unique identifier for this thumbnail size (e.g., small, medium, large)</p>
                    <p><strong>Width:</strong> The width of the thumbnail in pixels</p>
                    <p><strong>Height:</strong> The height of the thumbnail in pixels</p>
                    <p><strong>Crop:</strong> If enabled, the thumbnail will be cropped to exactly match the specified dimensions. If disabled, the thumbnail will be resized to fit within the dimensions while maintaining the aspect ratio.</p>
                    <p><strong>Watermark:</strong> If enabled, the configured watermark will be applied to thumbnails of this size.</p>
                    
                    <div class="alert alert-warning">
                        <p><strong>Note:</strong> After creating a new thumbnail size, you need to regenerate thumbnails for existing photos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>