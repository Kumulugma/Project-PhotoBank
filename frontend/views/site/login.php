<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model common\models\LoginForm */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Logowanie';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header text-center">
                    <h1 class="card-title"><?= Html::encode($this->title) ?></h1>
                </div>
                
                    <p class="text-muted">Zaloguj się do swojego konta, aby uzyskać dostęp do galerii zdjęć.</p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'col-form-label'],
                        ],
                    ]); ?>

                        <?= $form->field($model, 'username')->textInput([
                            'autofocus' => true,
                            'class' => 'form-control',
                            'placeholder' => 'Wprowadź nazwę użytkownika'
                        ]) ?>

                        <?= $form->field($model, 'password')->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Wprowadź hasło'
                        ]) ?>

                        <?= $form->field($model, 'rememberMe')->checkbox([
                            'template' => "<div class=\"form-check\">{input} {label}</div>\n<div>{error}</div>",
                            'labelOptions' => ['class' => 'form-check-label'],
                            'inputOptions' => ['class' => 'form-check-input'],
                        ]) ?>

                        <div class="form-group text-center">
                            <?= Html::submitButton('Zaloguj się', [
                                'class' => 'btn btn-primary btn-lg w-100', 
                                'name' => 'login-button'
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Nie masz konta? Skontaktuj się z administratorem.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>