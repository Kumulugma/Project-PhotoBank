<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'AWS S3 Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="s3-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">S3 Configuration</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 's3-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Bucket Name:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="bucket" value="<?= Html::encode($settings['bucket']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Region:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="region" value="<?= Html::encode($settings['region']) ?>" required>
                            <div class="help-block">e.g. us-east-1, eu-west-1</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Access Key:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="access_key" value="<?= Html::encode($settings['access_key']) ?>" placeholder="<?= empty($settings['access_key']) ? 'Enter your access key' : 'Keep existing access key' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Secret Key:</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="secret_key" value="<?= !empty($settings['secret_key']) ? '********' : '' ?>" placeholder="<?= empty($settings['secret_key']) ? 'Enter your secret key' : 'Keep existing secret key' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Photos Directory:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="directory" value="<?= Html::encode($settings['directory']) ?>">
                            <div class="help-block">Directory path within the bucket</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-4">Deleted Photos Directory:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="deleted_directory" value="<?= Html::encode($settings['deleted_directory']) ?>">
                            <div class="help-block">Directory for storing deleted photos</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <button type="button" class="btn btn-info" id="test-connection-btn">Test Connection</button>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">S3 Synchronization</h3>
                </div>
                <div class="panel-body">
                    <p>Use this form to synchronize photos with S3 storage. This will:</p>
                    <ul>
                        <li>Upload approved photos without an S3 path to S3 storage</li>
                        <li>Update database records with S3 paths</li>
                        <li>Optionally delete local copies after successful upload</li>
                    </ul>

                    <?php $form = ActiveForm::begin([
                        'id' => 's3-sync-form',
                        'action' => ['sync'],
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="delete_local" value="1"> Delete local copies after upload
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-success">Start Synchronization</button>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">S3 Information</h3>
                </div>
                <div class="panel-body">
                    <p>Amazon S3 (Simple Storage Service) is an object storage service that offers industry-leading scalability, data availability, security, and performance.</p>
                    <p>Benefits of using S3 for photo storage:</p>
                    <ul>
                        <li>Durability and reliability</li>
                        <li>Scalable storage capacity</li>
                        <li>Cost-effective for large collections</li>
                        <li>Reduced load on your server</li>
                        <li>Backups and version control</li>
                    </ul>
                    <p>To use S3 integration, you need to create an AWS account and configure an S3 bucket with appropriate permissions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$testConnectionUrl = Url::to(['test']);
$this->registerJs("
    // Test connection button handler
    $('#test-connection-btn').on('click', function() {
        $(this).prop('disabled', true).html('<i class=\"glyphicon glyphicon-refresh glyphicon-spin\"></i> Testing...');
        
        $.ajax({
            url: '{$testConnectionUrl}',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Connection test successful!');
                } else {
                    alert('Connection test failed: ' + response.message);
                }
            },
            error: function() {
                alert('Error testing connection. Please check your settings.');
            },
            complete: function() {
                $('#test-connection-btn').prop('disabled', false).text('Test Connection');
            }
        });
    });
    
    // Add spinning icon CSS
    $('<style>.glyphicon-spin { animation: spin 1s infinite linear; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
");
?>