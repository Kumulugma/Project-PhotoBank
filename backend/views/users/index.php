<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\User::STATUS_DELETED => 'Deleted',
    \common\models\User::STATUS_INACTIVE => 'Inactive',
    \common\models\User::STATUS_ACTIVE => 'Active',
];

// Role options
$roleOptions = [
    'admin' => 'Administrator',
    'user' => 'User',
];
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email:email',
            [
                'attribute' => 'status',
                'value' => function ($model) use ($statusOptions) {
                    return $statusOptions[$model->status] ?? 'Unknown';
                },
                'filter' => $statusOptions,
                'contentOptions' => function ($model) {
                    if ($model->status === \common\models\User::STATUS_DELETED) {
                        return ['class' => 'text-danger'];
                    } elseif ($model->status === \common\models\User::STATUS_INACTIVE) {
                        return ['class' => 'text-warning'];
                    } elseif ($model->status === \common\models\User::STATUS_ACTIVE) {
                        return ['class' => 'text-success'];
                    }
                    return [];
                },
            ],
            [
                'attribute' => 'role',
                'value' => function ($model) use ($roleOptions) {
                    $auth = Yii::$app->authManager;
                    $roles = $auth->getRolesByUser($model->id);
                    $roleNames = array_keys($roles);
                    $roleLabels = [];
                    
                    foreach ($roleNames as $roleName) {
                        $roleLabels[] = $roleOptions[$roleName] ?? $roleName;
                    }
                    
                    return implode(', ', $roleLabels);
                },
                'filter' => $roleOptions,
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return date('Y-m-d H:i', $model->created_at);
                },
                'filter' => \yii\jui\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_at',
                    'language' => 'en',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control', 'placeholder' => 'Registration Date'],
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>