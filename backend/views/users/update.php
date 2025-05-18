<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $currentRole string */

$this->title = 'Update User: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

// Role options
$roleOptions = [
    'user' => 'User',
    'admin' => 'Administrator',
];

// Status options
$statusOptions = [
    \common\models\User::STATUS_ACTIVE => 'Active',
    \common\models\User::STATUS_INACTIVE => 'Inactive',
    \common\models\User::STATUS_DELETED => 'Deleted',
];
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">User Information</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Enter new password or leave empty to keep current']) ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions) ?>
                    
                    <div class="form-group field-role">
                        <label class="control-label" for="role">Role</label>
                        <?= Html::dropDownList('role', $currentRole, $roleOptions, [
                            'class' => 'form-control',
                            'id' => 'role',
                        ]) ?>
                        <div class="help-block"></div>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Important Notes</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Password:</strong> Leave the password field empty if you don't want to change it.</p>
                    <p><strong>Status:</strong></p>
                    <ul>
                        <li><strong>Active:</strong> User can log in and access the system</li>
                        <li><strong>Inactive:</strong> User cannot log in until activated</li>
                        <li><strong>Deleted:</strong> Soft-deleted user (data preserved but cannot log in)</li>
                    </ul>
                    <p><strong>Admin Status:</strong> Be cautious when changing roles. There must always be at least one administrator in the system.</p>
                    
                    <?php
                    // Check if this is the only admin
                    $auth = Yii::$app->authManager;
                    $adminUsers = $auth->getUserIdsByRole('admin');
                    $isOnlyAdmin = count($adminUsers) <= 1 && in_array($model->id, $adminUsers);
                    
                    if ($isOnlyAdmin): ?>
                        <div class="alert alert-danger">
                            <strong>Warning:</strong> This is the only administrator in the system. You cannot remove its admin role.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ($isOnlyAdmin) {
    $this->registerJs("
        // Disable role dropdown if this is the only admin
        $('#role').prop('disabled', true);
    ");
}
?>