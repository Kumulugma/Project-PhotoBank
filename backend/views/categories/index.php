<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
\backend\assets\AppAsset::registerControllerCss($this, 'categories');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');

$this->title = 'Kategorie';
$this->params['breadcrumbs'][] = $this->title;

// Obliczanie statystyk
$totalCategories = \common\models\Category::find()->count();
$categoriesWithPhotos = \common\models\Category::find()
    ->innerJoin('photo_category', 'category.id = photo_category.category_id')
    ->distinct()
    ->count();
$emptyCategories = $totalCategories - $categoriesWithPhotos;
$totalPhotos = \common\models\PhotoCategory::find()->count();
?>
<div class="category-index">
    <!-- Header z tytułem i przyciskiem -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj kategorię', ['create'], [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
    </div>

    <!-- Sekcja ze statystykami -->
    <div class="category-header-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Przegląd kategorii</h4>
                <p class="text-muted mb-0">Zarządzaj kategoriami i organizuj swoje zdjęcia</p>
            </div>
            <div class="text-end">
                <i class="fas fa-chart-pie fa-2x text-primary opacity-50"></i>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number"><?= $totalCategories ?></div>
                <div class="stat-label">Łącznie</div>
            </div>
            <div class="stat-item success">
                <div class="stat-number"><?= $categoriesWithPhotos ?></div>
                <div class="stat-label">Z zdjęciami</div>
            </div>
            <div class="stat-item warning">
                <div class="stat-number"><?= $emptyCategories ?></div>
                <div class="stat-label">Puste</div>
            </div>
            <div class="stat-item info">
                <div class="stat-number"><?= $totalPhotos ?></div>
                <div class="stat-label">Przypisań</div>
            </div>
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
            $photoCount = $category->getPhotos()->count();
        ?>
            <div class="category-item category-card">
                <div class="category-header">
                    <h5 class="mb-1"><?= Html::encode($category->name) ?></h5>
                    <?php if ($category->description): ?>
                        <small class="opacity-90"><?= Html::encode($category->description) ?></small>
                    <?php else: ?>
                        <small class="opacity-75 fst-italic">Brak opisu</small>
                    <?php endif; ?>
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
                                         class="img-fluid"
                                         loading="lazy">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-photos">
                            <i class="fas fa-image fa-3x mb-2"></i>
                            <p class="mb-0">Brak zdjęć w tej kategorii</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="category-stats">
                    <div class="photo-count">
                        <i class="fas fa-images"></i>
                        <span><?= $photoCount ?> <?= $photoCount == 1 ? 'zdjęcie' : ($photoCount < 5 ? 'zdjęcia' : 'zdjęć') ?></span>
                        <?php if ($category->slug): ?>
                            <span class="text-muted ms-2">• <?= $category->slug ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $category->id], [
                            'class' => 'btn btn-outline-primary btn-sm',
                            'title' => 'Zobacz szczegóły'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $category->id], [
                            'class' => 'btn btn-outline-secondary btn-sm',
                            'title' => 'Edytuj kategorię'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $category->id], [
                            'class' => 'btn btn-outline-danger btn-sm',
                            'title' => 'Usuń kategorię',
                            'data-confirm' => $photoCount > 0 
                                ? "Ta kategoria zawiera {$photoCount} zdjęć. Czy na pewno chcesz ją usunąć?"
                                : 'Czy na pewno chcesz usunąć tę kategorię?',
                            'data-method' => 'post',
                        ]) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($categories)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Brak kategorii</h4>
                    <p class="text-muted mb-4">Rozpocznij organizację swoich zdjęć tworząc pierwszą kategorię</p>
                    <?= Html::a('<i class="fas fa-plus me-2"></i>Utwórz pierwszą kategorię', ['create'], [
                        'class' => 'btn btn-success'
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Traditional Table View -->
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Lista kategorii</h4>
            <small class="text-muted">Widok tabelaryczny ze wszystkimi szczegółami</small>
        </div>
        
        <?php Pjax::begin(['id' => 'categories-grid-pjax']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'summary' => 'Wyświetlono <b>{begin}-{end}</b> z <b>{totalCount}</b> wpisów',
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'summary' => '<div class="summary mb-3">Wyświetlono <b>{begin}-{end}</b> z <b>{totalCount}</b> kategorii</div>',
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
                    'contentOptions' => ['style' => 'min-width: 150px;'],
                ],
                [
                    'attribute' => 'slug',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->slug) {
                            return '<code class="small">' . Html::encode($model->slug) . '</code>';
                        }
                        return '<span class="text-muted fst-italic">Brak slug</span>';
                    },
                    'headerOptions' => ['style' => 'width: 200px;'],
                ],
                [
                    'attribute' => 'description',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->description) {
                            $truncated = mb_strlen($model->description) > 100 
                                ? mb_substr($model->description, 0, 100) . '...' 
                                : $model->description;
                            return '<span title="' . Html::encode($model->description) . '">' . 
                                   Html::encode($truncated) . '</span>';
                        }
                        return '<span class="text-muted fst-italic">Brak opisu</span>';
                    },
                    'headerOptions' => ['style' => 'max-width: 300px;'],
                ],
                [
                    'label' => 'Zdjęcia',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $photoCount = $model->getPhotos()->count();
                        if ($photoCount == 0) {
                            return '<span class="badge bg-light">0</span>';
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
                        'class' => 'form-control form-control-sm',
                        'placeholder' => 'YYYY-MM-DD'
                    ]),
                    'headerOptions' => ['style' => 'width: 140px;'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '<div class="btn-group btn-group-sm" role="group">{view}{update}{delete}</div>',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Zobacz szczegóły',
                                'data-pjax' => 0,
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'title' => 'Edytuj kategorię',
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
                                'title' => 'Usuń kategorię',
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading dla obrazków
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('src');
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Animacja stagger dla category items
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Enhanced hover effects
    categoryItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>