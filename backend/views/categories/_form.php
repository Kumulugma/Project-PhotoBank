<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Category */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="category-form">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder me-2"></i>Informacje o kategorii
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
                        'placeholder' => 'Wprowadź nazwę kategorii'
                    ])->label('Nazwa kategorii') ?>

                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 6,
                        'class' => 'form-control',
                        'placeholder' => 'Wprowadź opis kategorii...'
                    ])->label('Opis')->hint('Opcjonalny opis kategorii') ?>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save me-2"></i>Utwórz kategorię' : '<i class="fas fa-save me-2"></i>Zapisz zmiany', [
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
                        <i class="fas fa-info-circle me-2"></i>O kategoriach
                    </h5>
                </div>
                <div class="card-body">
                    <p>Kategorie pomagają w organizacji zdjęć według tematów lub projektów.</p>
                    
                    <h6 class="fw-bold">Korzyści z kategorii:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Ułatwiają nawigację po galerii</li>
                        <li><i class="fas fa-check text-success me-2"></i>Poprawiają organizację zdjęć</li>
                        <li><i class="fas fa-check text-success me-2"></i>Automatycznie generują URL na frontendzie</li>
                        <li><i class="fas fa-check text-success me-2"></i>Umożliwiają filtrowanie zdjęć</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-link me-2"></i>URL slug</h6>
                        <p class="mb-0">Slug URL jest automatycznie generowany z nazwy kategorii. Zostanie użyty w adresach frontend galerii.</p>
                    </div>
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
                        <h6><i class="fas fa-pencil-alt me-2"></i>Nazewnictwo</h6>
                        <ul class="mb-0">
                            <li>Używaj opisowych, jasnych nazw</li>
                            <li>Unikaj zbyt długich nazw</li>
                            <li>Zachowuj spójność w nazewnictwie</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <h6><i class="fas fa-sort-alpha-down me-2"></i>Organizacja</h6>
                        <ul class="mb-0">
                            <li>Planuj strukturę kategorii z góry</li>
                            <li>Unikaj tworzenia zbyt wielu kategorii</li>
                            <li>Regularnie przeglądaj i porządkuj kategorie</li>
                            <li>Dodawaj opisy dla lepszego zrozumienia</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="Category[description]"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
    
    // Slug preview
    const nameInput = document.querySelector('input[name="Category[name]"]');
    if (nameInput && !document.querySelector('input[name="Category[slug]"]')) {
        // Create slug preview element
        const slugPreview = document.createElement('div');
        slugPreview.className = 'form-text';
        slugPreview.innerHTML = '<strong>URL slug:</strong> <code id="slug-preview">/category/...</code>';
        nameInput.parentNode.appendChild(slugPreview);
        
        // Update slug preview on name change
        nameInput.addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-') // Replace multiple hyphens with single
                .trim('-'); // Remove leading/trailing hyphens
            
            document.getElementById('slug-preview').textContent = 
                slug ? `/category/${slug}` : '/category/...';
        });
    }
});
</script>