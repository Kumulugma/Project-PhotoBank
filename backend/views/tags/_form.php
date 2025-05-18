<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Tag */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="tag-form">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hashtag me-2"></i>Informacje o tagu
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
                        'placeholder' => 'Wprowadź nazwę tagu'
                    ])->label('Nazwa tagu')->hint('Nazwa tagu (bez znaku #)') ?>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save me-2"></i>Utwórz tag' : '<i class="fas fa-save me-2"></i>Zapisz zmiany', [
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
                        <i class="fas fa-info-circle me-2"></i>O tagach
                    </h5>
                </div>
                <div class="card-body">
                    <p>Tagi pomagają w kategoryzacji i wyszukiwaniu zdjęć według słów kluczowych.</p>
                    
                    <h6 class="fw-bold">Korzyści z tagów:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Ułatwiają wyszukiwanie zdjęć</li>
                        <li><i class="fas fa-check text-success me-2"></i>Pozwalają na kategoryzację krzyżową</li>
                        <li><i class="fas fa-check text-success me-2"></i>Tworzą chmurę tagów na frontendzie</li>
                        <li><i class="fas fa-check text-success me-2"></i>Automatycznie liczą popularność</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-bar-chart me-2"></i>Popularność</h6>
                        <p class="mb-0">System automatycznie śledzi liczbę użyć każdego tagu i wyświetla najpopularniejsze na frontendzie.</p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Podgląd tagu
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div id="tag-preview" class="mb-3">
                        <span class="badge bg-info fs-5" id="preview-badge">#<span id="preview-text">wprowadź-nazwę</span></span>
                    </div>
                    <small class="text-muted">Podgląd tagu w galerii</small>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Wskazówki
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-pencil-alt me-2"></i>Najlepsze praktyki</h6>
                        <ul class="mb-0">
                            <li>Używaj krótkich, opisowych słów</li>
                            <li>Unikaj spacji (używaj myślników)</li>
                            <li>Zachowaj spójność w nazewnictwie</li>
                            <li>Nie używaj znaku # w nazwie</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Uwagi</h6>
                        <ul class="mb-0">
                            <li>Tag musi mieć unikalną nazwę</li>
                            <li>System sprawdza duplikaty (bez uwzględniania wielkości liter)</li>
                            <li>Popularność jest liczona automatycznie</li>
                            <li>Nieużywane tagi można bezpiecznie usuwać</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="Tag[name]"]');
    const previewText = document.getElementById('preview-text');
    
    if (nameInput && previewText) {
        // Update preview on input
        nameInput.addEventListener('input', function() {
            let tagName = this.value.trim();
            
            if (tagName) {
                // Convert to lowercase and replace spaces with hyphens
                tagName = tagName
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                    .replace(/\s+/g, '-') // Replace spaces with hyphens
                    .replace(/-+/g, '-'); // Replace multiple hyphens with single
                
                previewText.textContent = tagName;
            } else {
                previewText.textContent = 'wprowadź-nazwę';
            }
        });
        
        // Initialize preview with current value
        if (nameInput.value) {
            nameInput.dispatchEvent(new Event('input'));
        }
        
        // Validate tag name format
        nameInput.addEventListener('blur', function() {
            let value = this.value.trim();
            if (value) {
                // Clean up the value
                value = value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
                
                this.value = value;
                previewText.textContent = value || 'wprowadź-nazwę';
            }
        });
    }
});
</script>