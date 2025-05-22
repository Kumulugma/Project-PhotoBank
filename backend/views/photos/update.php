<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Tag;
use common\models\Category;
use common\models\Photo;

/* @var $this yii\web\View */
/* @var $model common\models\Photo */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $allTags array */
/* @var $allCategories array */
/* @var $selectedTags array */
/* @var $selectedCategories array */

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

// Register Select2 assets
\backend\assets\Select2Asset::register($this);
?>
<div class="photo-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?=
            Html::a('<i class="fas fa-eye me-2"></i>Zobacz zdjęcie', ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-info'
            ])
            ?>
            <?=
            Html::a('<i class="fas fa-list me-2"></i>Lista zdjęć', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ])
            ?>
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

                    <?=
                    $form->field($model, 'title')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control',
                        'required' => true,
                    ])->label('Tytuł zdjęcia')
                    ?>

                    <?=
                    $form->field($model, 'description')->textarea([
                        'rows' => 6,
                        'class' => 'form-control',
                        'placeholder' => 'Wprowadź opis zdjęcia...'
                    ])->label('Opis')
                    ?>

<?=
$form->field($model, 'status')->dropDownList($statusOptions, [
    'class' => 'form-select'
])->label('Status')
?>

                    <div class="mb-3">
                        <div class="form-check">
<?=
Html::activeCheckbox($model, 'is_public', [
    'class' => 'form-check-input',
    'id' => 'photo-is-public'
])
?>
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
                        <?=
                        Html::dropDownList('tags', $selectedTags, ArrayHelper::map($allTags, 'id', 'name'), [
                            'class' => 'form-select select2-tags',
                            'multiple' => true,
                            'id' => 'photo-tags',
                        ])
                        ?>
                        <div class="form-text">Wybierz lub wpisz aby utworzyć nowe tagi</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas folder me-1"></i>Kategorie
                        </label>
                        <?=
                        Html::dropDownList('categories', $selectedCategories, ArrayHelper::map($allCategories, 'id', 'name'), [
                            'class' => 'form-select select2-categories',
                            'multiple' => true,
                            'id' => 'photo-categories',
                        ])
                        ?>
                        <div class="form-text">Wybierz kategorie dla tego zdjęcia</div>
                    </div>

                        <?=
                        $form->field($model, 'series')->textInput([
                            'maxlength' => true,
                            'class' => 'form-control',
                            'placeholder' => 'np. K01, K03, K05',
                            'list' => 'series-datalist'
                        ])->label('<i class="fas fa-layer-group me-1"></i>Seria')
                        ?>

                    <datalist id="series-datalist">
<?php foreach (Photo::getAllSeries() as $series): ?>
                            <option value="<?= Html::encode($series) ?>">
<?php endforeach; ?>
                    </datalist>

                    <div class="d-flex gap-2">
                    <?=
                    Html::submitButton('<i class="fas fa-save me-2"></i>Zapisz zmiany', [
                        'class' => 'btn btn-success'
                    ])
                    ?>
                    <?=
                    Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['view', 'id' => $model->id], [
                        'class' => 'btn btn-secondary'
                    ])
                    ?>
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
                                <th style="width: 40%;">Nazwa pliku:</th>
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

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>Operacje AI
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
                                <i class="fas fa-file-alt me-1"></i>Generuj opis
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-magic me-2"></i>Analizuj z AI
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
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Select2 for tags with tagging support
        $('#photo-tags').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            placeholder: 'Wybierz lub wpisz tagi',
            allowClear: true,
            createTag: function (params) {
                const term = params.term.trim();

                if (term === '') {
                    return null;
                }

                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            templateResult: function (data) {
                if (data.newTag) {
                    return $('<span><i class="fas fa-plus me-1"></i>Dodaj: <strong>' + data.text + '</strong></span>');
                }
                return data.text;
            }
        });

        // Initialize Select2 for categories
        $('#photo-categories').select2({
            placeholder: 'Wybierz kategorie',
            allowClear: true
        });

        // Handle AI analysis form submission
        document.querySelector('.ai-analyze-form').addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analizowanie...';

            // Re-enable button after a delay
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showToast('Zadanie analizy AI zostało dodane do kolejki', 'info');
            }, 2000);
        });

        // Auto-resize textarea
        const textarea = document.querySelector('textarea[name="Photo[description]"]');
        if (textarea) {
            textarea.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        }
    });
</script>

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        color: white;
        border: none;
        border-radius: 0.25rem;
        margin: 2px;
        padding: 2px 8px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffdddd;
    }

    .table th {
        font-weight: 600;
        color: #495057;
    }

    .img-fluid:hover {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
</style>