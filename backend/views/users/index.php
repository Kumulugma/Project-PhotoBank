<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

\backend\assets\AppAsset::registerControllerCss($this, 'users');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Użytkownicy';
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\User::STATUS_DELETED => 'Usunięty',
    \common\models\User::STATUS_INACTIVE => 'Nieaktywny',
    \common\models\User::STATUS_ACTIVE => 'Aktywny',
];

// Role options
$roleOptions = [
    'admin' => 'Administrator',
    'user' => 'Użytkownik',
];
?>
<div class="user-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?=
            Html::a('<i class="fas fa-plus me-2"></i>Dodaj użytkownika', ['create'], [
                'class' => 'btn btn-success'
            ])
            ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'users-grid-pjax']); ?>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => 'Wyświetlono <b>{begin}-{end}</b> z <b>{totalCount}</b> wpisów',
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->username), ['view', 'id' => $model->id], [
                        'class' => 'fw-bold text-decoration-none'
                    ]);
                },
            ],
            [
                'attribute' => 'email',
                'format' => 'email',
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) use ($statusOptions) {
                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                    $badgeClass = match ($model->status) {
                        \common\models\User::STATUS_ACTIVE => 'bg-success',
                        \common\models\User::STATUS_INACTIVE => 'bg-warning text-dark',
                        \common\models\User::STATUS_DELETED => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'status', $statusOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie'
                ]),
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'label' => 'Role',
                'format' => 'raw',
                'value' => function ($model) use ($roleOptions) {
                    $auth = Yii::$app->authManager;
                    $roles = $auth ? $auth->getRolesByUser($model->id) : [];

                    if (empty($roles)) {
                        return '<span class="badge bg-light text-dark">Brak ról</span>';
                    }

                    $html = '';
                    foreach ($roles as $roleName => $role) {
                        $badgeClass = $roleName === 'admin' ? 'bg-danger' : 'bg-info';
                        $label = $roleOptions[$roleName] ?? $roleName;
                        $html .= '<span class="badge ' . $badgeClass . ' me-1">' . $label . '</span>';
                    }

                    return $html;
                },
                'filter' => Html::activeDropDownList($searchModel, 'role', $roleOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie role'
                ]),
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'label' => 'Ostatnia aktywność',
                'format' => 'raw',
                'value' => function ($model) {
                    // BEZPIECZNE użycie last_login_at
                    if (property_exists($model, 'last_login_at') && $model->last_login_at) {
                        $lastLogin = Yii::$app->formatter->asRelativeTime($model->last_login_at);
                        $fullDate = date('Y-m-d H:i:s', $model->last_login_at);
                        return '<span title="' . $fullDate . '" class="text-success">' . $lastLogin . '</span>';
                    }
                    return '<span class="text-muted">Nigdy</span>';
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '<div class="btn-group-actions">{view}{update}{delete}</div>',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Zobacz',
                            'data-pjax' => 0,
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Edytuj',
                            'data-pjax' => 0,
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        // Check if this is the only admin
                        $auth = Yii::$app->authManager;
                        $isAdmin = $auth && $auth->getAssignment('admin', $model->id);
                        $adminCount = $auth ? count($auth->getUserIdsByRole('admin')) : 0;

                        if ($isAdmin && $adminCount <= 1) {
                            return '<span class="btn btn-sm btn-outline-secondary disabled" title="Nie można usunąć jedynego administratora">
                                <i class="fas fa-ban"></i>
                            </span>';
                        }

                        $photoCount = \common\models\Photo::find()->where(['created_by' => $model->id])->count();
                        $confirmMsg = $photoCount > 0 ? "Ten użytkownik ma {$photoCount} zdjęć. Czy na pewno chcesz go usunąć?" : 'Czy na pewno chcesz usunąć tego użytkownika?';

                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Usuń',
                            'data-confirm' => $confirmMsg,
                            'data-method' => 'post',
                            'data-pjax' => 0,
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width: 120px;'],
                'contentOptions' => ['class' => 'text-end'],
            ],
        ],
    ]);
    ?>

<?php Pjax::end(); ?>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statystyki użytkowników
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get user statistics
                    $totalUsers = $dataProvider->totalCount;
                    $activeUsers = \common\models\User::find()->where(['status' => \common\models\User::STATUS_ACTIVE])->count();
                    $adminUsers = Yii::$app->authManager ? count(Yii::$app->authManager->getUserIdsByRole('admin')) : 0;
                    $recentUsers = \common\models\User::find()->where(['>', 'created_at', time() - 30 * 24 * 3600])->count();
                    ?>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="border-end">
                                <h3 class="text-primary mb-0"><?= $totalUsers ?></h3>
                                <small class="text-muted">Łącznie</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h3 class="text-success mb-0"><?= $activeUsers ?></h3>
                                <small class="text-muted">Aktywnych</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h3 class="text-danger mb-0"><?= $adminUsers ?></h3>
                                <small class="text-muted">Administratorów</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <h3 class="text-info mb-0"><?= $recentUsers ?></h3>
                            <small class="text-muted">Nowych (30 dni)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Zarządzanie rolami
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small">System ról umożliwia kontrolę dostępu do funkcji:</p>
                    <ul class="list-unstyled small">
                        <li>
                            <span class="badge bg-danger me-2">Administrator</span>
                            Pełny dostęp do systemu
                        </li>
                        <li>
                            <span class="badge bg-info me-2">Użytkownik</span>
                            Dostęp do podstawowych funkcji
                        </li>
                    </ul>

                    <div class="alert alert-sm alert-warning mb-0">
                        <strong>Uwaga:</strong> W systemie musi być co najmniej jeden administrator.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>