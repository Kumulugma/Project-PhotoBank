<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
\backend\assets\AppAsset::registerControllerAssets($this, 'categories');
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

    <!-- Category Grid View -->
    <div class="category-grid">
        <?php 
        $categories = \common\models\Category::find()
            ->with(['photos' => function($query) {
                $query->andWhere(['status' => \common\models\Photo::STATUS_ACTIVE])
                      ->limit(6);
            }])
            ->all();
        
        foreach ($categories as $category): 
        ?>
            <div class="category-item category-card">
                <div class="category-header">
                    <h5 class="mb-1"><?= Html::encode($category->name) ?></h5>
                    <small class="opacity-75"><?= Html::encode($category->description) ?></small>
                </div>
                
                <div class="p-3">
                    <?php if (!empty($category->photos)): ?>
                        <div class="category-photos-grid">
                            <?php foreach ($category->photos as $photo): ?>
                                <?php 
                                $thumbnailUrl = $photo->getThumbnailUrl('small');
                                if ($thumbnailUrl): 
                                ?>
                                    <img src="<?= $thumbnailUrl ?>" 
                                         alt="<?= Html::encode($photo->title) ?>"
                                         class="img-fluid">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-image fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Brak zdjęć</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="category-stats">
                    <div>
                        <small class="text-muted">
                            <?= $category->getPhotos()->count() ?> zdjęć
                        </small>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $category->id], [
                            'class' => 'btn btn-outline-primary btn-sm',
                            'title' => 'Zobacz'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $category->id], [
                            'class' => 'btn btn-outline-secondary btn-sm',
                            'title' => 'Edytuj'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $category->id], [
                            'class' => 'btn btn-outline-danger btn-sm',
                            'title' => 'Usuń',
                            'data-confirm' => 'Czy na pewno usunąć tę kategorię?',
                            'data-method' => 'post',
                        ]) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Traditional Table View -->
    <div class="mt-5">
        <h4>Lista kategorii</h4>
        
        <?php Pjax::begin(['id' => 'categories-grid-pjax']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
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
                        if ($model->slug) {
                            return '<code class="small">' . Html::encode($model->slug) . '</code>';
                        }
                        return '<span class="text-muted">Brak slug</span>';
                    },
                    'headerOptions' => ['style' => 'width: 200px;'],
                ],
                [
                    'attribute' => 'description',
                    'format' => 'text',
                    'contentOptions' => ['class' => 'text-truncate'],
                    'headerOptions' => ['style' => 'max-width: 300px;'],
                ],
                [
                    'label' => 'Zdjęcia',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $photoCount = $model->getPhotos()->count();
                        if ($photoCount == 0) {
                            return '<span class="badge bg-light text-dark">0</span>';
                        }
                        return Html::a(
                            '<span class="badge bg-primary">' . $photoCount . '</span>',
                            ['/photos/index', 'PhotoSearch[category_id]' => $model->id],
                            ['title' => 'Pokaż zdjęcia z tej kategorii']
                        );
                    },
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 100px;'],
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
                            $photoCount = $model->getPhotos()->count();
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
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>O kategoriach
                    </h5>
                </div>
                <div class="card-body">
                    <p>Kategorie pomagają w organizacji i grupowaniu zdjęć według tematyki lub rodzaju.</p>
                    
                    <h6 class="fw-bold">Korzyści z kategorii:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Łatwiejsze przeglądanie kolekcji</li>
                        <li><i class="fas fa-check text-success me-2"></i>Hierarchiczna organizacja treści</li>
                        <li><i class="fas fa-check text-success me-2"></i>Tworzenie struktur galerii</li>
                        <li><i class="fas fa-check text-success me-2"></i>Automatyczne URL-e dla SEO</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statystyki
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $totalCategories = \common\models\Category::find()->count();
                    $categoriesWithPhotos = \common\models\Category::find()
                        ->innerJoin('photo_category', 'category.id = photo_category.category_id')
                        ->distinct()
                        ->count();
                    $emptyCategories = $totalCategories - $categoriesWithPhotos;
                    ?>
                    
                    <div class="text-center">
                        <div class="row">
                            <div class="col-4">
                                <h4 class="text-primary mb-0"><?= $totalCategories ?></h4>
                                <small class="text-muted">Łącznie</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-success mb-0"><?= $categoriesWithPhotos ?></h4>
                                <small class="text-muted">Z zdjęciami</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-warning mb-0"><?= $emptyCategories ?></h4>
                                <small class="text-muted">Puste</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>