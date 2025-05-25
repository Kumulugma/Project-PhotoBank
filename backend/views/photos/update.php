<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Tag;
use common\models\Category;
use common\models\Photo;

\backend\assets\AppAsset::registerControllerAssets($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\Select2Asset::register($this);

$this->title = 'Edytuj zdjęcie: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Wszystkie zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edytuj';

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'W kolejce',
    \common\models\Photo::STATUS_ACTIVE => 'Aktywne',
    \common\models\Photo::STATUS_DELETED => 'Usunięte',
];
?>
<div class="photo-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye me-2"></i>Zobacz zdjęcie', ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-info'
            ]) ?>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista zdjęć', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Szczegóły zdjęcia
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $form = ActiveForm::begin([
                        'options' => ['class' => 'needs-validation'],
                    ]);
                    ?>

                    <?= $form->field($model, 'title')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control',
                        'required' => true,
                    ])->label('Tytuł zdjęcia') ?>

                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 6,
                        'class' => 'form-control',
                        'placeholder' => 'Wprowadź opis zdjęcia...'
                    ])->label('Opis') ?>

                    <?= $form->field($model, 'english_description')->textarea([
                        'rows' => 6,
                        'class' => 'form-control',
                        'placeholder' => 'Enter photo description in English...'
                    ])->label('Opis w języku angielskim') ?>

                    <?= $form->field($model, 'status')->dropDownList($statusOptions, [
                        'class' => 'form-select'
                    ])->label('Status') ?>

                    <div class="mb-3">
                        <div class="form-check">
                            <?= Html::activeCheckbox($model, 'is_public', [
                                'class' => 'form-check-input',
                                'id' => 'photo-is-public'
                            ]) ?>
                            <label class="form-check-label" for="photo-is-public">
                                <i class="fas fa-eye me-1"></i>Zdjęcie publiczne
                            </label>
                            <div class="form-text">Publiczne zdjęcia są widoczne dla wszystkich użytkowników</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-tags me-1"></i>Tagi
                        </label>
                        <?= Html::dropDownList('tags', $selectedTags, ArrayHelper::map($allTags, 'id', 'name'), [
                            'class' => 'form-select select2-tags',
                            'multiple' => true,
                            'id' => 'photo-tags',
                        ]) ?>
                        <div class="form-text">Wybierz lub wpisz aby utworzyć nowe tagi</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-folder me-1"></i>Kategorie
                        </label>
                        <?= Html::dropDownList('categories', $selectedCategories, ArrayHelper::map($allCategories, 'id', 'name'), [
                            'class' => 'form-select select2-categories',
                            'multiple' => true,
                            'id' => 'photo-categories',
                        ]) ?>
                        <div class="form-text">Wybierz kategorie dla tego zdjęcia</div>
                    </div>

                    <?= $form->field($model, 'series')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control',
                        'placeholder' => 'np. K01, K03, K05',
                        'list' => 'series-datalist'
                    ])->label('<i class="fas fa-layer-group me-1"></i>Seria') ?>

                    <datalist id="series-datalist">
                        <?php foreach (Photo::getAllSeries() as $series): ?>
                            <option value="<?= Html::encode($series) ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <!-- Stock Platforms Section -->
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-store me-2"></i>Platformy stockowe</h6>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <?= Html::activeCheckbox($model, 'uploaded_to_shutterstock', [
                                    'class' => 'form-check-input',
                                    'id' => 'uploaded-to-shutterstock'
                                ]) ?>
                                <label class="form-check-label" for="uploaded-to-shutterstock">
                                    <i class="fas fa-camera me-1"></i>Shutterstock
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <?= Html::activeCheckbox($model, 'uploaded_to_adobe_stock', [
                                    'class' => 'form-check-input',
                                    'id' => 'uploaded-to-adobe-stock'
                                ]) ?>
                                <label class="form-check-label" for="uploaded-to-adobe-stock">
                                    <i class="fab fa-adobe me-1"></i>Adobe Stock
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <?= Html::activeCheckbox($model, 'used_in_private_project', [
                                    'class' => 'form-check-input',
                                    'id' => 'used-in-private-project'
                                ]) ?>
                                <label class="form-check-label" for="used-in-private-project">
                                    <i class="fas fa-briefcase me-1"></i>Prywatny projekt
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">Zaznacz platformy, na które zostało przesłane zdjęcie lub w których zostało użyte</div>

                    <!-- AI Section -->
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-robot me-2"></i>Sztuczna inteligencja</h6>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <?= Html::activeCheckbox($model, 'is_ai_generated', [
                                'class' => 'form-check-input',
                                'id' => 'is-ai-generated'
                            ]) ?>
                            <label class="form-check-label" for="is-ai-generated">
                                <i class="fas fa-magic me-1"></i>Zdjęcie wygenerowane przez AI
                            </label>
                        </div>
                        <div class="form-text">Zaznacz jeśli zdjęcie zostało utworzone za pomocą sztucznej inteligencji</div>
                    </div>

                    <div id="ai-fields" style="display: <?= $model->is_ai_generated ? 'block' : 'none' ?>;">
                        <?= $form->field($model, 'ai_prompt')->textarea([
                            'rows' => 3,
                            'class' => 'form-control',
                            'placeholder' => 'Wprowadź prompt użyty do wygenerowania obrazu...'
                        ])->label('<i class="fas fa-terminal me-1"></i>Prompt AI') ?>

                        <?= $form->field($model, 'ai_generator_url')->textInput([
                            'maxlength' => true,
                            'class' => 'form-control',
                            'placeholder' => 'https://...'
                        ])->label('<i class="fas fa-link me-1"></i>Link do generatora') ?>
                    </div>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i>Zapisz zmiany', [
                            'class' => 'btn btn-success'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>Podgląd zdjęcia
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php
                    $previewUrl = $model->getPreviewThumbnail();

                    if ($previewUrl) {
                        echo Html::img($previewUrl, [
                            'class' => 'img-fluid rounded shadow',
                            'alt' => $model->title,
                            'style' => 'max-height: 300px;'
                        ]);
                    } else {
                        echo '<div class="text-center p-4">';
                        echo '<i class="fas fa-image fa-4x text-muted mb-3"></i>';
                        echo '<p class="text-muted">Podgląd niedostępny</p>';
                        if (\common\helpers\PathHelper::isFrontendMode()) {
                            echo '<small class="text-info">Tryb frontend - sprawdź ścieżki do miniatur</small>';
                        } else {
                            echo '<small class="text-warning">Miniatury nie zostały wygenerowane</small>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informacje o pliku
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 40%;">Kod:</th>
                                <td><code><?= Html::encode($model->search_code) ?></code></td>
                            </tr>
                            <tr>
                                <th>Nazwa pliku:</th>
                                <td><code><?= Html::encode($model->file_name) ?></code></td>
                            </tr>
                            <tr>
                                <th>Rozmiar:</th>
                                <td><?= Yii::$app->formatter->asShortSize($model->file_size, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Wymiary:</th>
                                <td><?= $model->width ?> × <?= $model->height ?> px</td>
                            </tr>
                            <tr>
                                <th>Typ MIME:</th>
                                <td><span class="badge bg-info"><?= $model->mime_type ?></span></td>
                            </tr>
                            <tr>
                                <th>Przesłane:</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php
                                    $statusBadge = match ($model->status) {
                                        \common\models\Photo::STATUS_QUEUE => 'bg-warning text-dark',
                                        \common\models\Photo::STATUS_ACTIVE => 'bg-success',
                                        \common\models\Photo::STATUS_DELETED => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusBadge ?>"><?= $statusOptions[$model->status] ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stock and AI Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Status wykorzystania
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($model->isUploadedToAnyStock() || $model->isUsedInPrivateProject()): ?>
                        <h6 class="mb-2">Platformy stockowe:</h6>
                        <div class="mb-3">
                            <?php if ($model->isUploadedToShutterstock()): ?>
                                <span class="badge bg-success me-1"><i class="fas fa-camera me-1"></i>Shutterstock</span>
                            <?php endif; ?>
                            <?php if ($model->isUploadedToAdobeStock()): ?>
                                <span class="badge bg-primary me-1"><i class="fab fa-adobe me-1"></i>Adobe Stock</span>
                            <?php endif; ?>
                            <?php if ($model->isUsedInPrivateProject()): ?>
                                <span class="badge bg-info me-1"><i class="fas fa-briefcase me-1"></i>Prywatny projekt</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-3">Zdjęcie nie zostało jeszcze wykorzystane</p>
                    <?php endif; ?>

                    <?php if ($model->isAiGenerated()): ?>
                        <h6 class="mb-2">Informacje AI:</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-robot me-2"></i>
                            <strong>Zdjęcie wygenerowane przez AI</strong>
                            <?php if ($model->hasAiPrompt()): ?>
                                <hr>
                                <small><strong>Prompt:</strong> <?= Html::encode($model->ai_prompt) ?></small>
                            <?php endif; ?>
                            <?php if ($model->hasAiGeneratorUrl()): ?>
                                <hr>
                                <small><strong>Generator:</strong> 
                                    <a href="<?= Html::encode($model->ai_generator_url) ?>" target="_blank" class="alert-link">
                                        Zobacz w generatorze <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>Operacje AI i EXIF
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Użyj sztucznej inteligencji do automatycznego analizowania zdjęcia:</p>

                    <?php
                    $aiForm = ActiveForm::begin([
                        'action' => ['ai/analyze-photo', 'id' => $model->id],
                        'options' => ['class' => 'ai-analyze-form'],
                    ]);
                    ?>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="analyze_tags" value="1" checked id="analyze-tags">
                            <label class="form-check-label" for="analyze-tags">
                                <i class="fas fa-tags me-1"></i>Generuj tagi
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="analyze_description" value="1" checked id="analyze-description">
                            <label class="form-check-label" for="analyze-description">
                                <i class="fas fa-file-alt me-1"></i>Generuj opis polski
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="analyze_english_description" value="1" checked id="analyze-english-description">
                            <label class="form-check-label" for="analyze-english-description">
                                <i class="fas fa-globe me-1"></i>Generuj opis angielski
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-magic me-2"></i>Analizuj z AI
                        </button>
                        <button type="button" class="btn btn-outline-warning" id="set-exif-btn" data-photo-id="<?= $model->id ?>">
                            <i class="fas fa-camera me-2"></i>Ustaw dane EXIF
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="alert alert-info mt-3 mb-0">
                        <small><i class="fas fa-info-circle me-1"></i>Analiza AI jest wykonywana w tle. Wyniki pojawią się w ciągu kilku minut.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for tags and categories
    if (typeof $.fn.select2 !== 'undefined') {
        $('#photo-tags').select2({
            placeholder: 'Wybierz lub wpisz tagi...',
            tags: true,
            allowClear: true,
            width: '100%'
        });

        $('#photo-categories').select2({
            placeholder: 'Wybierz kategorie...',
            allowClear: true,
            width: '100%'
        });
    }

    // Toggle AI fields visibility
    const aiCheckbox = document.getElementById('is-ai-generated');
    const aiFields = document.getElementById('ai-fields');
    
    if (aiCheckbox && aiFields) {
        aiCheckbox.addEventListener('change', function() {
            aiFields.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Obsługa przycisku ustawiania EXIF
    const setExifBtn = document.getElementById('set-exif-btn');
    if (setExifBtn) {
        setExifBtn.addEventListener('click', function() {
            const photoId = this.getAttribute('data-photo-id');
            const button = this;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ustawianie...';
            
            fetch('<?= \yii\helpers\Url::to(['/exif/set-artist']) ?>?id=' + photoId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Wystąpił błąd podczas ustawiania danych EXIF.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-camera me-2"></i>Ustaw dane EXIF';
            });
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.photo-update');
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>