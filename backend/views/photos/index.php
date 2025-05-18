<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PhotoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Aktywne zdjęcia';
$this->params['breadcrumbs'][] = $this->title;

// Get all tags and categories for filter dropdowns
$tags = ArrayHelper::map(Tag::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$categories = ArrayHelper::map(Category::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

// Status options
$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'W kolejce',
    \common\models\Photo::STATUS_ACTIVE => 'Aktywne',
    \common\models\Photo::STATUS_DELETED => 'Usunięte',
];
?>
<div class="photo-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-upload me-2"></i>Prześlij zdjęcia', ['upload'], [
                'class' => 'btn btn-success'
            ]) ?>
            <button type="button" class="btn btn-primary batch-action-btn" style="display: none;" 
                    data-bs-toggle="modal" data-bs-target="#batchUpdateModal">
                <i class="fas fa-edit me-2"></i>Aktualizuj zaznaczone
            </button>
            <button type="button" class="btn btn-info batch-action-btn" style="display: none;"
                    data-bs-toggle="modal" data-bs-target="#batchAnalyzeModal">
                <i class="fas fa-robot me-2"></i>Analiza AI
            </button>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'photos-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model) {
                    return ['value' => $model->id, 'name' => 'selection[]'];
                }
            ],
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'fw-bold'],
            ],
            [
                'label' => 'Miniatura',
                'format' => 'raw',
                'value' => function ($model) {
                    $thumbnailSize = \common\models\ThumbnailSize::findOne(['name' => 'small']);
                    if ($thumbnailSize) {
                        $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $thumbnailSize->name . '_' . $model->file_name);
                        return Html::img($thumbnailUrl, [
                            'class' => 'img-thumbnail',
                            'style' => 'max-width: 80px; max-height: 80px; object-fit: cover;',
                            'alt' => $model->title,
                        ]);
                    }
                    return '<span class="text-muted">Brak</span>';
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 100px;'],
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->title), ['view', 'id' => $model->id], [
                        'class' => 'fw-bold text-decoration-none'
                    ]);
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) use ($statusOptions) {
                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                    $badgeClass = match($model->status) {
                        \common\models\Photo::STATUS_QUEUE => 'bg-warning',
                        \common\models\Photo::STATUS_ACTIVE => 'bg-success',
                        \common\models\Photo::STATUS_DELETED => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'status', $statusOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie'
                ]),
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'is_public',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->is_public) {
                        return '<span class="badge bg-success"><i class="fas fa-eye me-1"></i>Publiczne</span>';
                    } else {
                        return '<span class="badge bg-secondary"><i class="fas fa-eye-slash me-1"></i>Prywatne</span>';
                    }
                },
                'filter' => Html::activeDropDownList($searchModel, 'is_public', [
                    0 => 'Prywatne',
                    1 => 'Publiczne'
                ], [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie'
                ]),
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'label' => 'Tagi',
                'format' => 'raw',
                'value' => function ($model) {
                    $tags = $model->getTags()->limit(3)->all();
                    if (empty($tags)) {
                        return '<span class="text-muted">Brak tagów</span>';
                    }
                    
                    $html = '';
                    foreach ($tags as $tag) {
                        $html .= '<span class="badge bg-info text-dark me-1">' . Html::encode($tag->name) . '</span>';
                    }
                    
                    $totalTags = $model->getTags()->count();
                    if ($totalTags > 3) {
                        $html .= '<span class="badge bg-light text-dark">+' . ($totalTags - 3) . '</span>';
                    }
                    
                    return $html;
                },
                'filter' => false,
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<span title="' . date('Y-m-d H:i:s', $model->created_at) . '">' . 
                           Yii::$app->formatter->asRelativeTime($model->created_at) . '</span>';
                },
                'filter' => Html::activeTextInput($searchModel, 'created_at', [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD'
                ]),
                'headerOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Zobacz',
                            'data-pjax' => 0,
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Edytuj',
                            'data-pjax' => 0,
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Usuń',
                            'data-confirm' => 'Czy na pewno chcesz usunąć to zdjęcie?',
                            'data-method' => 'post',
                            'data-pjax' => 0,
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width: 120px;'],
                'contentOptions' => ['class' => 'text-end'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<!-- Batch Update Modal -->
<div class="modal fade" id="batchUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aktualizuj zaznaczone zdjęcia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'id' => 'batch-update-form',
                'action' => ['batch-update'],
            ]); ?>
            <div class="modal-body">
                <input type="hidden" name="ids" id="selected-photo-ids">
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <?= Html::dropDownList('status', null, $statusOptions, [
                        'prompt' => '- Bez zmian -',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Widoczność</label>
                    <?= Html::dropDownList('is_public', null, ['0' => 'Prywatne', '1' => 'Publiczne'], [
                        'prompt' => '- Bez zmian -',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Kategorie</label>
                    <?= Html::dropDownList('categories[]', null, $categories, [
                        'class' => 'form-select',
                        'multiple' => true,
                        'size' => 5,
                    ]) ?>
                    <div class="form-text">Przytrzymaj Ctrl/Cmd aby wybrać wiele kategorii</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tagi</label>
                    <?= Html::dropDownList('tags[]', null, $tags, [
                        'class' => 'form-select',
                        'multiple' => true,
                        'size' => 5,
                    ]) ?>
                    <div class="form-text">Przytrzymaj Ctrl/Cmd aby wybrać wiele tagów</div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="replace" value="1" id="replace-check">
                        <label class="form-check-label" for="replace-check">
                            Zastąp istniejące kategorie i tagi
                        </label>
                        <div class="form-text">Bez tej opcji nowe kategorie i tagi zostaną dodane do istniejących</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-primary" id="batch-update-submit">Aktualizuj zdjęcia</button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Batch Analyze Modal -->
<div class="modal fade" id="batchAnalyzeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Analiza AI - zaznaczone zdjęcia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'id' => 'batch-analyze-form',
                'action' => ['/ai/analyze-batch'],
            ]); ?>
            <div class="modal-body">
                <input type="hidden" name="ids" id="analyze-photo-ids">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Ta akcja uruchomi analizę AI dla zaznaczonych zdjęć. Proces zostanie wykonany w tle i może zająć trochę czasu.
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="analyze_tags" value="1" id="analyze-tags" checked>
                        <label class="form-check-label" for="analyze-tags">
                            <i class="fas fa-tags me-1"></i>Generuj tagi na podstawie zawartości
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="analyze_description" value="1" id="analyze-description" checked>
                        <label class="form-check-label" for="analyze-description">
                            <i class="fas fa-file-alt me-1"></i>Generuj opisy zdjęć
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-primary" id="batch-analyze-submit">
                    <i class="fas fa-robot me-1"></i>Uruchom analizę
                </button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle batch operations
    const checkboxes = document.querySelectorAll('input[name="selection[]"]');
    const batchButtons = document.querySelectorAll('.batch-action-btn');
    const selectAll = document.querySelector('input[name="selection_all"]');
    
    function updateBatchButtons() {
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        batchButtons.forEach(btn => {
            btn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
        });
    }
    
    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBatchButtons();
        });
    }
    
    // Individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchButtons);
    });
    
    // Batch update form submission
    document.getElementById('batch-update-submit').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('selected-photo-ids').value = ids.join(',');
        document.getElementById('batch-update-form').submit();
    });
    
    // Batch analyze form submission
    document.getElementById('batch-analyze-submit').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('analyze-photo-ids').value = ids.join(',');
        document.getElementById('batch-analyze-form').submit();
    });
});
</script>