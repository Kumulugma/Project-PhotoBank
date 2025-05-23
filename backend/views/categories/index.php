<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
\backend\assets\AppAsset::registerControllerCss($this, 'categories');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Kategorie';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj kategorię', ['create'], [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'categories-grid-pjax']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width: 80px;'],
                'contentOptions' => ['class' => 'fw-bold'],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->name), ['view', 'id' => $model->id], [
                        'class' => 'fw-bold text-decoration-none'
                    ]);
                },
            ],
            [
                'attribute' => 'slug',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<code>' . Html::encode($model->slug) . '</code>';
                },
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'value' => function ($model) {
                    if (empty($model->description)) {
                        return '<span class="text-muted">Brak opisu</span>';
                    }
                    return Yii::$app->formatter->asText(
                        mb_strlen($model->description) > 100 
                            ? mb_substr($model->description, 0, 100) . '...' 
                            : $model->description
                    );
                },
                'filter' => false,
            ],
            [
                'label' => 'Liczba zdjęć',
                'format' => 'raw',
                'value' => function ($model) {
                    $count = $model->getPhotoCount();
                    if ($count == 0) {
                        return '<span class="badge bg-light text-dark">0</span>';
                    }
                    return Html::a(
                        '<span class="badge bg-primary">' . $count . '</span>',
                        ['/photos/index', 'PhotoSearch[category]' => $model->id],
                        ['title' => 'Pokaż zdjęcia w tej kategorii']
                    );
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 120px;'],
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
                        $photoCount = $model->getPhotoCount();
                        $confirmMsg = $photoCount > 0 
                            ? "Ta kategoria zawiera {$photoCount} zdjęć. Czy na pewno chcesz ją usunąć?"
                            : 'Czy na pewno chcesz usunąć tę kategorię?';
                        
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Usuń',
                            'data-confirm' => $confirmMsg,
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
    
    <div class="mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>O kategoriach
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p>Kategorie pomagają w organizacji zdjęć według tematów lub projektów.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Ułatwiają wyszukiwanie zdjęć</li>
                            <li><i class="fas fa-check text-success me-2"></i>Poprawiają organizację galerii</li>
                            <li><i class="fas fa-check text-success me-2"></i>Automatycznie generują slug URL</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            <h6><i class="fas fa-lightbulb me-2"></i>Wskazówki:</h6>
                            <ul class="mb-0">
                                <li>Używaj opisowych nazw kategorii</li>
                                <li>Dodawaj opisy dla lepszej organizacji</li>
                                <li>Regularne usuwanie nieużywanych kategorii</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>