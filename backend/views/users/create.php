<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Role options
$roleOptions = [
    'user' => 'User',
    'admin' => 'Administrator',
];

// Status options
$statusOptions = [
    \common\models\User::STATUS_ACTIVE => 'Active',
    \common\models\User::STATUS_INACTIVE => 'Inactive',
];
?>
<div class="user-create">

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

                    <?= $form->field($model, 'password')->passwordInput() ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions) ?>
                    
                    <div class="form-group field-role">
                        <label class="control-label" for="role">Role</label>
                        <?= Html::dropDownList('role', 'user', $roleOptions, [
                            'class' => 'form-control',
                            'id' => 'role',
                        ]) ?>
                        <div class="help-block"></div>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">User Roles</h3>
                </div>
                <div class="panel-body">
                    <p>Choose the appropriate role for this user:</p>
                    <ul>
                        <li><strong>User:</strong> Can view public photos and their own uploaded content.</li>
                        <li><strong>Administrator:</strong> Has full access to all system features and content.</li>
                    </ul>
                    <div class="alert alert-info">
                        <strong>Note:</strong> New users will receive an email with their login credentials.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>