<?php
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\helpers\Html;

$this->title = 'Galeria zdjęć';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
    <div class="page-content">
        <div class="text-center mb-4">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-secondary">Przeglądaj wszystkie publiczne zdjęcia w naszej galerii</p>
        </div>
        
        <div class="photo-gallery" id="photoGallery">
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemOptions' => ['class' => 'photo-item-wrapper'],
                'summary' => '<div class="gallery-summary text-center mb-4">
                    <p class="text-secondary">Wyświetlanie <strong>{begin}-{end}</strong> z <strong>{totalCount}</strong> zdjęć</p>
                </div>',
                'layout' => "{summary}\n<div class='gallery-grid'>{items}</div>\n{pager}",
                'itemView' => function ($model, $key, $index, $widget) {
                    return $this->render('_photo-card', ['model' => $model, 'index' => $index]);
                },
                'pager' => [
                    'class' => 'yii\widgets\LinkPager',
                    'options' => ['class' => 'pagination-wrapper text-center mt-4'],
                    'linkOptions' => ['class' => 'btn btn-outline'],
                    'disabledListItemSubTagOptions' => ['class' => 'btn btn-outline disabled'],
                    'prevPageLabel' => '<i class="fas fa-chevron-left"></i> Poprzednia',
                    'nextPageLabel' => 'Następna <i class="fas fa-chevron-right"></i>',
                    'maxButtonCount' => 5,
                ],
            ]) ?>
        </div>
    </div>
</div>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.gallery-summary {
    background: var(--background);
    padding: 1rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.pagination-wrapper {
    margin-top: 3rem;
}

.pagination-wrapper .btn {
    margin: 0 0.25rem;
    min-width: 40px;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.onload = () => {
                            img.classList.add('loaded');
                        };
                        observer.unobserve(img);
                    }
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Staggered reveal animation
    const items = document.querySelectorAll('.photo-item');
    items.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('reveal-on-scroll');
    });
    
    // Photo modal functionality
    initPhotoModal();
});

function initPhotoModal() {
    document.querySelectorAll('.photo-item img').forEach(img => {
        img.addEventListener('click', function() {
            const photoItem = this.closest('.photo-item');
            const title = photoItem.querySelector('.photo-title').textContent;
            const description = photoItem.querySelector('.photo-description')?.textContent || '';
            const tags = photoItem.querySelectorAll('.tag');
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalImage').src = this.dataset.large || this.src;
            document.getElementById('modalImage').alt = this.alt;
            document.getElementById('modalDescription').textContent = description;
            
            // Copy tags
            const modalTags = document.getElementById('modalTags');
            modalTags.innerHTML = '';
            tags.forEach(tag => {
                modalTags.appendChild(tag.cloneNode(true));
            });
            
            document.getElementById('photoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
}
</script>