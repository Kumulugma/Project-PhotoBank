<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;
use common\models\Photo;

\backend\assets\AppAsset::registerControllerAssets($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');

$this->title = 'Aktywne zdjęcia';
$this->params['breadcrumbs'][] = $this->title;

$tags = ArrayHelper::map(Tag::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$categories = ArrayHelper::map(Category::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
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

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => 'Wyświetlono <b>{begin}-{end}</b> z <b>{totalCount}</b> wpisów',
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
                    $thumbnailUrl = $model->getListThumbnail();
                    
                    if ($thumbnailUrl) {
                        return Html::img($thumbnailUrl, [
                            'class' => 'img-thumbnail',
                            'style' => 'max-width: 80px; max-height: 80px; object-fit: cover;',
                            'alt' => $model->title,
                        ]);
                    } else {
                        return '<div class="text-center p-2" style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>';
                    }
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
                'label' => 'Stock/AI',
                'format' => 'raw',
                'value' => function ($model) {
                    $badges = [];
                    
                    if ($model->isAiGenerated()) {
                        $badges[] = '<span class="badge bg-warning text-dark me-1" title="Wygenerowane przez AI"><i class="fas fa-robot me-1"></i>AI</span>';
                    }
                    
                    if ($model->isUploadedToShutterstock()) {
                        $badges[] = '<span class="badge bg-success me-1" title="Shutterstock"><i class="fas fa-camera me-1"></i>S</span>';
                    }
                    
                    if ($model->isUploadedToAdobeStock()) {
                        $badges[] = '<span class="badge bg-primary me-1" title="Adobe Stock"><i class="fab fa-adobe me-1"></i>A</span>';
                    }
                    
                    if ($model->isUsedInPrivateProject()) {
                        $badges[] = '<span class="badge bg-info me-1" title="Prywatny projekt"><i class="fas fa-briefcase me-1"></i>P</span>';
                    }
                    
                    if (empty($badges)) {
                        return '<span class="text-muted">-</span>';
                    }
                    
                    return '<div class="d-flex flex-wrap">' . implode('', $badges) . '</div>';
                },
                'filter' => Html::activeDropDownList($searchModel, 'stock_filter', [
                    'ai' => 'AI',
                    'shutterstock' => 'Shutterstock',
                    'adobe' => 'Adobe Stock',
                    'private' => 'Prywatny projekt',
                    'stock_any' => 'Dowolny stock',
                    'unused' => 'Nieużywane'
                ], [
                    'class' => 'form-select',
                    'prompt' => 'Wszystkie'
                ]),
                'headerOptions' => ['style' => 'width: 120px;'],
                'contentOptions' => ['class' => 'text-center'],
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
                'class' => 'yii\grid\ActionColumn',
                'template' => '<div class="btn-group-actions" role="group">{view}{update}{delete}</div>',
                    'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary me-1',
                            'title' => 'Zobacz',
                            'data-pjax' => 0,
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-secondary me-1',
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
                'headerOptions' => ['style' => 'width: 140px;'],
                'contentOptions' => ['class' => 'text-nowrap'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <!-- Batch Update Modal -->
    <div class="modal fade" id="batchUpdateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aktualizuj zaznaczone zdjęcia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <?php
                $form = \yii\bootstrap5\ActiveForm::begin([
                    'id' => 'batch-update-form',
                    'action' => ['batch-update'],
                ]);
                ?>
                <div class="modal-body">
                    <input type="hidden" name="ids" id="batch-update-photo-ids">

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Wybrane pola zostaną zaktualizowane dla wszystkich zaznaczonych zdjęć. 
                        Puste pola pozostaną bez zmian.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Widoczność</label>
                                <?= Html::dropDownList('is_public', '', [
                                    0 => 'Prywatne',
                                    1 => 'Publiczne'
                                ], [
                                    'class' => 'form-select',
                                    'prompt' => 'Bez zmian'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Seria</label>
                                <?= Html::dropDownList('series', '', array_combine(Photo::getAllSeries(), Photo::getAllSeries()), [
                                    'class' => 'form-select',
                                    'prompt' => 'Bez zmian'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Platforms -->
                    <h6 class="mb-3"><i class="fas fa-store me-2"></i>Platformy stockowe</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="uploaded_to_shutterstock" value="1" id="batch-shutterstock">
                                <label class="form-check-label" for="batch-shutterstock">
                                    <i class="fas fa-camera me-1"></i>Shutterstock
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="uploaded_to_adobe_stock" value="1" id="batch-adobe">
                                <label class="form-check-label" for="batch-adobe">
                                    <i class="fab fa-adobe me-1"></i>Adobe Stock
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="used_in_private_project" value="1" id="batch-private">
                                <label class="form-check-label" for="batch-private">
                                    <i class="fas fa-briefcase me-1"></i>Prywatny projekt
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- AI Section -->
                    <h6 class="mb-3"><i class="fas fa-robot me-2"></i>AI</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_ai_generated" value="1" id="batch-ai">
                            <label class="form-check-label" for="batch-ai">
                                <i class="fas fa-magic me-1"></i>Oznacz jako wygenerowane przez AI
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategorie</label>
                        <?= Html::dropDownList('categories', [], $categories, [
                            'class' => 'form-select',
                            'multiple' => true,
                            'id' => 'batch-categories'
                        ]) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tagi</label>
                        <?= Html::dropDownList('tags', [], $tags, [
                            'class' => 'form-select',
                            'multiple' => true,
                            'id' => 'batch-tags'
                        ]) ?>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="replace" value="1" id="batch-replace">
                        <label class="form-check-label" for="batch-replace">
                            Zastąp istniejące tagi i kategorie
                        </label>
                        <div class="form-text">Jeśli nie zaznaczone, nowe tagi i kategorie zostaną dodane do istniejących</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="button" class="btn btn-primary" id="batch-update-submit">
                        <i class="fas fa-save me-1"></i>Aktualizuj zdjęcia
                    </button>
                </div>
                <?php \yii\bootstrap5\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>