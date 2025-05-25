<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

$this->title = 'Ustawienia EXIF';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exif-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera me-2"></i>Konfiguracja EXIF
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'exif-settings-form',
                        'action' => ['update-settings'],
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label">Domyślny artysta/autor</label>
                        <input type="text" class="form-control" name="default_artist" 
                               value="<?= Html::encode($settings['default_artist']) ?>"
                               placeholder="np. Jan Kowalski">
                        <div class="form-text">Wartość która zostanie ustawiona w polu Artist/Creator w EXIF</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Domyślne prawa autorskie</label>
                        <input type="text" class="form-control" name="default_copyright" 
                               value="<?= Html::encode($settings['default_copyright']) ?>"
                               placeholder="np. © 2024 Jan Kowalski. All rights reserved.">
                        <div class="form-text">Wartość która zostanie ustawiona w polu Copyright w EXIF</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Zapisz ustawienia
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>O ustawieniach EXIF
                    </h5>
                </div>
                <div class="card-body">
                    <p>EXIF (Exchangeable Image File Format) to metadane przechowywane w plikach obrazów.</p>
                    
                    <h6 class="fw-bold mt-3">Co można ustawić:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-user text-primary me-2"></i><strong>Artist/Creator:</strong> Imię i nazwisko autora zdjęcia</li>
                        <li><i class="fas fa-copyright text-info me-2"></i><strong>Copyright:</strong> Informacje o prawach autorskich</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-3">Jak to działa:</h6>
                    <ol>
                        <li>Skonfiguruj domyślne wartości w polach powyżej</li>
                        <li>Przejdź do szczegółów dowolnego zdjęcia</li>
                        <li>Kliknij przycisk "Ustaw dane EXIF"</li>
                        <li>Dane zostaną zapisane w pliku obrazu</li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Wymagania systemowe
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-tools me-2"></i>Wymagane oprogramowanie</h6>
                        <p class="mb-2">Do modyfikacji EXIF wymagany jest <strong>exiftool</strong>:</p>
                        <ul class="mb-2">
                            <li><strong>Ubuntu/Debian:</strong> <code>apt install libimage-exiftool-perl</code></li>
                            <li><strong>CentOS/RHEL:</strong> <code>yum install perl-Image-ExifTool</code></li>
                            <li><strong>macOS:</strong> <code>brew install exiftool</code></li>
                        </ul>
                        <small>Bez exiftool funkcja ustawiania EXIF nie będzie działać.</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <h6><i class="fas fa-shield-alt me-2"></i>Bezpieczeństwo</h6>
                        <p class="mb-0">
                            Modyfikacja EXIF tworzy tymczasową kopię zapasową pliku. 
                            W przypadku błędu plik zostanie przywrócony do oryginalnego stanu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>