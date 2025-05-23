<?php
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="error-icon mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="h2 mb-3"><?= Html::encode($this->title) ?></h1>
                    
                    <div class="alert alert-danger">
                        <?= nl2br(Html::encode($message)) ?>
                    </div>
                    
                    <p class="text-muted mb-4">
                        Powyższy błąd wystąpił podczas przetwarzania Twojego żądania.
                    </p>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Strona główna
                        </a>
                        <button onclick="history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Powrót
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>