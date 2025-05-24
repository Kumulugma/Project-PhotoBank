<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerControllerCss($this, 'watermark');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'Ustawienia znaku wodnego';
$this->params['breadcrumbs'][] = $this->title;

// Position options
$positionOptions = [
    'top-left' => 'Lewy górny',
    'top-right' => 'Prawy górny',
    'bottom-left' => 'Lewy dolny',
    'bottom-right' => 'Prawy dolny',
    'center' => 'Środek',
];
?>
<div class="watermark-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tint me-2"></i>Konfiguracja znaku wodnego
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'watermark-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'needs-validation', 'enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label">Typ znaku wodnego</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" value="text" 
                                   <?= $settings['type'] === 'text' ? 'checked' : '' ?> id="watermark-type-text">
                            <label class="form-check-label" for="watermark-type-text">
                                <i class="fas fa-font me-2"></i>Tekst
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" value="image" 
                                   <?= $settings['type'] === 'image' ? 'checked' : '' ?> id="watermark-type-image">
                            <label class="form-check-label" for="watermark-type-image">
                                <i class="fas fa-image me-2"></i>Obraz
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 watermark-text-fields" style="<?= $settings['type'] === 'image' ? 'display: none;' : '' ?>">
                        <label class="form-label">Tekst znaku wodnego</label>
                        <input type="text" class="form-control" name="text" 
                               value="<?= Html::encode($settings['text']) ?>"
                               placeholder="© Moja firma">
                        <div class="form-text">Tekst do wyświetlenia jako znak wodny</div>
                    </div>

                    <div class="mb-3 watermark-image-fields" style="<?= $settings['type'] === 'text' ? 'display: none;' : '' ?>">
                        <label class="form-label">Obrazek znaku wodnego</label>
                        <?php if (!empty($settings['image_url'])): ?>
                            <div class="mb-2">
                                <img src="<?= $settings['image_url'] ?>" alt="Aktualny znak wodny" 
                                     class="img-thumbnail" style="max-height: 100px;">
                                <div class="form-text">Aktualny obrazek znaku wodnego</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image" id="watermark-image-upload" accept="image/*">
                        <div class="form-text">Prześlij PNG lub GIF z przezroczystością dla najlepszych rezultatów</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pozycja</label>
                        <?= Html::dropDownList('position', $settings['position'], $positionOptions, [
                            'class' => 'form-select',
                            'id' => 'watermark-position',
                        ]) ?>
                        <div class="form-text">Gdzie na zdjęciu umieścić znak wodny</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Przezroczystość</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-range flex-grow-1" name="opacity" 
                                   min="0" max="1" step="0.1" value="<?= $settings['opacity'] ?>" id="watermark-opacity">
                            <span class="badge bg-secondary watermark-opacity-display" id="opacity-value"><?= $settings['opacity'] * 100 ?>%</span>
                        </div>
                        <div class="form-text">Poziom przezroczystości znaku wodnego (0% = niewidoczny, 100% = nieprzezroczysty)</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Zapisz ustawienia
                        </button>
                        <button type="button" class="btn btn-info" id="preview-watermark-btn">
                            <i class="fas fa-eye me-2"></i>Podgląd
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
                        <i class="fas fa-search me-2"></i>Podgląd znaku wodnego
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div id="watermark-preview-container" class="watermark-preview-container">
                        <div id="watermark-preview-placeholder" class="p-5">
                            <i class="fas fa-image fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Kliknij "Podgląd" aby zobaczyć efekt</p>
                        </div>
                        <div id="watermark-preview-loading" class="watermark-preview-loading p-5" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                            <p class="text-primary">Generowanie podglądu...</p>
                        </div>
                        <div id="watermark-preview-result" class="watermark-preview-result" style="display: none;">
                            <img src="" alt="Podgląd znaku wodnego" class="img-fluid rounded shadow" id="watermark-preview-image">
                            <div class="mt-2">
                                <small class="text-muted">Podgląd na przykładowym obrazie</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card watermark-info-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>O znakach wodnych
                    </h5>
                </div>
                <div class="card-body">
                    <p>Znaki wodne są nakładane na zdjęcia aby:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-shield-alt text-primary me-2"></i>Chronić przed nieautoryzowanym użyciem</li>
                        <li><i class="fas fa-copyright text-info me-2"></i>Oznaczyć własność treści</li>
                        <li><i class="fas fa-award text-success me-2"></i>Promować markę lub logo</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-3">Rekomendacje:</h6>
                    <div class="row watermark-recommendation-cards">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-font fa-2x text-warning mb-2"></i>
                                    <h6>Tekstowe:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success me-1"></i>Używaj krótkiego tekstu</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Wybierz czytelną czcionkę</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Umieść w rogu zdjęcia</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-image fa-2x text-info mb-2"></i>
                                    <h6>Obrazkowe:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success me-1"></i>Używaj PNG z przezroczystością</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Optymalne wymiary 200x200px</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Unikaj zbyt dużych plików</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3 mb-0">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Uwaga</h6>
                        <ul class="mb-0">
                            <li>Znak wodny jest dodawany do miniatur zgodnie z ustawieniami rozmiarów</li>
                            <li>Ustaw przezroczystość poniżej 50% aby nie zasłaniać zdjęcia</li>
                            <li>Po zmianie ustawień regeneruj miniatury aby zastosować nowy znak</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle watermark type toggle
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const textFields = document.querySelector('.watermark-text-fields');
    const imageFields = document.querySelector('.watermark-image-fields');
    
    function toggleWatermarkFields() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;
        
        if (selectedType === 'text') {
            textFields.style.display = 'block';
            imageFields.style.display = 'none';
        } else {
            textFields.style.display = 'none';
            imageFields.style.display = 'block';
        }
    }
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleWatermarkFields);
    });
    
    // Update opacity percentage display
    const opacityRange = document.getElementById('watermark-opacity');
    const opacityValue = document.getElementById('opacity-value');
    
    opacityRange.addEventListener('input', function() {
        opacityValue.textContent = Math.round(this.value * 100) + '%';
    });
    
    // Preview watermark
    const previewBtn = document.getElementById('preview-watermark-btn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const placeholder = document.getElementById('watermark-preview-placeholder');
            const loading = document.getElementById('watermark-preview-loading');
            const result = document.getElementById('watermark-preview-result');
            
            // Show loading
            placeholder.style.display = 'none';
            result.style.display = 'none';
            loading.style.display = 'block';
            
            // Create form data
            const formData = new FormData();
            formData.append('type', document.querySelector('input[name="type"]:checked').value);
            formData.append('text', document.querySelector('input[name="text"]').value);
            formData.append('position', document.getElementById('watermark-position').value);
            formData.append('opacity', document.getElementById('watermark-opacity').value);
            
            // Add image if selected
            const imageFile = document.getElementById('watermark-image-upload').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }
            
            // Send AJAX request
            fetch('<?= Url::to(['preview']) ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('watermark-preview-image').src = data.preview;
                    result.style.display = 'block';
                } else {
                    showToast('Błąd podczas generowania podglądu: ' + (data.message || 'Nieznany błąd'), 'error');
                    placeholder.style.display = 'block';
                }
            })
            .catch(error => {
                showToast('Błąd podczas generowania podglądu', 'error');
                placeholder.style.display = 'block';
            })
            .finally(() => {
                loading.style.display = 'none';
            });
        });
    }
});
</script>