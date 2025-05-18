<?php
/* @var $this yii\web\View */
/* @var $randomPhotos common\models\Photo[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Zasobnik B';
?>
<div class="site-index">
    <!-- Hero section with parallax layers -->
    <section class="hero-section-new" id="hero">
        <div class="layer" id="layer1"></div>
        <div class="layer" id="layer2"></div>
        <div class="layer" id="layer3">
            <div class="hero-content">
                <h1 class="hero-title">Zasobnik <span>B</span></h1>
                <?php if (Yii::$app->user->isGuest): ?>
                    <div class="hero-actions">
                        <?= Html::a('Zaloguj się', ['/site/login'], ['class' => 'btn btn-primary btn-lg']) ?>
                    </div>
                <?php else: ?>
                    <div class="hero-actions">
                        <?= Html::a('Przeglądaj galerię', ['/gallery/index'], ['class' => 'btn btn-primary btn-lg']) ?>
                        <?= Html::a('Wyszukaj zdjęcia', ['/search/index'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($randomPhotos)): ?>
        <!-- Photo tiles section -->
        <section class="photo-tiles-section">
            <div class="container">
                <h2 class="section-title">Najnowsze zdjęcia</h2>
                <div class="photo-tiles">
                    <?php foreach ($randomPhotos as $index => $photo): ?>
                        <div class="photo-tile <?= $index === 0 ? 'photo-tile-large' : '' ?>" 
                             style="background-image: url(<?= $photo->thumbnails['medium'] ?>);">
                            <div class="photo-tile-overlay">
                                <div class="photo-tile-content">
                                    <h3 class="photo-tile-title"><?= Html::encode($photo->title) ?></h3>
                                    <?php if ($photo->description): ?>
                                        <p class="photo-tile-description">
                                            <?= Html::encode(mb_substr($photo->description, 0, 100)) ?>
                                            <?= mb_strlen($photo->description) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    <a href="<?= Url::to(['/gallery/view', 'id' => $photo->id]) ?>" 
                                       class="photo-tile-link">
                                        <i class="fas fa-eye"></i> Zobacz
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="text-center mt-5">
                <?= Html::a('Zobacz wszystkie zdjęcia', ['/gallery/index'], 
                    ['class' => 'btn btn-outline-primary btn-lg']) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Updated site index styles with new hero section */
.site-index {
    overflow-x: hidden;
}

/* Hero section with parallax layers */
.hero-section-new {
    min-height: 105vh;
    padding-top: 110px;
    background-color: #efefef;
    color: white;
    position: relative;
    top: -115px;
    margin-bottom: 0;
    overflow: hidden;
}

.layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
}

#layer1 {
    background-image: url('/images/zasobnik.png');
    z-index: 1;
}

#layer2 {
    background-image: url('/images/zasobnik_be.png');
    z-index: 2;
}

#layer3 {
    display: grid;
    place-content: center;
    z-index: 3;
}

.hero-content {
    text-align: center;
    position: relative;
    top: 150px;
    z-index: 4;
    padding: 0 var(--spacing-xl);
    max-width: 800px;
    margin: 0 auto;
}

.hero-title {
    font-family: var(--font-family-display);
    font-size: var(--font-size-5xl);
    font-weight: var(--font-weight-bold);
    color: #fff;
    margin-bottom: var(--spacing-lg);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 1s ease-out;
}

.hero-title span {
    font-size: 0.9em;
    display: block;
    font-weight: var(--font-weight-normal);
    margin-top: var(--spacing-sm);
    opacity: 0.95;
}

.hero-subtitle {
    font-family: var(--font-family-base);
    font-size: var(--font-size-xl);
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: var(--spacing-2xl);
    font-weight: var(--font-weight-medium);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 1s ease-out 0.3s both;
}

.hero-actions {
    animation: fadeInUp 1s ease-out 0.6s both;
}

.hero-actions .btn {
    margin: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-2xl);
    font-family: var(--font-family-base);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: var(--letter-spacing-wide);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.hero-actions .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.hero-actions .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border: none;
}

.hero-actions .btn-outline-secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    backdrop-filter: blur(10px);
}

.hero-actions .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

/* Parallax effect for layers */
@media (prefers-reduced-motion: no-preference) {
    .layer {
        transition: transform 0.1s ease-out;
    }
}

/* Photo tiles section - updated typography */
.photo-tiles-section {
    padding: var(--spacing-3xl) 0;
    background: var(--surface);
    margin-top: -50px;
    position: relative;
    z-index: 10;
}

.section-title {
    font-family: var(--font-family-display);
    text-align: center;
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-2xl);
    color: var(--text-primary);
    font-weight: var(--font-weight-semibold);
}

.photo-tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-xl);
    margin-top: var(--spacing-2xl);
}

.photo-tile {
    position: relative;
    height: 300px;
    border-radius: var(--radius);
    overflow: hidden;
    background-size: cover;
    background-position: center;
    transition: var(--animation);
    cursor: pointer;
    box-shadow: var(--shadow-md);
}

.photo-tile-large {
    grid-column: span 2;
    height: 400px;
}

.photo-tile:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.photo-tile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0) 0%,
        rgba(0, 0, 0, 0.7) 100%
    );
    display: flex;
    align-items: flex-end;
    padding: var(--spacing-xl);
    opacity: 0;
    transition: var(--animation);
}

.photo-tile:hover .photo-tile-overlay {
    opacity: 1;
}

.photo-tile-content {
    color: white;
    width: 100%;
}

.photo-tile-title {
    font-family: var(--font-family-display);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-sm);
    line-height: var(--line-height-tight);
}

.photo-tile-description {
    font-family: var(--font-family-base);
    margin-bottom: var(--spacing-md);
    opacity: 0.9;
    line-height: var(--line-height-relaxed);
}

.photo-tile-link {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: white;
    text-decoration: none;
    font-family: var(--font-family-base);
    font-weight: var(--font-weight-medium);
    padding: var(--spacing-sm) var(--spacing-md);
    border: 2px solid white;
    border-radius: var(--radius-full);
    transition: var(--animation);
    backdrop-filter: blur(10px);
}

.photo-tile-link:hover {
    background: white;
    color: var(--text-primary);
    text-decoration: none;
    transform: scale(1.05);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-section-new {
        min-height: 100vh;
        padding-top: 80px;
        top: -80px;
    }
    
    .hero-content {
        top: 100px;
        padding: 0 var(--spacing-md);
    }
    
    .hero-title {
        font-size: var(--font-size-4xl);
    }
    
    .hero-subtitle {
        font-size: var(--font-size-lg);
    }
    
    .hero-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .hero-actions .btn {
        width: 100%;
        max-width: 280px;
        margin: 0;
    }
    
    .photo-tiles {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
    
    .photo-tile-large {
        grid-column: span 1;
        height: 300px;
    }
    
    .photo-tiles-section {
        margin-top: -30px;
    }
}

@media (max-width: 480px) {
    .hero-content {
        top: 50px;
    }
    
    .hero-title {
        font-size: var(--font-size-3xl);
    }
    
    .hero-subtitle {
        font-size: var(--font-size-base);
    }
    
    .photo-tile-overlay {
        padding: var(--spacing-md);
    }
    
    .photo-tile-title {
        font-size: var(--font-size-lg);
    }
}

/* High performance optimizations */
.layer {
    will-change: transform;
}

.photo-tile {
    will-change: transform;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .hero-title,
    .hero-subtitle,
    .hero-actions {
        animation: none;
    }
    
    .photo-tile:hover {
        transform: none;
    }
    
    .layer {
        transition: none;
    }
}

/* Print styles */
@media print {
    .hero-section-new {
        display: none;
    }
    
    .photo-tiles-section {
        margin-top: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Parallax effect for hero layers
    let ticking = false;
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        const parallax1 = document.getElementById('layer1');
        const parallax2 = document.getElementById('layer2');
        const heroTitle = document.querySelector('.hero-title');
        
        if (parallax1 && parallax2) {
            const speed1 = scrolled * 0.2;
            const speed2 = scrolled * 0.5;
            
            parallax1.style.transform = `translateY(${speed1}px)`;
            parallax2.style.transform = `translateY(${speed2}px)`;
            
            // Fade out hero content on scroll
            if (heroTitle) {
                const opacity = Math.max(0, 1 - scrolled / 500);
                heroTitle.style.opacity = opacity;
            }
        }
        
        ticking = false;
    }
    
    function requestParallaxUpdate() {
        if (!ticking && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }
    
    // Throttled scroll event
    window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
    
    // Photo tiles interactions
    const photoTiles = document.querySelectorAll('.photo-tile');
    
    photoTiles.forEach(tile => {
        tile.addEventListener('click', function() {
            const link = this.querySelector('.photo-tile-link');
            if (link && !event.target.closest('a')) {
                link.click();
            }
        });
        
        // Keyboard navigation
        tile.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
        
        // Make tiles focusable for keyboard navigation
        tile.setAttribute('tabindex', '0');
        tile.setAttribute('role', 'button');
        
        const title = tile.querySelector('.photo-tile-title');
        if (title) {
            tile.setAttribute('aria-label', `Zobacz zdjęcie: ${title.textContent}`);
        }
    });
    
    // Intersection Observer for reveal animations
    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        // Observe photo tiles for staggered animation
        photoTiles.forEach((tile, index) => {
            tile.style.opacity = '0';
            tile.style.transform = 'translateY(30px)';
            tile.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s forwards paused`;
            revealObserver.observe(tile);
        });
    }
    
    // Performance optimization: Preload critical images
    const preloadImages = () => {
        const criticalImages = document.querySelectorAll('.photo-tile');
        criticalImages.forEach(tile => {
            const bgImage = tile.style.backgroundImage;
            if (bgImage) {
                const imageUrl = bgImage.slice(4, -1).replace(/"/g, "");
                const img = new Image();
                img.src = imageUrl;
            }
        });
    };
    
    // Preload after a short delay
    setTimeout(preloadImages, 1000);
    
    // Handle window resize for responsive adjustments
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            // Recalculate positions on resize
            requestParallaxUpdate();
        }, 250);
    }, { passive: true });
});
</script>