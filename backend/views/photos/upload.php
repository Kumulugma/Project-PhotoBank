<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\DropzoneAsset;

/* @var $this yii\web\View */

$this->title = 'Upload Photos';
$this->params['breadcrumbs'][] = ['label' => 'Photos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register Dropzone asset
DropzoneAsset::register($this);
?>
<div class="photo-upload">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Upload Photos</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-info">
                <p>Drag and drop your photos here, or click to browse files. You can upload multiple photos at once.</p>
                <p>Allowed file types: JPG, PNG, GIF. Maximum file size: <?= ini_get('upload_max_filesize') ?></p>
            </div>
            
            <form action="<?= Url::to(['upload-ajax']) ?>" class="dropzone" id="photo-dropzone">
                <div class="fallback">
                    <input name="file" type="file" multiple>
                </div>
            </form>
        </div>
    </div>
    
    <div class="panel panel-default" id="uploaded-photos-panel" style="display: none;">
        <div class="panel-heading">
            <h3 class="panel-title">Uploaded Photos</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-success">
                <p>Your photos have been uploaded successfully. They are now in the photo queue waiting for approval.</p>
                <p>You can approve them from the <a href="<?= Url::to(['queue']) ?>">Photo Queue</a> page.</p>
            </div>
            
            <div class="row" id="uploaded-photos-container">
                <!-- Uploaded photos will be dynamically added here -->
            </div>
        </div>
        <div class="panel-footer">
            <a href="<?= Url::to(['queue']) ?>" class="btn btn-primary">Go to Photo Queue</a>
            <button type="button" class="btn btn-default" id="upload-more-btn">Upload More Photos</button>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Initialize Dropzone
    Dropzone.autoDiscover = false;
    
    var uploadedPhotos = [];
    
    var photoDropzone = new Dropzone('#photo-dropzone', {
        paramName: 'file',
        maxFilesize: 20, // MB
        acceptedFiles: 'image/jpeg,image/png,image/gif',
        chunking: true,
        forceChunking: true,
        chunkSize: 1000000, // 1MB chunks
        parallelChunkUploads: false,
        maxFiles: 100,
        addRemoveLinks: true,
        dictDefaultMessage: '<i class=\"glyphicon glyphicon-upload\"></i><br>Drop files here or click to upload',
        dictResponseError: 'Error uploading file!',
        dictFallbackMessage: 'Your browser does not support drag\'n\'drop file uploads.',
        dictFileTooBig: 'File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.',
        dictInvalidFileType: 'You can\'t upload files of this type.',
        dictRemoveFile: 'Remove',
        dictMaxFilesExceeded: 'You can only upload {{maxFiles}} files at a time.',
        dictCancelUpload: 'Cancel upload',
        
        init: function() {
            this.on('success', function(file, response) {
                if (response.success) {
                    // Add to uploaded photos array
                    uploadedPhotos.push(response.photo);
                    
                    // Store photo ID in the file object
                    file.photoId = response.photo.id;
                } else {
                    // Show error message
                    this.emit('error', file, response.message || 'Upload failed');
                }
            });
            
            this.on('queuecomplete', function() {
                if (uploadedPhotos.length > 0) {
                    // Show uploaded photos panel
                    $('#uploaded-photos-panel').show();
                    
                    // Generate thumbnails
                    var html = '';
                    for (var i = 0; i < uploadedPhotos.length; i++) {
                        var photo = uploadedPhotos[i];
                        var thumbnail = photo.thumbnails.small || photo.thumbnails.medium || Object.values(photo.thumbnails)[0];
                        
                        html += '<div class=\"col-xs-6 col-sm-4 col-md-3 col-lg-2\">' +
                                  '<div class=\"thumbnail\">' +
                                    '<img src=\"' + thumbnail + '\" alt=\"' + photo.title + '\">' +
                                    '<div class=\"caption\">' +
                                      '<h4>' + photo.title + '</h4>' +
                                      '<p>' +
                                        '<a href=\"' + '" . Url::to(['update']) . "?id=' + photo.id + '\" class=\"btn btn-primary btn-xs\">Edit</a> ' +
                                        '<a href=\"' + '" . Url::to(['view']) . "?id=' + photo.id + '\" class=\"btn btn-info btn-xs\">View</a>' +
                                      '</p>' +
                                    '</div>' +
                                  '</div>' +
                                '</div>';
                    }
                    
                    $('#uploaded-photos-container').append(html);
                }
            });
        }
    });
    
    // Handle 'Upload More' button
    $('#upload-more-btn').on('click', function() {
        // Clear dropzone
        photoDropzone.removeAllFiles();
        
        // Hide uploaded photos panel
        $('#uploaded-photos-panel').hide();
        
        // Clear uploaded photos array
        uploadedPhotos = [];
        
        // Clear uploaded photos container
        $('#uploaded-photos-container').html('');
    });
");
?>