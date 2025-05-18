<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Logowanie';

// Don't show layout for login page
$this->context->layout = 'main-login';
?>
<div class="login-page">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-primary">
                <div class="text-center text-white">
                    <i class="fas fa-camera fa-5x mb-4"></i>
                    <h2 class="mb-4">Zasobnik B</h2>
                    <p class="lead">System zarządzania zdjęciami</p>
                    <p>Zaloguj się do panelu administracyjnego</p>
                </div>
            </div>
            
            <div class="col-lg-6 d-flex align-items-center">
                <div class="w-100">
                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6">
                            <div class="card shadow-sm">
                                <div class="card-body p-5">
                                    <div class="text-center mb-4">
                                        <h3 class="card-title">Panel administracyjny</h3>
                                        <p class="text-muted">Wprowadź swoje dane logowania</p>
                                    </div>
                                    
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'login-form',
                                        'options' => ['class' => 'needs-validation'],
                                        'fieldConfig' => [
                                            'template' => '{input}{error}',
                                            'options' => ['class' => 'mb-3'],
                                            'inputOptions' => ['class' => 'form-control form-control-lg'],
                                        ],
                                    ]); ?>

                                    <?= $form->field($model, 'username', [
                                        'inputOptions' => [
                                            'autofocus' => true,
                                            'placeholder' => 'Nazwa użytkownika',
                                            'class' => 'form-control form-control-lg',
                                            'required' => true,
                                        ]
                                    ])->textInput()->label(false) ?>

                                    <?= $form->field($model, 'password', [
                                        'inputOptions' => [
                                            'placeholder' => 'Hasło',
                                            'class' => 'form-control form-control-lg',
                                            'required' => true,
                                        ]
                                    ])->passwordInput()->label(false) ?>

                                    <div class="mb-3">
                                        <?= $form->field($model, 'rememberMe')->checkbox([
                                            'template' => '<div class="form-check">{input} {label}</div>{error}',
                                            'labelOptions' => ['class' => 'form-check-label'],
                                            'inputOptions' => ['class' => 'form-check-input'],
                                        ]) ?>
                                    </div>

                                    <div class="d-grid">
                                        <?= Html::submitButton('<i class="fas fa-sign-in-alt me-2"></i>Zaloguj się', [
                                            'class' => 'btn btn-primary btn-lg',
                                            'name' => 'login-button'
                                        ]) ?>
                                    </div>

                                    <?php ActiveForm::end(); ?>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Bezpieczne logowanie SSL
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    © <?= date('Y') ?> Zasobnik B. Wszystkie prawa zastrzeżone.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.login-page .card {
    border: none;
    border-radius: 1rem;
}

.login-page .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.login-page .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
}

.login-page .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>