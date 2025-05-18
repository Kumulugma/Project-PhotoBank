<?php
/* @var $this yii\web\View */
/* @var $tag common\models\Tag */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\helpers\Html;

$this->title = "Tag: {$tag->name}";
$this->params['breadcrumbs'][] = ['label' => 'Galeria', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <div class="page-content">
        <!-- Tag Header -->
        <header class="tag-header text-center mb-5">
            <h1 class="tag-title">
                <i class="fas fa-tag" aria-hidden="true"></i>
                <?= Html::encode($tag->name) ?>
            </h1>
            
            <div class="tag-meta">
                <div class="meta-item">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <span id="photoCount" aria-live="polite">
                        <?= $dataProvider->totalCount ?> 
                        <?= $dataProvider->totalCount === 1 ? 'zdjęcie' : 'zdjęć' ?>
                    </span>
                </div>
                
                <div class="meta-item">
                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                    <span>
                        Popularność: <?= $tag->frequency ?? 0 ?>
                    </span>
                </div>
            </div>
            
            <!-- Tag Badge -->
            <div class="tag-badge-container">
                <span class="tag-badge large">
                    <?= Html::encode($tag->name) ?>
                </span>
            </div>
            
            <!-- Tag Actions -->
            <div class="tag-actions">
                <?= Html::a(
                    '<i class="fas fa-arrow-left" aria-hidden="true"></i> Wróć do galerii',
                    ['index'],
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
                
                <?= Html::a(
                    '<i class="fas fa-search" aria-hidden="true"></i> Wyszukaj podobne',
                    ['/search/index', 'SearchForm[tags][]' => $tag->id],
                    ['class' => 'btn btn-outline-primary']
                ) ?>
                
                <button type="button" 
                        class="btn btn-secondary" 
                        id="toggleViewMode"
                        aria-label="Przełącz tryb wyświetlania">
                    <i class="fas fa-th" aria-hidden="true"></i>
                    <span class="view-mode-text">Widok siatki</span>
                </button>
            </div>
        </header>

        <!-- Photo Gallery -->
        <section class="photo-gallery" id="photoGallery" aria-label="Zdjęcia z tagiem">
            <?php if ($dataProvider->totalCount > 0): ?>
                <?= ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemOptions' => ['class' => 'photo-item-wrapper'],
                    'summary' => '
                        <div class="gallery-summary text-center mb-4">
                            <p class="text-secondary">
                                Wyświetlanie <strong>{begin}-{end}</strong> z <strong>{totalCount}</strong> zdjęć
                                z tagiem <span class="tag small">' . Html::encode($tag->name) . '</span>
                            </p>
                        </div>
                    ',
                    'layout' => "{summary}\n<div class='gallery-grid' id='galleryGrid'>{items}</div>\n{pager}",
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('/gallery/_photo-card', [
                            'model' => $model, 
                            'index' => $index,
                            'highlightTag' => $this->context->actionParams['name'] ?? null
                        ]);
                    },
                    'pager' => [
                        'class' => 'yii\widgets\LinkPager',
                        'options' => ['class' => 'pagination-wrapper text-center mt-5'],
                        'linkOptions' => ['class' => 'btn btn-outline-primary'],
                        'disabledListItemSubTagOptions' => ['class' => 'btn btn-outline-secondary disabled'],
                        'prevPageLabel' => '<i class="fas fa-chevron-left" aria-hidden="true"></i> Poprzednia',
                        'nextPageLabel' => 'Następna <i class="fas fa-chevron-right" aria-hidden="true"></i>',
                        'maxButtonCount' => 5,
                    ],
                ]) ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state text-center">
                    <div class="empty-state-icon">
                        <i class="fas fa-tag" aria-hidden="true"></i>
                    </div>
                    <h3 class="empty-state-title">Brak zdjęć z tym tagiem</h3>
                    <p class="empty-state-message">
                        Nie znaleziono zdjęć z tagiem "<?= Html::encode($tag->name) ?>".
                    </p>
                    <div class="empty-state-actions">
                        <?= Html::a(
                            '<i class="fas fa-images" aria-hidden="true"></i> Przeglądaj wszystkie zdjęcia',
                            ['index'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-search" aria-hidden="true"></i> Wyszukaj inne tagi',
                            ['/search/index'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Related Tags -->
        <?php 
        $relatedTags = \common\models\Tag::find()
            ->where(['!=', 'id', $tag->id])
            ->orderBy(['frequency' => SORT_DESC])
            ->limit(20)
            ->all();
        ?>
        
        <?php if (!empty($relatedTags)): ?>
            <section class="related-tags mt-5" aria-label="Powiązane tagi">
                <h2 class="section-title">Popularne tagi</h2>
                <div class="tag-cloud">
                    <?php foreach ($relatedTags as $relatedTag): ?>
                        <?php 
                        $photoCount = $relatedTag->getPhotos()->count();
                        $frequency = $relatedTag->frequency ?? 0;
                        $size = 'normal';
                        
                        if ($frequency > 50) $size = 'large';
                        elseif ($frequency > 20) $size = 'medium';
                        elseif ($frequency < 5) $size = 'small';
                        ?>
                        <a href="<?= \yii\helpers\Url::to(['tag', 'name' => $relatedTag->name]) ?>" 
                           class="tag <?= $size ?>"
                           title="<?= $photoCount ?> <?= $photoCount === 1 ? 'zdjęcie' : 'zdjęć' ?> z tagiem <?= Html::encode($relatedTag->name) ?>">
                            <?= Html::encode($relatedTag->name) ?>
                            <span class="tag-count"><?= $photoCount ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="tag-cloud-legend">
                    <h3 class="legend-title">Legenda wielkości:</h3>
                    <div class="legend-items">
                        <span class="tag small">Małe (1-4)</span>
                        <span class="tag normal">Normalne (5-19)</span>
                        <span class="tag medium">Średnie (20-49)</span>
                        <span class="tag large">Duże (50+)</span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Tag Statistics -->
        <section class="tag-statistics mt-5" aria-label="Statystyki tagu">
            <div class="stats-container">
                <h2 class="section-title">Statystyki</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-images" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $dataProvider->totalCount ?></div>
                            <div class="stat-label">Zdjęć z tym tagiem</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $tag->frequency ?? 0 ?></div>
                            <div class="stat-label">Popularność tagu</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percentage" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <?php 
                            $totalPhotos = \common\models\Photo::find()
                                ->where(['status' => \common\models\Photo::STATUS_ACTIVE, 'is_public' => true])
                                ->count();
                            $percentage = $totalPhotos > 0 ? round(($dataProvider->totalCount / $totalPhotos) * 100, 1) : 0;
                            ?>
                            <div class="stat-number"><?= $percentage ?>%</div>
                            <div class="stat-label">Wszystkich zdjęć</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <?php 
                            $tagRank = \common\models\Tag::find()
                                ->where(['>', 'frequency', $tag->frequency ?? 0])
                                ->count() + 1;
                            ?>
                            <div class="stat-number">#<?= $tagRank ?></div>
                            <div class="stat-label">Pozycja w rankingu</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>