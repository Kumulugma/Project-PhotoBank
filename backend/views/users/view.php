<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $roles array */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Użytkownicy', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Status options
$statusOptions = [
    \common\models\User::STATUS_DELETED => 'Usunięty',
    \common\models\User::STATUS_INACTIVE => 'Nieaktywny',
    \common\models\User::STATUS_ACTIVE => 'Aktywny',
];

// Role labels
$roleLabels = [
    'admin' => 'Administrator',
    'user' => 'Użytkownik',
];
?>
<div class="user-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Czy na pewno chcesz usunąć tego użytkownika?',
                'data-method' => 'post',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Szczegóły użytkownika
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
                            ],
                            [
                                'attribute' => 'username',
                                'label' => 'Nazwa użytkownika',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'email',
                                'label' => 'Adres email',
                                'format' => 'email',
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Status',
                                'format' => 'raw',
                                'value' => function($model) use ($statusOptions) {
                                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                                    $badgeClass = match($model->status) {
                                        \common\models\User::STATUS_ACTIVE => 'bg-success',
                                        \common\models\User::STATUS_INACTIVE => 'bg-warning text-dark',
                                        \common\models\User::STATUS_DELETED => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                                },
                            ],
                            [
                                'label' => 'Role',
                                'format' => 'raw',
                                'value' => function ($model) use ($roles, $roleLabels) {
                                    if (empty($roles)) {
                                        return '<span class="badge bg-light text-dark">Brak ról</span>';
                                    }
                                    
                                    $html = '';
                                    foreach ($roles as $role) {
                                        $label = $roleLabels[$role] ?? $role;
                                        $badgeClass = $role === 'admin' ? 'bg-danger' : 'bg-info';
                                        $html .= '<span class="badge ' . $badgeClass . ' me-1">' . $label . '</span>';
                                    }
                                    return $html;
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data rejestracji',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Ostatnia aktualizacja',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                            [
                                'label' => 'Ostatnie logowanie',
                                'value' => $model->last_login_at ? date('Y-m-d H:i:s', $model->last_login_at) : 'Nigdy',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Aktywność użytkownika
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get user activity statistics
                    $uploadedPhotosCount = \common\models\Photo::find()
                        ->where(['created_by' => $model->id])
                        ->count();
                    $activePhotosCount = \common\models\Photo::find()
                        ->where(['created_by' => $model->id, 'status' => \common\models\Photo::STATUS_ACTIVE])
                        ->count();
                    $queuedPhotosCount = \common\models\Photo::find()
                        ->where(['created_by' => $model->id, 'status' => \common\models\Photo::STATUS_QUEUE])
                        ->count();
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-primary mb-0"><?= $uploadedPhotosCount ?></h3>
                                <small class="text-muted">Wszystkich zdjęć</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-success mb-0"><?= $activePhotosCount ?></h3>
                                <small class="text-muted">Aktywnych</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning mb-0"><?= $queuedPhotosCount ?></h3>
                            <small class="text-muted">W kolejce</small>
                        </div>
                    </div>
                    
                    <?php if ($uploadedPhotosCount > 0): ?>
                        <hr class="my-3">
                        <div class="d-grid">
                            <?= Html::a('<i class="fas fa-images me-2"></i>Zobacz zdjęcia użytkownika', 
                                ['/photos/index', 'PhotoSearch[created_by]' => $model->id], 
                                ['class' => 'btn btn-outline-primary']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informacje o koncie
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-calendar-plus text-info me-2"></i>
                            <strong>Wiek konta:</strong> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                        </li>
                        <?php if ($model->last_login_at): ?>
                            <li class="mb-2">
                                <i class="fas fa-clock text-success me-2"></i>
                                <strong>Ostatnia aktywność:</strong> <?= Yii::$app->formatter->asRelativeTime($model->last_login_at) ?>
                            </li>
                        <?php else: ?>
                            <li class="mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <strong>Ostatnia aktywność:</strong> Nigdy się nie logował
                            </li>
                        <?php endif; ?>
                        <li>
                            <i class="fas fa-edit text-warning me-2"></i>
                            <strong>Ostatnia edycja:</strong> <?= Yii::$app->formatter->asRelativeTime($model->updated_at) ?>
                        </li>
                    </ul>
                </div>
            </div>
            
            <?php if (in_array('admin', $roles)): ?>
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Uprawnienia administratora
                    </h5>
                </div>
                <div class="card-body">
                    <p>Ten użytkownik posiada uprawnienia administratora, które obejmują:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Pełny dostęp do ustawień systemowych</li>
                        <li><i class="fas fa-check text-success me-2"></i>Zarządzanie wszystkimi użytkownikami</li>
                        <li><i class="fas fa-check text-success me-2"></i>Dostęp do wszystkich zdjęć, včetně prywatnych</li>
                        <li><i class="fas fa-check text-success me-2"></i>Możliwość zatwierdzania, modyfikacji i usuwania treści</li>
                        <li><i class="fas fa-check text-success me-2"></i>Konfiguracja integracji (S3, AI, znaki wodne)</li>
                    </ul>
                    <div class="alert alert-warning mb-0">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Ostrzeżenie:</strong> 
                        Zachowaj ostrożność przy przyznawaniu uprawnień administratora.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.detail-view th {
    width: 200px;
    font-weight: 600;
    background-color: #f8f9fa;
}

.detail-view td {
    word-break: break-word;
}

.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
</style>