<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $roles array */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\User::STATUS_DELETED => 'Deleted',
    \common\models\User::STATUS_INACTIVE => 'Inactive',
    \common\models\User::STATUS_ACTIVE => 'Active',
];

// Role labels
$roleLabels = [
    'admin' => 'Administrator',
    'user' => 'User',
];
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this user?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">User Details</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'username',
                            'email:email',
                            [
                                'attribute' => 'status',
                                'value' => $statusOptions[$model->status] ?? 'Unknown',
                            ],
                            [
                                'label' => 'Roles',
                                'value' => function ($model) use ($roles, $roleLabels) {
                                    $labels = [];
                                    foreach ($roles as $role) {
                                        $labels[] = $roleLabels[$role] ?? $role;
                                    }
                                    return implode(', ', $labels);
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php
            // Get user activity statistics
            $uploadedPhotosCount = \common\models\Photo::find()
                ->where(['created_by' => $model->id])
                ->count();
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">User Activity</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th>Uploaded Photos:</th>
                                <td>
                                    <?= $uploadedPhotosCount ?>
                                    <?php if ($uploadedPhotosCount > 0): ?>
                                        <?= Html::a('View', ['/photos/index', 'PhotoSearch[created_by]' => $model->id], ['class' => 'btn btn-xs btn-info']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Last Login:</th>
                                <td>
                                    <?= $model->last_login_at ? date('Y-m-d H:i:s', $model->last_login_at) : 'Never' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Account Age:</th>
                                <td>
                                    <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if (in_array('admin', $roles)): ?>
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Administrator Privileges</h3>
                </div>
                <div class="panel-body">
                    <p>This user has administrator privileges, which include:</p>
                    <ul>
                        <li>Full access to all system settings</li>
                        <li>User management capabilities</li>
                        <li>Access to all photos, including private ones</li>
                        <li>Ability to approve, modify, and delete content</li>
                    </ul>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> Be careful when granting administrator privileges.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>