<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="thumbnail-size-form">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Ustawienia miniatur
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <?= $form->field($model, 'name')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'np. small, medium, large'
                    ])->label('Nazwa rozmiaru')->hint('Unikalna nazwa identyfikująca ten rozmiar miniatur') ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'width')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'max' => 5000,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => '300'
                            ])->label('Szerokość (px)') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'height')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'max' => 5000,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => '300'
                            ])->label('Wysokość (px)') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Opcje generowania</label>
                        
                        <div class="form-check">
                            <?= Html::activeCheckbox($model, 'crop', [
                                'class' => 'form-check-input',
                                'id' => 'thumbnail-crop'
                            ]) ?>
                            <label class="form-check-label" for="thumbnail-crop">
                                <i class="fas fa-crop me-1"></i>Kadrowanie
                            </label>
                            <div class="form-text">Gdy włączone, miniatura będzie przycięta aby dokładnie pasować do wymiarów. Gdy wyłączone, obraz zostanie przeskalowany zachowując proporcje.</div>
                        </div>

                        <div class="form-check">
                            <?= Html::activeCheckbox($model, 'watermark', [
                                'class' => 'form-check-input',
                                'id' => 'thumbnail-watermark'
                            ]) ?>
                            <label class="form-check-label" for="thumbnail-watermark">
                                <i class="fas fa-tint me-1"></i>Znak wodny
                            </label>
                            <div class="form-text">Gdy włączone, skonfigurowany znak wodny zostanie dodany do miniatur tego rozmiaru.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i>Zapisz', [
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
                        <i class="fas fa-info-circle me-2"></i>Informacje o miniaturach
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Nazwa:</strong> Unikalny identyfikator dla tego rozmiaru miniatur (np. small, medium, large)</p>
                    <p><strong>Szerokość i wysokość:</strong> Wymiary miniatur w pikselach</p>
                    
                    <h6 class="fw-bold">Tryby skalowania:</h6>
                    <div class="row size-recommendation-cards">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-crop fa-2x text-warning mb-2"></i>
                                    <h6>Kadrowanie</h6>
                                    <p class="small mb-0">Miniatura będzie przycięta aby dokładnie pasować do określonych wymiarów. Część obrazu może zostać obcięta.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-expand-arrows-alt fa-2x text-info mb-2"></i>
                                    <h6>Dopasowanie</h6>
                                    <p class="small mb-0">Miniatura zostanie przeskalowana aby zmieścić się w wymiarach zachowując oryginalne proporcje.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mt-3">Znak wodny:</h6>
                    <p class="small">Gdy opcja jest włączona, skonfigurowany znak wodny będzie aplikowany do miniatur tego rozmiaru. Skonfiguruj znak wodny w <a href="<?= yii\helpers\Url::to(['/watermark/index']) ?>" target="_blank">ustawieniach znaku wodnego</a>.</p>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne</h6>
                        <ul class="mb-0">
                            <li>Po utworzeniu nowego rozmiaru musisz regenerować miniatury dla istniejących zdjęć</li>
                            <li>Nazwa rozmiaru musi być unikalna w systemie</li>
                            <li>Zalecane wymiary to 150×150px (small), 300×300px (medium), 600×600px (large)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Podgląd wymiarów
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div id="dimension-preview" class="dimension-preview-container border rounded p-3">
                        <div id="preview-box" style="border: 2px dashed #007bff; background: rgba(0, 123, 255, 0.1); position: relative; display: flex; align-items: center; justify-content: center; min-width: 50px; min-height: 50px;">
                            <span id="preview-label" class="text-primary fw-bold">Wprowadź wymiary</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Podgląd relatywnych wymiarów miniatur</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const widthInput = document.getElementById('thumbnailsize-width');
    const heightInput = document.getElementById('thumbnailsize-height');
    const previewBox = document.getElementById('preview-box');
    const previewLabel = document.getElementById('preview-label');
    const nameInput = document.getElementById('thumbnailsize-name');
    
    function updatePreview() {
        const width = parseInt(widthInput.value) || 0;
        const height = parseInt(heightInput.value) || 0;
        
        if (width > 0 && height > 0) {
            // Scale down the preview to fit in the container
            const maxPreviewSize = 150;
            const scale = Math.min(maxPreviewSize / width, maxPreviewSize / height);
            const previewWidth = width * scale;
            const previewHeight = height * scale;
            
            previewBox.style.width = previewWidth + 'px';
            previewBox.style.height = previewHeight + 'px';
            previewLabel.textContent = width + '×' + height + 'px';
            
            // Update name suggestion
            if (!nameInput.value) {
                if (width <= 200 && height <= 200) {
                    nameInput.placeholder = 'small';
                } else if (width <= 400 && height <= 400) {
                    nameInput.placeholder = 'medium';
                } else {
                    nameInput.placeholder = 'large';
                }
            }
        } else {
            previewBox.style.width = '50px';
            previewBox.style.height = '50px';
            previewLabel.textContent = 'Wprowadź wymiary';
            nameInput.placeholder = 'np. small, medium, large';
        }
    }
    
    // Update preview on input change
    widthInput.addEventListener('input', updatePreview);
    heightInput.addEventListener('input', updatePreview);
    
    // Initialize preview
    updatePreview();
    
    // Aspect ratio helper
    let aspectRatioLocked = false;
    let lastRatio = 1;
    
    // Add aspect ratio lock button
    const ratioButton = document.createElement('button');
    ratioButton.type = 'button';
    ratioButton.className = 'btn btn-sm btn-outline-info aspect-ratio-lock-btn';
    ratioButton.innerHTML = '<i class="fas fa-lock-open"></i>';
    ratioButton.title = 'Zablokuj proporcje';
    
    // Insert after height input
    heightInput.parentNode.appendChild(ratioButton);
    
    ratioButton.addEventListener('click', function() {
        aspectRatioLocked = !aspectRatioLocked;
        
        if (aspectRatioLocked) {
            const width = parseInt(widthInput.value) || 1;
            const height = parseInt(heightInput.value) || 1;
            lastRatio = width / height;
            ratioButton.innerHTML = '<i class="fas fa-lock"></i>';
            ratioButton.title = 'Odblokuj proporcje';
            ratioButton.classList.remove('btn-outline-info');
            ratioButton.classList.add('btn-info');
        } else {
            ratioButton.innerHTML = '<i class="fas fa-lock-open"></i>';
            ratioButton.title = 'Zablokuj proporcje';
            ratioButton.classList.remove('btn-info');
            ratioButton.classList.add('btn-outline-info');
        }
    });
    
    // Handle aspect ratio locking
    widthInput.addEventListener('input', function() {
        if (aspectRatioLocked && this.value) {
            const newHeight = Math.round(parseInt(this.value) / lastRatio);
            heightInput.value = newHeight;
            updatePreview();
        }
    });
    
    heightInput.addEventListener('input', function() {
        if (aspectRatioLocked && this.value) {
            const newWidth = Math.round(parseInt(this.value) * lastRatio);
            widthInput.value = newWidth;
            updatePreview();
        }
    });
});
</script>