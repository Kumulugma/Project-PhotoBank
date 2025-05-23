<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Logowanie';
$this->context->layout = 'main-login';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="login-card card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Zasobnik B</h3>
                        <p class="text-muted">Panel administracyjny</p>
                    </div>
                    
                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Nazwa użytkownika',
                        'class' => 'form-control form-control-lg',
                    ])->label(false) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Hasło',
                        'class' => 'form-control form-control-lg',
                    ])->label(false) ?>

                    <div class="mb-3">
                        <?= $form->field($model, 'rememberMe')->checkbox([
                            'template' => '<div class="form-check">{input} {label}</div>{error}',
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
                <small class="text-white-50">
                    © <?= date('Y') ?> Zasobnik B. Wszystkie prawa zastrzeżone.
                </small>
            </div>
        </div>
    </div>
</div>