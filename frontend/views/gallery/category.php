<?php
/* @var $this yii\web\View */
/* @var $category common\models\Category */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\helpers\Html;

$this->title = "Kategoria: {$category->name}";
$this->params['breadcrumbs'][] = ['label' => 'Galeria', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <div class="page-content">
        <!-- Category Header -->
        <header class="category-header text-center mb-5">
            <h1 class="category-title">
                <i class="fas fa-folder" aria-hidden="true"></i>
                <?= Html::encode($category->name) ?>
            </h1>
            
            <?php if ($category->description): ?>
                <p class="category-description">
                    <?= nl2br(Html::encode($category->description)) ?>
                </p>
            <?php endif; ?>
            
            <div class="category-meta">
                <div class="meta-item">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <span id="photoCount" aria-live="polite">
                        <?= $dataProvider->totalCount ?> 
                        <?= $dataProvider->totalCount === 1 ? 'zdjęcie' : 'zdjęć' ?>
                    </span>
                </div>
            </div>
            
            <!-- Category Actions -->
            <div class="category-actions">
                <?= Html::a(
                    '<i class="fas fa-arrow-left" aria-hidden="true"></i> Wróć do galerii',
                    ['index'],
                    ['class' => 'btn btn-outline-secondary']
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
        <section class="photo-gallery" id="photoGallery" aria-label="Zdjęcia w kategorii">
            <?php if ($dataProvider->totalCount > 0): ?>
                <?= ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemOptions' => ['class' => 'photo-item-wrapper'],
                    'summary' => '
                        <div class="gallery-summary text-center mb-4">
                            <p class="text-secondary">
                                Wyświetlanie <strong>{begin}-{end}</strong> z <strong>{totalCount}</strong> zdjęć
                                w kategorii <strong>' . Html::encode($category->name) . '</strong>
                            </p>
                        </div>
                    ',
                    'layout' => "{summary}\n<div class='gallery-grid' id='galleryGrid'>{items}</div>\n{pager}",
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('/gallery/_photo-card', [
                            'model' => $model, 
                            'index' => $index
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
                        <i class="fas fa-folder-open" aria-hidden="true"></i>
                    </div>
                    <h3 class="empty-state-title">Brak zdjęć w tej kategorii</h3>
                    <p class="empty-state-message">
                        W kategorii "<?= Html::encode($category->name) ?>" nie ma jeszcze żadnych zdjęć.
                    </p>
                    <div class="empty-state-actions">
                        <?= Html::a(
                            '<i class="fas fa-images" aria-hidden="true"></i> Przeglądaj wszystkie zdjęcia',
                            ['index'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-search" aria-hidden="true"></i> Wyszukaj zdjęcia',
                            ['/search/index'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Related Categories -->
        <?php 
        $relatedCategories = \common\models\Category::find()
            ->where(['!=', 'id', $category->id])
            ->orderBy(['name' => SORT_ASC])
            ->limit(6)
            ->all();
        ?>
        
        <?php if (!empty($relatedCategories)): ?>
            <section class="related-categories mt-5" aria-label="Powiązane kategorie">
                <h2 class="section-title">Inne kategorie</h2>
                <div class="categories-grid">
                    <?php foreach ($relatedCategories as $relatedCategory): ?>
                        <article class="category-card">
                            <a href="<?= \yii\helpers\Url::to(['category', 'slug' => $relatedCategory->slug]) ?>" 
                               class="category-card-link">
                                <div class="category-card-icon">
                                    <i class="fas fa-folder" aria-hidden="true"></i>
                                </div>
                                <h3 class="category-card-title">
                                    <?= Html::encode($relatedCategory->name) ?>
                                </h3>
                                <div class="category-card-meta">
                                    <?php $photoCount = $relatedCategory->getPhotos()->count(); ?>
                                    <span class="photo-count">
                                        <i class="fas fa-images" aria-hidden="true"></i>
                                        <?= $photoCount ?> <?= $photoCount === 1 ? 'zdjęcie' : 'zdjęć' ?>
                                    </span>
                                </div>
                                <?php if ($relatedCategory->description): ?>
                                    <p class="category-card-description">
                                        <?= Html::encode(mb_substr($relatedCategory->description, 0, 100)) ?>
                                        <?= mb_strlen($relatedCategory->description) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<style>
/* Category-specific styles */
.category-header {
    padding: var(--spacing-2xl) 0;
    background: var(--gradient-surface);
    border-radius: var(--radius);
    margin-bottom: var(--spacing-xl);
    border: 1px solid var(--border);
}

.category-title {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-md);
}

.category-title i {
    color: var(--primary-color);
}

.category-description {
    font-size: var(--font-size-lg);
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto var(--spacing-lg);
    line-height: var(--line-height-relaxed);
}

.category-meta {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--background);
    border-radius: var(--radius-full);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-weight: var(--font-weight-medium);
}

.meta-item i {
    color: var(--primary-color);
}

.category-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

/* Empty state styles */
.empty-state {
    padding: var(--spacing-3xl) var(--spacing-xl);
    background: var(--surface);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--text-light);
    margin-bottom: var(--spacing-lg);
}

.empty-state-title {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.empty-state-message {
    font-size: var(--font-size-lg);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

/* Related categories */
.related-categories {
    padding: var(--spacing-xl) 0;
    border-top: 1px solid var(--border);
}

.section-title {
    text-align: center;
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-xl);
    color: var(--text-primary);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
}

.category-card {
    background: var(--background);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: var(--animation);
    height: 100%;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.category-card-link {
    display: block;
    padding: var(--spacing-xl);
    text-decoration: none;
    color: inherit;
    height: 100%;
}

.category-card-icon {
    text-align: center;
    margin-bottom: var(--spacing-md);
}

.category-card-icon i {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.category-card-title {
    text-align: center;
    font-size: var(--font-size-xl);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.category-card-meta {
    text-align: center;
    margin-bottom: var(--spacing-md);
}

.photo-count {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-xs) var(--spacing-md);
    background: var(--primary-light);
    color: var(--primary-dark);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.category-card-description {
    color: var(--text-secondary);
    line-height: var(--line-height-relaxed);
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-title {
        font-size: var(--font-size-3xl);
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .category-meta {
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .category-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .category-actions .btn {
        width: 100%;
        max-width: 280px;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View mode toggle
    const toggleButton = document.getElementById('toggleViewMode');
    const galleryGrid = document.getElementById('galleryGrid');
    const viewModeText = toggleButton?.querySelector('.view-mode-text');
    const toggleIcon = toggleButton?.querySelector('i');
    
    if (toggleButton && galleryGrid) {
        let isListView = false;
        
        toggleButton.addEventListener('click', function() {
            isListView = !isListView;
            
            if (isListView) {
                galleryGrid.classList.add('list-view');
                viewModeText.textContent = 'Widok listy';
                toggleIcon.className = 'fas fa-list';
                toggleButton.setAttribute('aria-label', 'Przełącz na widok siatki');
            } else {
                galleryGrid.classList.remove('list-view');
                viewModeText.textContent = 'Widok siatki';
                toggleIcon.className = 'fas fa-th';
                toggleButton.setAttribute('aria-label', 'Przełącz na widok listy');
            }
            
            // Announce change to screen readers
            const announcement = isListView ? 'Przełączono na widok listy' : 'Przełączono na widok siatki';
            if (window.announceToScreenReader) {
                window.announceToScreenReader(announcement);
            }
        });
    }
    
    // Initialize photo gallery enhancements
    if (window.photoGallery) {
        window.photoGallery.refresh();
    }
    
    // Photo count animation
    const photoCount = document.getElementById('photoCount');
    if (photoCount) {
        const count = parseInt(photoCount.textContent);
        if (count > 0) {
            animateCount(photoCount, count);
        }
    }
});

function animateCount(element, targetCount) {
    const duration = 1000;
    const step = targetCount / (duration / 16);
    let currentCount = 0;
    
    const animate = () => {
        currentCount += step;
        if (currentCount >= targetCount) {
            currentCount = targetCount;
            const text = targetCount + (targetCount === 1 ? ' zdjęcie' : ' zdjęć');
            element.innerHTML = element.innerHTML.replace(/\d+\s+zdjęć?/, text);
            return;
        }
        
        const displayCount = Math.floor(currentCount);
        const text = displayCount + (displayCount === 1 ? ' zdjęcie' : ' zdjęć');
        element.innerHTML = element.innerHTML.replace(/\d+\s+zdjęć?/, text);
        
        requestAnimationFrame(animate);
    };
    
    requestAnimationFrame(animate);
}
</script>