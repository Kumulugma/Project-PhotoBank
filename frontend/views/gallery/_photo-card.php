<?php
/* @var $model common\models\Photo */
/* @var $index integer */
/* @var $highlightTag string|null */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<article class="photo-card reveal-on-scroll" 
         data-photo-id="<?= $model->id ?>"
         itemscope 
         itemtype="https://schema.org/Photograph">
    
    <!-- Photo Image Container -->
    <div class="photo-image">
        <img src="<?= $model->thumbnails['medium'] ?>" 
             data-src="<?= $model->thumbnails['medium'] ?>" 
             data-large="<?= $model->thumbnails['large'] ?>"
             alt="<?= Html::encode($model->title) ?>"
             loading="lazy"
             class="photo-main-image"
             itemprop="image"
             width="<?= $model->width ?>"
             height="<?= $model->height ?>" />
        
        <!-- Overlay with Actions -->
        <div class="photo-overlay">
            <div class="photo-actions">
                <?= Html::a(
                    '<i class="fas fa-eye" aria-hidden="true"></i><span class="sr-only">Zobacz szczegóły</span>', 
                    ['/gallery/view', 'id' => $model->id], 
                    [
                        'class' => 'btn btn-primary btn-sm',
                        'title' => 'Zobacz szczegóły zdjęcia',
                        'aria-label' => 'Zobacz szczegóły zdjęcia: ' . Html::encode($model->title),
                        'encode' => false
                    ]
                ) ?>
                
                <button type="button" 
                        class="btn btn-secondary btn-sm photo-modal-trigger" 
                        title="Podgląd zdjęcia"
                        aria-label="Pokaż podgląd zdjęcia: <?= Html::encode($model->title) ?>">
                    <i class="fas fa-expand" aria-hidden="true"></i>
                    <span class="sr-only">Podgląd</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Photo Content -->
    <div class="photo-content">
        <!-- Title -->
        <header class="photo-header">
            <h3 class="photo-title" itemprop="name">
                <?= Html::a(
                    Html::encode($model->title), 
                    ['/gallery/view', 'id' => $model->id],
                    ['itemprop' => 'url']
                ) ?>
            </h3>
        </header>
        
        <!-- Description -->
        <?php if ($model->description): ?>
            <div class="photo-description" itemprop="description">
                <?= Html::encode(mb_substr($model->description, 0, 120)) ?>
                <?= mb_strlen($model->description) > 120 ? '...' : '' ?>
            </div>
        <?php endif; ?>
        
        <!-- Categories -->
        <?php if ($model->categories): ?>
            <nav class="photo-categories" aria-label="Kategorie zdjęcia">
                <?php foreach ($model->categories as $category): ?>
                    <?= Html::a(
                        Html::encode($category->name), 
                        ['/gallery/category', 'slug' => $category->slug], 
                        [
                            'class' => 'category',
                            'rel' => 'tag',
                            'title' => 'Zobacz więcej zdjęć w kategorii: ' . Html::encode($category->name)
                        ]
                    ) ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        
        <!-- Tags -->
        <?php if ($model->tags): ?>
            <nav class="photo-tags" aria-label="Tagi zdjęcia">
                <div class="tags">
                    <?php foreach ($model->tags as $tag): ?>
                        <?php
                        // Sprawdź czy tag ma być podświetlony (dla widoku tag)
                        $highlightClass = isset($highlightTag) && $tag->name == $highlightTag ? 'highlighted' : '';
                        ?>
                        <?= Html::a(
                            Html::encode($tag->name), 
                            ['/gallery/tag', 'name' => $tag->name], 
                            [
                                'class' => 'tag ' . $highlightClass,
                                'rel' => 'tag',
                                'title' => 'Zobacz więcej zdjęć z tagiem: ' . Html::encode($tag->name)
                            ]
                        ) ?>
                    <?php endforeach; ?>
                </div>
            </nav>
        <?php endif; ?>
        
        <!-- Metadata -->
        <footer class="photo-meta">
            <div class="photo-date">
                <i class="fas fa-calendar" aria-hidden="true"></i>
                <time datetime="<?= Yii::$app->formatter->asDatetime($model->created_at, 'php:Y-m-d') ?>" 
                      itemprop="dateCreated">
                    <?= Yii::$app->formatter->asDate($model->created_at) ?>
                </time>
            </div>
            
            <div class="photo-dimensions">
                <i class="fas fa-expand-arrows-alt" aria-hidden="true"></i>
                <span itemprop="width"><?= $model->width ?></span> × 
                <span itemprop="height"><?= $model->height ?></span>
            </div>
        </footer>
    </div>

    <!-- Structured Data -->
    <meta itemprop="author" content="Kumulugma">
    <meta itemprop="copyrightHolder" content="Kumulugma">
    <meta itemprop="uploadDate" content="<?= Yii::$app->formatter->asDatetime($model->created_at, 'php:Y-m-d') ?>">
</article>

<style>
/* Styl dla kart zdjęć */
.photo-card {
    background: var(--background);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--animation);
    height: 100%;
    border: 1px solid var(--border);
    display: flex;
    flex-direction: column;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.photo-image {
    position: relative;
    overflow: hidden;
    padding-top: 66.67%; /* Proporcje 3:2 */
    background: var(--surface);
}

.photo-main-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-card:hover .photo-main-image {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    padding: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-card:hover .photo-overlay {
    opacity: 1;
}

.photo-actions {
    display: flex;
    gap: 0.5rem;
}

.photo-actions .btn {
    border-radius: var(--radius-full);
    box-shadow: var(--shadow);
}

.photo-content {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.photo-header {
    margin-bottom: 0.75rem;
}

.photo-title {
    font-size: var(--font-size-lg);
    margin: 0;
    line-height: 1.3;
}

.photo-title a {
    color: var(--text-primary);
    text-decoration: none;
    transition: var(--animation);
}

.photo-title a:hover {
    color: var(--primary-color);
}

.photo-description {
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.photo-categories {
    margin-bottom: 0.75rem;
}

.category {
    display: inline-block;
    background: var(--primary-light);
    color: var(--primary-dark);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    text-decoration: none;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    transition: var(--animation);
}

.category:hover {
    background: var(--primary-color);
    color: white;
    text-decoration: none;
}

.photo-tags {
    margin-bottom: 0.75rem;
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag {
    display: inline-block;
    background: var(--surface);
    color: var(--text-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    text-decoration: none;
    transition: var(--animation);
    border: 1px solid var(--border);
}

.tag:hover {
    background: var(--text-secondary);
    color: white;
    text-decoration: none;
}

.tag.highlighted {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.photo-meta {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    color: var(--text-light);
    font-size: var(--font-size-xs);
    padding-top: 0.75rem;
    border-top: 1px solid var(--border);
}

.photo-date, .photo-dimensions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Animacja dla ładowania zdjęć */
.reveal-on-scroll {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.reveal-on-scroll.loaded {
    opacity: 1;
    transform: translateY(0);
}

/* Responsywność */
@media (max-width: 768px) {
    .photo-content {
        padding: 1rem;
    }
    
    .photo-title {
        font-size: var(--font-size-base);
    }
}

/* List view */
.list-view .photo-card {
    display: grid;
    grid-template-columns: 200px 1fr;
    height: auto;
}

.list-view .photo-image {
    padding-top: 0;
    height: 100%;
}

.list-view .photo-content {
    padding: 1rem;
}

@media (max-width: 640px) {
    .list-view .photo-card {
        grid-template-columns: 1fr;
    }
    
    .list-view .photo-image {
        padding-top: 66.67%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal triggers
    const modalTriggers = document.querySelectorAll('.photo-modal-trigger');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const photoCard = this.closest('.photo-card');
            const image = photoCard.querySelector('.photo-main-image');
            const title = photoCard.querySelector('.photo-title').innerText;
            const description = photoCard.querySelector('.photo-description')?.innerText || '';
            const tags = photoCard.querySelectorAll('.tag');
            
            // Pokaż modal jeśli istnieje
            const photoModal = document.getElementById('photoModal');
            if (photoModal) {
                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');
                const modalDescription = document.getElementById('modalDescription');
                const modalTags = document.getElementById('modalTags');
                const modalView = document.getElementById('modalView');
                
                if (modalImage) modalImage.src = image.dataset.large || image.src;
                if (modalImage) modalImage.alt = image.alt;
                if (modalTitle) modalTitle.innerText = title;
                if (modalDescription) modalDescription.innerText = description;
                
                // Wyczyść i dodaj tagi
                if (modalTags) {
                    modalTags.innerHTML = '';
                    tags.forEach(tag => {
                        modalTags.appendChild(tag.cloneNode(true));
                    });
                }
                
                // Ustaw link do widoku szczegółowego
                if (modalView) {
                    modalView.href = photoCard.querySelector('.photo-title a').href;
                }
                
                // Pokaż modal
                photoModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Lazy loading zdjęć
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.onload = () => {
                            img.classList.add('loaded');
                            img.closest('.reveal-on-scroll')?.classList.add('loaded');
                        };
                        observer.unobserve(img);
                    }
                }
            });
        });

        document.querySelectorAll('.photo-main-image[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Animacja pojawienia się kart
    const photoCards = document.querySelectorAll('.photo-card');
    if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                    observer.unobserve(entry.target);
                }
            });
        });

        photoCards.forEach(card => {
            cardObserver.observe(card);
        });
    } else {
        // Fallback dla starszych przeglądarek
        photoCards.forEach(card => {
            card.classList.add('loaded');
        });
    }
});
</script>