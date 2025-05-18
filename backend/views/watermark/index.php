<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'Watermark Settings';
$this->params['breadcrumbs'][] = $this->title;

// Position options
$positionOptions = [
    'top-left' => 'Top Left',
    'top-right' => 'Top Right',
    'bottom-left' => 'Bottom Left',
    'bottom-right' => 'Bottom Right',
    'center' => 'Center',
];
?>
<div class="watermark-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Watermark Configuration</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'watermark-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Watermark Type:</label>
                        <div class="col-sm-8">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="type" value="text" <?= $settings['type'] === 'text' ? 'checked' : '' ?> id="watermark-type-text">
                                    Text
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="type" value="image" <?= $settings['type'] === 'image' ? 'checked' : '' ?> id="watermark-type-image">
                                    Image
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group watermark-text-fields" <?= $settings['type'] === 'image' ? 'style="display: none;"' : '' ?>>
                        <label class="control-label col-sm-4">Watermark Text:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="text" value="<?= Html::encode($settings['text']) ?>">
                            <div class="help-block">Text to display as watermark</div>
                        </div>
                    </div>

                    <div class="form-group watermark-image-fields" <?= $settings['type'] === 'text' ? 'style="display: none;"' : '' ?>>
                        <label class="control-label col-sm-4">Watermark Image:</label>
                        <div class="col-sm-8">
                            <?php if (!empty($settings['image_url'])): ?>
                                <div class="thumbnail" style="max-width: 200px;">
                                    <img src="<?= $settings['image_url'] ?>" alt="Current watermark" class="img-responsive">
                                    <div class="caption">
                                        <p class="text-center">Current watermark image</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" id="watermark-image-upload" accept="image/*">
                            <div class="help-block">Upload a PNG or GIF image with transparency for best results</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Position:</label>
                        <div class="col-sm-8">
                            <?= Html::dropDownList('position', $settings['position'], $positionOptions, [
                                'class' => 'form-control',
                                'id' => 'watermark-position',
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Opacity:</label>
                        <div class="col-sm-8">
                            <input type="range" name="opacity" min="0" max="1" step="0.1" value="<?= $settings['opacity'] ?>" id="watermark-opacity">
                            <span id="opacity-value"><?= $settings['opacity'] * 100 ?>%</span>
                            <div class="help-block">Transparency level of the watermark</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <button type="button" class="btn btn-info" id="preview-watermark-btn">Preview</button>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Watermark Preview</h3>
                </div>
                <div class="panel-body text-center">
                    <div id="watermark-preview-container">
                        <img src="<?= Url::to('@web/img/watermark-preview-placeholder.jpg') ?>" alt="Preview placeholder" class="img-responsive center-block" id="watermark-preview-placeholder">
                        <div id="watermark-preview-loading" style="display: none;">
                            <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Generating preview...
                        </div>
                        <div id="watermark-preview-result" style="display: none;">
                            <img src="" alt="Watermark preview" class="img-responsive center-block" id="watermark-preview-image">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">About Watermarks</h3>
                </div>
                <div class="panel-body">
                    <p>Watermarks are overlaid on photos to:</p>
                    <ul>
                        <li>Protect your images from unauthorized use</li>
                        <li>Brand your photos with your name or logo</li>
                        <li>Indicate ownership of the content</li>
                    </ul>
                    <p><strong>Recommendations:</strong></p>
                    <ul>
                        <li>For text watermarks, use short and simple text</li>
                        <li>For image watermarks, use PNG or GIF with transparency</li>
                        <li>Set opacity below 50% to avoid obscuring the photo</li>
                        <li>Position in a corner to minimize distraction</li>
                    </ul>
                    <p>The watermark will be applied to thumbnails based on your thumbnail settings configuration.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$previewUrl = Url::to(['preview']);
$this->registerJs("
    // Handle watermark type toggle
    $('input[name=\"type\"]').on('change', function() {
        if ($(this).val() === 'text') {
            $('.watermark-text-fields').show();
            $('.watermark-image-fields').hide();
        } else {
            $('.watermark-text-fields').hide();
            $('.watermark-image-fields').show();
        }
    });
    
    // Update opacity percentage display
    $('#watermark-opacity').on('input', function() {
        $('#opacity-value').text(Math.round($(this).val() * 100) + '%');
    });
    
    // Preview watermark
    $('#preview-watermark-btn').on('click', function() {
        // Show loading
        $('#watermark-preview-placeholder').hide();
        $('#watermark-preview-result').hide();
        $('#watermark-preview-loading').show();
        
        // Create form data
        var formData = new FormData();
        formData.append('type', $('input[name=\"type\"]:checked').val());
        formData.append('text', $('input[name=\"text\"]').val());
        formData.append('position', $('#watermark-position').val());
        formData.append('opacity', $('#watermark-opacity').val());
        
        // Add image if selected
        var imageFile = $('#watermark-image-upload')[0].files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        // Send AJAX request
        $.ajax({
            url: '{$previewUrl}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#watermark-preview-image').attr('src', response.preview);
                    $('#watermark-preview-result').show();
                } else {
                    alert('Error generating preview: ' + response.message);
                    $('#watermark-preview-placeholder').show();
                }
            },
            error: function() {
                alert('Error generating preview. Please try again.');
                $('#watermark-preview-placeholder').show();
            },
            complete: function() {
                $('#watermark-preview-loading').hide();
            }
        });
    });
    
    // Add spinning icon CSS
    $('<style>.glyphicon-spin { animation: spin 1s infinite linear; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
");
?>