<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'AI Integration Settings';
$this->params['breadcrumbs'][] = $this->title;

// Provider options
$providerOptions = [
    'aws' => 'AWS Rekognition',
    'google' => 'Google Vision',
    'openai' => 'OpenAI',
];
?>
<div class="ai-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">AI Configuration</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'ai-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>

                    <div class="form-group">
                        <label class="control-label col-sm-4">AI Provider:</label>
                        <div class="col-sm-8">
                            <?= Html::dropDownList('provider', $settings['provider'], $providerOptions, [
                                'class' => 'form-control',
                                'id' => 'ai-provider',
                                'prompt' => '- Select Provider -',
                                'required' => true,
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group aws-field google-field openai-field">
                        <label class="control-label col-sm-4">API Key:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="api_key" value="<?= Html::encode($settings['api_key']) ?>" placeholder="<?= empty($settings['api_key']) ? 'Enter your API key' : 'Keep existing API key' ?>">
                            <div class="help-block aws-field" style="display: none;">AWS Access Key</div>
                            <div class="help-block google-field" style="display: none;">Path to Google service account key file</div>
                            <div class="help-block openai-field" style="display: none;">OpenAI API key</div>
                        </div>
                    </div>

                    <div class="form-group aws-field" style="display: none;">
                        <label class="control-label col-sm-4">Region:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="region" value="<?= Html::encode($settings['region']) ?>">
                            <div class="help-block">AWS region (e.g. us-east-1)</div>
                        </div>
                    </div>

                    <div class="form-group openai-field" style="display: none;">
                        <label class="control-label col-sm-4">Model:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="model" value="<?= Html::encode($settings['model']) ?>">
                            <div class="help-block">OpenAI model name (e.g. gpt-4-vision-preview)</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="enabled" value="1" <?= $settings['enabled'] ? 'checked' : '' ?>> Enable AI Integration
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <button type="button" class="btn btn-info" id="test-ai-btn">Test AI Service</button>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">AI Features</h3>
                </div>
                <div class="panel-body">
                    <p>The AI integration can provide the following features:</p>
                    <ul>
                        <li><strong>Automatic Tagging</strong> - Generate relevant tags for photos based on their content</li>
                        <li><strong>Content Description</strong> - Create natural language descriptions of photos</li>
                        <li><strong>Content Filtering</strong> - Detect and flag inappropriate content</li>
                        <li><strong>Object Recognition</strong> - Identify common objects in photos</li>
                        <li><strong>Face Detection</strong> - Detect faces and expressions (if enabled)</li>
                        <li><strong>Text Recognition</strong> - Extract text visible in photos</li>
                    </ul>
                    <p>Available features vary by provider:</p>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th>AWS</th>
                                <th>Google</th>
                                <th>OpenAI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Automatic Tagging</td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                            </tr>
                            <tr>
                                <td>Content Description</td>
                                <td><i class="glyphicon glyphicon-minus text-warning"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                            </tr>
                            <tr>
                                <td>Content Filtering</td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                            </tr>
                            <tr>
                                <td>Object Recognition</td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                            </tr>
                            <tr>
                                <td>Face Detection</td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-minus text-warning"></i></td>
                            </tr>
                            <tr>
                                <td>Text Recognition</td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                                <td><i class="glyphicon glyphicon-ok text-success"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$testAiUrl = Url::to(['test']);
$this->registerJs("
    // Toggle provider-specific fields
    function toggleProviderFields() {
        var provider = $('#ai-provider').val();
        $('.aws-field, .google-field, .openai-field').hide();
        
        if (provider) {
            $('.' + provider + '-field').show();
        }
    }
    
    // Initialize fields visibility
    toggleProviderFields();
    
    // Handle provider change
    $('#ai-provider').on('change', toggleProviderFields);
    
    // Test AI service button handler
    $('#test-ai-btn').on('click', function() {
        $(this).prop('disabled', true).html('<i class=\"glyphicon glyphicon-refresh glyphicon-spin\"></i> Testing...');
        
        $.ajax({
            url: '{$testAiUrl}',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('AI service test successful!');
                } else {
                    alert('AI service test failed: ' + response.message);
                }
            },
            error: function() {
                alert('Error testing AI service. Please check your settings.');
            },
            complete: function() {
                $('#test-ai-btn').prop('disabled', false).text('Test AI Service');
            }
        });
    });
");
?>