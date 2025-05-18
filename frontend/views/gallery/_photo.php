<?php
/* @var $model common\models\Photo */
/* @var $index integer */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<article class="photo-item reveal-on-scroll" data-photo-id="<?= $model->id ?>">
    <div class="photo-image">
        <img src="<?= $model->thumbnails['medium'] ?>" 
             alt="<?= Html::encode($model->title) ?>"
             data-large="<?= $model->thumbnails['large'] ?>"
             loading="lazy"
             class="photo-main-image">
        <div class="photo-overlay">
            <div class="photo-actions">
                <?= Html::a('<i class="fas fa-eye"></i>', ['/gallery/view', 'id' => $model->id], [
                    'class' => 'btn btn-primary btn-sm',
                    'title' => 'Zobacz szczegóły',
                    'encode' => false
                ]) ?>
                <button class="btn btn-secondary btn-sm photo-modal-trigger" 
                        title="Podgląd">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="photo-content">
        <h3 class="photo-title">
            <?= Html::a(Html::encode($model->title), ['/gallery/view', 'id' => $model->id]) ?>
        </h3>
        
        <?php if ($model->description): ?>
            <p class="photo-description">
                <?= Html::encode(mb_substr($model->description, 0, 120)) ?>
                <?= mb_strlen($model->description) > 120 ? '...' : '' ?>
            </p>
        <?php endif; ?>
        
        <?php if ($model->categories): ?>
            <div class="photo-categories">
                <?php foreach ($model->categories as $category): ?>
                    <?= Html::a(Html::encode($category->name), ['/gallery/category', 'slug' => $category->slug], [
                        'class' => 'category'
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($model->tags): ?>
            <div class="tags">
                <?php foreach ($model->tags as $tag): ?>
                    <?= Html::a(Html::encode($tag->name), ['/gallery/tag', 'name' => $tag->name], [
                        'class' => 'tag'
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="photo-meta">
            <span class="photo-date">
                <i class="fas fa-calendar"></i> 
                <?= Yii::$app->formatter->asDate($model->created_at) ?>
            </span>
            <span class="photo-dimensions">
                <i class="fas fa-expand-arrows-alt"></i>
                <?= $model->width ?> × <?= $model->height ?>
            </span>
        </div>
    </div>
</article>

<style>
.photo-item {
    background: var(--background);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--animation);
    cursor: pointer;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.photo-item:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.photo-item:hover .photo-overlay {
    opacity: 1;
}

.photo-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.photo-main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--animation);
    display: block;
}

.photo-item:hover .photo-main-image {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--animation);
}

.photo-actions {
    display: flex;
    gap: 0.75rem;
}

.photo-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.photo-title {
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
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
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex: 1;
}

.photo-categories {
    margin-bottom: 0.75rem;
}

.photo-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.photo-meta span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.photo-meta i {
    opacity: 0.7;
}

/* Loading state */
.photo-main-image[data-src] {
    background: var(--surface);
    background-image: linear-gradient(90deg, var(--surface) 25%, var(--border) 50%, var(--surface) 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

.photo-main-image.loaded {
    animation: none;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .photo-image {
        height: 200px;
    }
    
    .photo-content {
        padding: 1rem;
    }
    
    .photo-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal trigger functionality
    document.querySelectorAll('.photo-modal-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const photoItem = this.closest('.photo-item');
            const img = photoItem.querySelector('.photo-main-image');
            const title = photoItem.querySelector('.photo-title').textContent.trim();
            const description = photoItem.querySelector('.photo-description')?.textContent.trim() || '';
            const tags = photoItem.querySelectorAll('.tag');
            
            // Set modal content
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalImage').src = img.dataset.large || img.src;
            document.getElementById('modalImage').alt = img.alt;
            document.getElementById('modalDescription').textContent = description;
            
            // Copy tags
            const modalTags = document.getElementById('modalTags');
            modalTags.innerHTML = '';
            tags.forEach(tag => {
                modalTags.appendChild(tag.cloneNode(true));
            });
            
            // Show modal
            document.getElementById('photoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Click on photo to view details
    document.querySelectorAll('.photo-main-image').forEach(img => {
        img.addEventListener('click', function(e) {
            // Only if not clicking on action buttons
            if (!e.target.closest('.photo-actions')) {
                const photoItem = this.closest('.photo-item');
                const photoId = photoItem.dataset.photoId;
                window.location.href = `/gallery/view/${photoId}`;
            }
        });
    });
});
</script>