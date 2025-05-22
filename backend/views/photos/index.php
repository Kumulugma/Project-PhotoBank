<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;
use common\models\Photo;

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
            <?=
            Html::a('<i class="fas fa-upload me-2"></i>Prześlij zdjęcia', ['upload'], [
                'class' => 'btn btn-success'
            ])
            ?>
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
                               placeholder="Wpisz kod zdjęcia..." maxlength="12"
                               style="text-transform: uppercase;" autocomplete="off">
                        <button class="btn btn-primary" type="button" id="quick-search-btn">
                            <i class="fas fa-search"></i> Znajdź
                        </button>
                    </div>
                    <div id="search-status" class="form-text mt-1"></div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">
                        <strong>Wskazówki:</strong><br>
                        • Wpisz pełny 12-znakowy kod aby przejść do zdjęcia<br>
                        • Wpisz część kodu aby filtrować listę<br>
                        • Kody zawierają tylko cyfry i wielkie litery
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info alert-sm mb-0 py-2">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Każde zdjęcie ma unikalny kod. Znajdziesz go w szczegółach zdjęcia.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'photos-grid-pjax']); ?>

    <?=
    GridView::widget([
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
                    $badgeClass = match ($model->status) {
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
                'attribute' => 'series',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->series)) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="badge bg-info text-dark"><i class="fas fa-layer-group me-1"></i>' . Html::encode($model->series) . '</span>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'series',
                        array_combine(Photo::getAllSeries(), Photo::getAllSeries()), [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie serie'
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
                'label' => 'Copyright',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->hasCopyrightInfo()) {
                        $copyrightInfo = $model->getCopyrightInfo();
                        $tooltip = '';
                        if (isset($copyrightInfo['copyright'])) {
                            $tooltip .= 'Copyright: ' . Html::encode($copyrightInfo['copyright']) . "\n";
                        }
                        if (isset($copyrightInfo['artist'])) {
                            $tooltip .= 'Autor: ' . Html::encode($copyrightInfo['artist']);
                        }
                        
                        return '<span class="badge bg-danger" title="' . Html::encode($tooltip) . '">
                                    <i class="fas fa-copyright me-1"></i>©
                                </span>';
                    }
                    return '<span class="text-muted">-</span>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'has_copyright', [
                    1 => 'Z prawami autorskimi',
                    0 => 'Bez praw autorskich'
                ], [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie'
                ]),
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'text-center'],
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
    ]);
    ?>

<?php Pjax::end(); ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Szybkie wyszukiwanie po kodzie
        const quickSearchInput = document.getElementById('quick-search-code');
        const quickSearchBtn = document.getElementById('quick-search-btn');
        const searchStatus = document.getElementById('search-status');

        if (quickSearchInput && quickSearchBtn) {
            // Automatyczne wielkie litery
            quickSearchInput.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
            });

            // Funkcja wyszukiwania
            function performSearch() {
                const code = quickSearchInput.value.trim();
                if (code.length > 0) {
                    // Jeśli kod ma 12 znaków, spróbuj znaleźć konkretne zdjęcie
                    if (code.length === 12) {
                        searchStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Szukam zdjęcia...';
                        searchStatus.className = 'form-text mt-1 text-primary';

                        // Sprawdź czy zdjęcie o tym kodzie istnieje
                        window.location.href = '/photos/find-by-code?code=' + encodeURIComponent(code);
                    } else {
                        // Dla niepełnych kodów użyj filtra tabeli
                        filterByCode(code);
                    }
                }
            }

            // Funkcja filtrowania tabeli
            function filterByCode(code) {
                searchStatus.innerHTML = '<i class="fas fa-filter"></i> Filtruję wyniki...';
                searchStatus.className = 'form-text mt-1 text-info';

                const filterInput = document.querySelector('input[name="PhotoSearch[search_code]"]');
                if (filterInput) {
                    filterInput.value = code;

                    // Wyczyść inne filtry dla lepszych wyników wyszukiwania po kodzie
                    const titleFilter = document.querySelector('input[name="PhotoSearch[title]"]');
                    if (titleFilter)
                        titleFilter.value = '';

                    // Wyślij formularz filtrowania
                    const form = filterInput.closest('form');
                    if (form) {
                        form.submit();
                    } else {
                        // Jeśli nie ma formularza, odśwież stronę z parametrem
                        const currentUrl = new URL(window.location);
                        currentUrl.searchParams.set('PhotoSearch[search_code]', code);
                        window.location.href = currentUrl.toString();
                    }
                }
            }

            // Wyszukiwanie po kliknięciu przycisku
            quickSearchBtn.addEventListener('click', performSearch);

            // Wyszukiwanie po naciśnięciu Enter
            quickSearchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Dodaj wskazówkę wizualną
            quickSearchInput.addEventListener('keyup', function () {
                const code = this.value.trim();
                if (code.length === 12) {
                    this.classList.add('border-success');
                    this.classList.remove('border-warning');
                    quickSearchBtn.innerHTML = '<i class="fas fa-eye"></i> Zobacz';
                    quickSearchBtn.className = 'btn btn-success';
                    searchStatus.innerHTML = '<i class="fas fa-check-circle"></i> Kod kompletny - kliknij aby przejść do zdjęcia';
                    searchStatus.className = 'form-text mt-1 text-success';
                } else if (code.length > 0) {
                    this.classList.add('border-warning');
                    this.classList.remove('border-success');
                    quickSearchBtn.innerHTML = '<i class="fas fa-search"></i> Filtruj';
                    quickSearchBtn.className = 'btn btn-warning';
                    searchStatus.innerHTML = '<i class="fas fa-info-circle"></i> Kod niekompletny - będzie użyty jako filtr';
                    searchStatus.className = 'form-text mt-1 text-warning';
                } else {
                    this.classList.remove('border-success', 'border-warning');
                    quickSearchBtn.innerHTML = '<i class="fas fa-search"></i> Znajdź';
                    quickSearchBtn.className = 'btn btn-primary';
                    searchStatus.innerHTML = '';
                    searchStatus.className = 'form-text mt-1';
                }
            });
        }

        // Batch operations - obsługa zaznaczania zdjęć
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
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBatchButtons();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButtons);
        });
    });
</script>

<style>
    .alert-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    #quick-search-code.border-success {
        border-color: #198754 !important;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }

    #quick-search-code.border-warning {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 0.85em;
    }

    .img-thumbnail {
        transition: transform 0.2s ease;
    }

    .img-thumbnail:hover {
        transform: scale(1.1);
    }
</style>