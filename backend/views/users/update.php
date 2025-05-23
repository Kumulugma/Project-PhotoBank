<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerControllerCss($this, 'users');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $currentRole string */

$this->title = 'Edytuj użytkownika: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Użytkownicy', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edytuj';

// Role options
$roleOptions = [
    'user' => 'Użytkownik',
    'admin' => 'Administrator',
];

// Status options
$statusOptions = [
    \common\models\User::STATUS_ACTIVE => 'Aktywny',
    \common\models\User::STATUS_INACTIVE => 'Nieaktywny',
    \common\models\User::STATUS_DELETED => 'Usunięty',
];

// Check if this is the only admin
$auth = Yii::$app->authManager;
$adminUsers = $auth ? $auth->getUserIdsByRole('admin') : [];
$isOnlyAdmin = count($adminUsers) <= 1 && in_array($model->id, $adminUsers);
?>
<div class="user-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye me-2"></i>Zobacz profil', ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-info'
            ]) ?>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista użytkowników', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>Informacje o użytkowniku
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control',
                        'required' => true,
                    ])->label('Nazwa użytkownika') ?>

                    <?= $form->field($model, 'email')->textInput([
                        'maxlength' => true,
                        'type' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                    ])->label('Adres email') ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Wprowadź nowe hasło lub zostaw puste aby zachować obecne'
                    ])->label('Nowe hasło')->hint('Zostaw puste jeśli nie chcesz zmieniać hasła') ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions, [
                        'class' => 'form-select'
                    ])->label('Status') ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Rola</label>
                        <?= Html::dropDownList('role', $currentRole, $roleOptions, [
                            'class' => 'form-select',
                            'id' => 'role',
                            'disabled' => $isOnlyAdmin,
                        ]) ?>
                        <div class="form-text">
                            <?= $isOnlyAdmin ? 'Nie można zmienić roli jedynego administratora' : 'Wybierz rolę użytkownika' ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i>Zapisz zmiany', [
                            'class' => 'btn btn-success'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Informacje o rolach
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">Użytkownik</h6>
                                    <ul class="list-unstyled small text-start">
                                        <li><i class="fas fa-check text-success me-1"></i>Dostęp do frontendu</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Zarządzanie własnymi zdjęciami</li>
                                        <li><i class="fas fa-times text-danger me-1"></i>Brak dostępu do panelu admin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-shield fa-2x text-danger mb-2"></i>
                                    <h6 class="card-title">Administrator</h6>
                                    <ul class="list-unstyled small text-start">
                                        <li><i class="fas fa-check text-success me-1"></i>Pełny dostęp do systemu</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Panel administracyjny</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Zarządzanie użytkownikami</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Ustawienia systemowe</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($isOnlyAdmin): ?>
                        <div class="alert alert-danger mt-3 mb-0">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Uwaga!</h6>
                            <p class="mb-0">To jest jedyny administrator w systemie. Nie można zmienić jego roli ani usunąć konta.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Ważne informacje
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-key me-2"></i>Hasło</h6>
                        <p class="mb-0">Zostaw pole hasła puste jeśli nie chcesz go zmieniać. Wprowadź nowe hasło tylko gdy chcesz je zaktualizować.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-user-tag me-2"></i>Status użytkownika</h6>
                        <ul class="mb-0">
                            <li><strong>Aktywny:</strong> Użytkownik może się logować i korzystać z systemu</li>
                            <li><strong>Nieaktywny:</strong> Logowanie zablokowane do aktywacji</li>
                            <li><strong>Usunięty:</strong> Soft-delete - dane zachowane ale dostęp zablokowany</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success mb-0">
                        <h6><i class="fas fa-chart-line me-2"></i>Statystyki użytkownika</h6>
                        <ul class="mb-0">
                            <li>Zarejestrowany: <?= Yii::$app->formatter->asDatetime($model->created_at) ?></li>
                            <li>Ostatnia aktualizacja: <?= Yii::$app->formatter->asDatetime($model->updated_at) ?></li>
                            <?php if ($model->last_login_at): ?>
                                <li>Ostatnie logowanie: <?= Yii::$app->formatter->asDatetime($model->last_login_at) ?></li>
                            <?php else: ?>
                                <li>Nigdy się nie logował</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>