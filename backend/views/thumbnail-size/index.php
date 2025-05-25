<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
\backend\assets\AppAsset::registerComponentCss($this, 'modals');

$this->title = 'Rozmiary miniatur';
$this->params['breadcrumbs'][] = $this->title;

$totalSizes = $dataProvider->getTotalCount();
?>

<style>
.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.page-subtitle {
    color: #6c757d;
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.actions-toolbar {
    margin-bottom: 1.5rem;
    display: flex;
    gap: 0.5rem;
}

.btn-gradient {
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    font-weight: 500;
}

.btn-gradient::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-gradient:hover::before {
    left: 100%;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.btn-success.btn-gradient {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.btn-success.btn-gradient:hover {
    background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
}

.btn-warning.btn-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.btn-warning.btn-gradient:hover {
    background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
}

.table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.grid-view {
    margin: 0;
}

.grid-view table {
    margin: 0;
    border: none;
}

.grid-view th {
    background: #f8f9fa;
    color: #495057;
    border: none;
    padding: 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
}

.grid-view td {
    padding: 1rem;
    border: none;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
    color: #495057;
}

.grid-view tbody tr {
    transition: all 0.2s ease;
}

.grid-view tbody tr:hover {
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.03) 0%, rgba(0, 123, 255, 0.01) 100%);
}

.grid-view tbody tr:last-child td {
    border-bottom: none;
}

.size-name-code {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9rem;
    color: #e83e8c;
}

.size-dimensions {
    font-weight: 600;
    color: #495057;
}

.badge-modern {
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.badge-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
}

.badge-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.btn-group-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.8rem;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.btn-action.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.btn-action.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-action.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
    color: white;
}

.filters {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.filters input, .filters select {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.9rem;
}

.filters input:focus, .filters select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

.modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-bottom: none;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
}

.modal-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
}
</style>

<div class="thumbnail-size-index">
    <div class="page-header">
        <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
        <div class="page-subtitle">Wyświetlono 1-<?= min($totalSizes, $dataProvider->pagination->pageSize) ?> z <?= $totalSizes ?> kategorii</div>
    </div>

    <div class="actions-toolbar">
        <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj rozmiar', ['create'], [
            'class' => 'btn btn-success btn-gradient'
        ]) ?>
        <?= Html::a('<i class="fas fa-sync me-2"></i>Regeneruj wszystkie', '#', [
            'class' => 'btn btn-warning btn-gradient',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#regenerateModal',
        ]) ?>
    </div>

    <div class="table-container">
        <?php Pjax::begin(); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{items}{pager}',
            'tableOptions' => ['class' => 'table table-hover mb-0'],
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'header' => '#',
                    'headerOptions' => ['style' => 'width: 60px;'],
                ],
                [
                    'attribute' => 'name',
                    'header' => 'Nazwa',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a('<span class="size-name-code">' . Html::encode($model->name) . '</span>', 
                            ['view', 'id' => $model->id], 
                            ['style' => 'text-decoration: none;']
                        );
                    },
                ],
                [
                    'attribute' => 'slug',
                    'header' => 'Slug',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a('<span class="size-name-code">' . Html::encode($model->name) . '</span>', 
                            ['view', 'id' => $model->id], 
                            ['style' => 'text-decoration: none;']
                        );
                    },
                    'visible' => false, // ukryj jeśli nie masz pola slug
                ],
                [
                    'label' => 'Opis',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span class="size-dimensions">' . $model->width . ' × ' . $model->height . ' px</span>';
                    },
                    'headerOptions' => ['style' => 'width: 150px;'],
                ],
                [
                    'label' => 'Zdjęcia',
                    'format' => 'raw',
                    'value' => function ($model) {
                        // Liczba zdjęć z tym rozmiarem
                        $thumbnailsDir = Yii::getAlias('@webroot/uploads/thumbnails/');
                        $pattern = $thumbnailsDir . $model->name . '_*';
                        $thumbnailFiles = glob($pattern);
                        $count = count($thumbnailFiles);
                        
                        if ($count > 0) {
                            return '<span class="badge-modern badge-success">' . $count . '</span>';
                        } else {
                            return '<span class="badge-modern badge-secondary">0</span>';
                        }
                    },
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                [
                    'attribute' => 'created_at',
                    'header' => 'Data utworzenia',
                    'value' => function ($model) {
                        return date('d.m.Y', $model->created_at);
                    },
                    'filter' => Html::input('date', Html::getInputName($searchModel, 'created_at'), 
                        $searchModel->created_at ? date('Y-m-d', strtotime($searchModel->created_at)) : '', 
                        ['class' => 'form-control']
                    ),
                    'headerOptions' => ['style' => 'width: 150px;'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '',
                    'template' => '<div class="btn-group-actions">{view}{update}{delete}</div>',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn-action btn-info',
                                'title' => 'Zobacz',
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                'class' => 'btn-action btn-primary',
                                'title' => 'Edytuj',
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-trash"></i>', $url, [
                                'class' => 'btn-action btn-danger',
                                'title' => 'Usuń',
                                'data-confirm' => 'Czy na pewno chcesz usunąć ten rozmiar miniatur?',
                                'data-method' => 'post',
                            ]);
                        },
                    ],
                    'headerOptions' => ['style' => 'width: 120px;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>
</div>

<div class="modal fade" id="regenerateModal" tabindex="-1" aria-labelledby="regenerateModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="regenerateModalLabel">
                    <i class="fas fa-sync"></i>
                    Regeneruj miniatury
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Zamknij"></button>
            </div>
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'action' => ['regenerate'],
                'method' => 'post',
            ]); ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">ID zdjęcia (opcjonalne):</label>
                    <input type="number" class="form-control" name="photo_id" placeholder="Zostaw puste aby regenerować wszystkie">
                    <div class="form-text">Podaj konkretne ID zdjęcia lub zostaw puste aby regenerować wszystkie zdjęcia</div>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Uwaga!</h6>
                    <p class="mb-2"><strong>Ostrzeżenie:</strong> Ta operacja może być bardzo zasobożerna i może zająć dużo czasu dla dużych galerii.</p>
                    <p class="mb-0">Proces będzie wykonywany w tle. Możesz sprawdzić status w kolejce zadań.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-warning btn-gradient">
                    <i class="fas fa-sync me-2"></i>Regeneruj miniatury
                </button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>