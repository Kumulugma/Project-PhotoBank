<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\DropzoneAsset;
\backend\assets\AppAsset::registerControllerCss($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

/* @var $this yii\web\View */

$this->title = 'Prześlij zdjęcia';
$this->params['breadcrumbs'][] = ['label' => 'Wszystkie zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register Dropzone asset
DropzoneAsset::register($this);
?>

<div class="photo-upload">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-cloud-upload-alt me-2"></i> Wgraj zdjęcia
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <span>Przeciągnij i upuść zdjęcia tutaj lub kliknij, aby przeglądać pliki. Możesz wgrać wiele zdjęć jednocześnie.</span>
                <p class="mb-0 mt-2">Dozwolone typy plików: JPG, PNG, GIF. Maksymalny rozmiar pliku: <?= ini_get('upload_max_filesize') ?></p>
            </div>
            
            <!-- Używamy standardowej klasy 'dropzone' dla formularza, ale bez auto-discover -->
            <form action="<?= Url::to(['upload-ajax']) ?>" class="dropzone" id="my-dropzone">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="fallback">
                    <input name="file" type="file" multiple>
                </div>
                <div class="dz-message">
                    <div>
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3em; color: #0087F7;"></i>
                        <h3>Przeciągnij i upuść pliki tutaj</h3>
                        <p>lub</p>
                        <button type="button" class="dz-button">Wybierz pliki</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <button type="button" id="submit-all" class="btn btn-success" style="display: none;">
                <i class="fas fa-upload me-2"></i> Wgraj wszystkie zdjęcia
            </button>
        </div>
    </div>
    
    <div id="uploaded-photos-panel" class="card" style="display: none;">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-check-circle me-2"></i> Wgrane zdjęcia
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <p><i class="fas fa-check me-2"></i> Twoje zdjęcia zostały pomyślnie wgrane. Są teraz w poczekalni oczekując na zatwierdzenie.</p>
                <p class="mb-0">Możesz je zatwierdzić na stronie <a href="<?= Url::to(['queue']) ?>" class="alert-link">Poczekalnia zdjęć</a>.</p>
            </div>
            
            <div id="uploaded-photos-container" class="row g-3">
                <!-- Tutaj będą dynamicznie dodawane miniatury -->
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <button type="button" id="upload-more-btn" class="btn btn-outline-primary">
                <i class="fas fa-plus me-2"></i> Wgraj więcej zdjęć
            </button>
            <a href="<?= Url::to(['queue']) ?>" class="btn btn-primary">
                <i class="fas fa-tasks me-2"></i> Przejdź do poczekalni
            </a>
        </div>
    </div>
</div>
