<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;

$this->title = 'Aktywne zdjęcia';
$this->params['breadcrumbs'][] = $this->title;

$tags = ArrayHelper::map(Tag::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$categories = ArrayHelper::map(Category::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

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

    <!-- Quick Search Box -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Szybkie wyszukiwanie po kodzie</label>
                    <div class="input-group">
                        <input type="text" id="quick-search-code" class="form-control" 
                               placeholder="Wpisz 12-cyfrowy kod..." maxlength="12"
                               style="text-transform: uppercase;">
                        <button class="btn btn-primary" type="button" id="quick-search-btn">
                            <i class="fas fa-search"></i> Znajdź
                        </button>
                    </div>
                </div>
                <div class="col-md-8">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Każde zdjęcie ma unikalny 12-cyfrowy kod. Wpisz kod aby szybko znaleźć konkretne zdjęcie.
                    </small>
                </div>
            </div>
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
                'attribute' => 'search_code',
                'label' => 'Kod',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<code class="badge bg-secondary">' . Html::encode($model->search_code) . '</code>';
                },
                'filter' => Html::activeTextInput($searchModel, 'search_code', [
                    'class' => 'form-control',
                    'placeholder' => 'Kod...',
                    'style' => 'text-transform: uppercase;'
                ]),
                'headerOptions' => ['style' => 'width: 120px;'],
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

<!-- Pozostałe modale bez zmian... -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Szybkie wyszukiwanie po kodzie
    const quickSearchInput = document.getElementById('quick-search-code');
    const quickSearchBtn = document.getElementById('quick-search-btn');
    
    if (quickSearchInput && quickSearchBtn) {
        // Automatyczne wielkie litery
        quickSearchInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Wyszukiwanie po kliknięciu przycisku
        quickSearchBtn.addEventListener('click', function() {
            const code = quickSearchInput.value.trim();
            if (code.length > 0) {
                // Ustaw wartość w filtrze tabeli i odśwież
                const filterInput = document.querySelector('input[name="PhotoSearch[search_code]"]');
                if (filterInput) {
                    filterInput.value = code;
                    // Wyślij formularz filtrowania
                    const form = filterInput.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            }
        });
        
        // Wyszukiwanie po naciśnięciu Enter
        quickSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                quickSearchBtn.click();
            }
        });
    }

    // Pozostały kod JavaScript bez zmian...
    const checkboxes = document.querySelectorAll('input[name="selection[]"]');
    const batchButtons = document.querySelectorAll('.batch-action-btn');
    const selectAll = document.querySelector('input[name="selection_all"]');
    
    function updateBatchButtons() {
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        batchButtons.forEach(btn => {
            btn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
        });
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBatchButtons();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchButtons);
    });
});
</script>