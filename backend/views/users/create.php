<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Dodaj użytkownika';
$this->params['breadcrumbs'][] = ['label' => 'Użytkownicy', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Role options
$roleOptions = [
    'user' => 'Użytkownik',
    'admin' => 'Administrator',
];

// Status options
$statusOptions = [
    \common\models\User::STATUS_ACTIVE => 'Aktywny',
    \common\models\User::STATUS_INACTIVE => 'Nieaktywny',
];
?>
<div class="user-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
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
                        <i class="fas fa-user-plus me-2"></i>Informacje o użytkowniku
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
                        'placeholder' => 'Wprowadź nazwę użytkownika'
                    ])->label('Nazwa użytkownika') ?>

                    <?= $form->field($model, 'email')->textInput([
                        'maxlength' => true,
                        'type' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Wprowadź adres email'
                    ])->label('Adres email') ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Wprowadź hasło'
                    ])->label('Hasło') ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions, [
                        'class' => 'form-select'
                    ])->label('Status') ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Rola</label>
                        <?= Html::dropDownList('role', 'user', $roleOptions, [
                            'class' => 'form-select',
                            'id' => 'role',
                        ]) ?>
                        <div class="form-text">Wybierz rolę dla nowego użytkownika</div>
                    </div>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i>Utwórz użytkownika', [
                            'class' => 'btn btn-success'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['index'], [
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
                        <i class="fas fa-info-circle me-2"></i>Role użytkowników
                    </h5>
                </div>
                <div class="card-body">
                    <p>Wybierz odpowiednią rolę dla tego użytkownika:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-user fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">Użytkownik</h6>
                                    <ul class="list-unstyled small text-start">
                                        <li><i class="fas fa-check text-success me-1"></i>Przeglądanie publicznych zdjęć</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Zarządzanie własnymi zdjęciami</li>
                                        <li><i class="fas fa-times text-danger me-1"></i>Brak dostępu do panelu admin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-shield fa-2x text-danger mb-2"></i>
                                    <h6 class="card-title">Administrator</h6>
                                    <ul class="list-unstyled small text-start">
                                        <li><i class="fas fa-check text-success me-1"></i>Pełny dostęp do systemu</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Zarządzanie wszystkimi użytkownikami</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Panel administracyjny</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Ustawienia systemowe</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Ważne informacje
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-envelope me-2"></i>Powiadomienie email</h6>
                        <p class="mb-0">Nowy użytkownik otrzyma email z danymi logowania (jeśli skonfigurowany SMTP).</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-key me-2"></i>Bezpieczeństwo hasła</h6>
                        <ul class="mb-0">
                            <li>Używaj silnych haseł (min. 8 znaków)</li>
                            <li>Kombinuj litery, cyfry i znaki specjalne</li>
                            <li>Unikaj prostych haseł jak "123456"</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success mb-0">
                        <h6><i class="fas fa-lightbulb me-2"></i>Wskazówki</h6>
                        <ul class="mb-0">
                            <li>Nazwa użytkownika musi być unikalna</li>
                            <li>Adres email służy do odzyskiwania hasła</li>
                            <li>Status można później zmienić w edycji</li>
                            <li>Role można modyfikować po utworzeniu konta</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>