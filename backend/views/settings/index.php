<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'System Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">System Configuration</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'settings-form',
                'action' => ['update'],
                'options' => ['class' => 'form-horizontal'],
            ]); ?>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>General Settings</h4>
                    
                    <?php if (isset($settings['general'])): ?>
                        <?php foreach ($settings['general'] as $key => $value): ?>
                            <div class="form-group">
                                <label class="control-label col-sm-4"><?= ucwords(str_replace('_', ' ', $key)) ?>:</label>
                                <div class="col-sm-8">
                                    <?php if (is_bool($value) || $value === '0' || $value === '1'): ?>
                                        <select name="Settings[<?= $key ?>]" class="form-control">
                                            <option value="1" <?= $value == 1 ? 'selected' : '' ?>>Enabled</option>
                                            <option value="0" <?= $value == 0 ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" name="Settings[<?= $key ?>]" value="<?= Html::encode($value) ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No general settings configured</p>
                    <?php endif; ?>
                    
                    <h4>Email Settings</h4>
                    
                    <?php if (isset($settings['email'])): ?>
                        <?php foreach ($settings['email'] as $key => $value): ?>
                            <div class="form-group">
                                <label class="control-label col-sm-4"><?= ucwords(str_replace('_', ' ', $key)) ?>:</label>
                                <div class="col-sm-8">
                                    <?php if ($key === 'password' || $key === 'smtp_password'): ?>
                                        <input type="password" class="form-control" name="Settings[email.<?= $key ?>]" value="<?= !empty($value) ? '********' : '' ?>" placeholder="<?= empty($value) ? 'Enter password' : 'Keep existing password' ?>">
                                    <?php elseif (is_bool($value) || $value === '0' || $value === '1'): ?>
                                        <select name="Settings[email.<?= $key ?>]" class="form-control">
                                            <option value="1" <?= $value == 1 ? 'selected' : '' ?>>Enabled</option>
                                            <option value="0" <?= $value == 0 ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" name="Settings[email.<?= $key ?>]" value="<?= Html::encode($value) ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No email settings configured</p>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <h4>Upload Settings</h4>
                    
                    <?php if (isset($settings['upload'])): ?>
                        <?php foreach ($settings['upload'] as $key => $value): ?>
                            <div class="form-group">
                                <label class="control-label col-sm-4"><?= ucwords(str_replace('_', ' ', $key)) ?>:</label>
                                <div class="col-sm-8">
                                    <?php if (is_bool($value) || $value === '0' || $value === '1'): ?>
                                        <select name="Settings[upload.<?= $key ?>]" class="form-control">
                                            <option value="1" <?= $value == 1 ? 'selected' : '' ?>>Enabled</option>
                                            <option value="0" <?= $value == 0 ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" name="Settings[upload.<?= $key ?>]" value="<?= Html::encode($value) ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No upload settings configured</p>
                    <?php endif; ?>
                    
                    <h4>Gallery Settings</h4>
                    
                    <?php if (isset($settings['gallery'])): ?>
                        <?php foreach ($settings['gallery'] as $key => $value): ?>
                            <div class="form-group">
                                <label class="control-label col-sm-4"><?= ucwords(str_replace('_', ' ', $key)) ?>:</label>
                                <div class="col-sm-8">
                                    <?php if (is_bool($value) || $value === '0' || $value === '1'): ?>
                                        <select name="Settings[gallery.<?= $key ?>]" class="form-control">
                                            <option value="1" <?= $value == 1 ? 'selected' : '' ?>>Enabled</option>
                                            <option value="0" <?= $value == 0 ? 'selected' : '' ?>>Disabled</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" name="Settings[gallery.<?= $key ?>]" value="<?= Html::encode($value) ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No gallery settings configured</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">About System Settings</h3>
        </div>
        <div class="panel-body">
            <p>This page allows you to manage global system settings. Settings are organized by category for easier management.</p>
            <p><strong>Note:</strong> Some settings may require a system restart to take effect.</p>
            <p>Specialized settings for specific components (S3, AI, Watermark, etc.) are available in their respective sections.</p>
            
            <h4>Settings Categories:</h4>
            <ul>
                <li><strong>General Settings:</strong> Basic system configuration options</li>
                <li><strong>Email Settings:</strong> SMTP configuration for sending emails</li>
                <li><strong>Upload Settings:</strong> File upload limitations and constraints</li>
                <li><strong>Gallery Settings:</strong> Frontend gallery display preferences</li>
            </ul>
            
            <p>For more advanced configuration, you can modify the application config files directly.</p>
        </div>
    </div>
</div>