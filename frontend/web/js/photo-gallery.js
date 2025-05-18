/**
 * Photo Gallery JavaScript Module
 * Handles all photo gallery related functionality
 */

class PhotoGallery {
    constructor() {
        this.lightbox = null;
        this.currentPhotoIndex = 0;
        this.photos = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupKeyboardNavigation();
        this.initPhotoNavigation();
    }

    bindEvents() {
        // Photo item clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('.photo-main-image, .photo-modal-trigger')) {
                e.preventDefault();
                this.openLightbox(e.target);
            }
        });

        // Photo tile navigation
        document.addEventListener('click', (e) => {
            if (e.target.closest('.photo-tile') && !e.target.closest('a, button')) {
                const tile = e.target.closest('.photo-tile');
                const link = tile.querySelector('a');
                if (link) {
                    link.click();
                }
            }
        });

        // Keyboard navigation in gallery
        document.addEventListener('keydown', (e) => {
            if (!this.lightbox || !this.lightbox.classList.contains('active')) return;

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.previousPhoto();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.nextPhoto();
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.closeLightbox();
                    break;
            }
        });

        // Touch/swipe support for mobile
        if ('ontouchstart' in window) {
            this.initTouchSupport();
        }
    }

    setupKeyboardNavigation() {
        // Arrow key navigation between photos in grid
        const photoItems = document.querySelectorAll('.photo-item');
        
        photoItems.forEach((item, index) => {
            item.addEventListener('keydown', (e) => {
                let targetIndex;
                const currentIndex = Array.from(photoItems).indexOf(item);
                const itemsPerRow = this.getItemsPerRow();

                switch (e.key) {
                    case 'ArrowRight':
                        e.preventDefault();
                        targetIndex = Math.min(currentIndex + 1, photoItems.length - 1);
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        targetIndex = Math.max(currentIndex - 1, 0);
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        targetIndex = Math.min(currentIndex + itemsPerRow, photoItems.length - 1);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        targetIndex = Math.max(currentIndex - itemsPerRow, 0);
                        break;
                    case 'Home':
                        e.preventDefault();
                        targetIndex = 0;
                        break;
                    case 'End':
                        e.preventDefault();
                        targetIndex = photoItems.length - 1;
                        break;
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        this.openLightbox(item.querySelector('.photo-main-image'));
                        return;
                    default:
                        return;
                }

                if (targetIndex !== undefined && photoItems[targetIndex]) {
                    photoItems[targetIndex].focus();
                }
            });

            // Make photo items focusable
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'button');
            item.setAttribute('aria-label', `Otwórz zdjęcie: ${this.getPhotoTitle(item)}`);
        });
    }

    getItemsPerRow() {
        const gallery = document.querySelector('.photo-gallery, .gallery-grid');
        if (!gallery) return 1;

        const firstItem = gallery.querySelector('.photo-item');
        if (!firstItem) return 1;

        const galleryRect = gallery.getBoundingClientRect();
        const itemRect = firstItem.getBoundingClientRect();
        
        return Math.floor(galleryRect.width / itemRect.width);
    }

    getPhotoTitle(photoItem) {
        const titleElement = photoItem.querySelector('.photo-title');
        return titleElement ? titleElement.textContent.trim() : 'Zdjęcie';
    }

    initPhotoNavigation() {
        // Previous/Next buttons in photo view
        const prevButton = document.querySelector('.btn:has(.fa-chevron-left)');
        const nextButton = document.querySelector('.btn:has(.fa-chevron-right)');

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                this.announceNavigation('Poprzednie zdjęcie');
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                this.announceNavigation('Następne zdjęcie');
            });
        }

        // Preload adjacent images for better performance
        this.preloadAdjacentImages();
    }

    openLightbox(trigger) {
        const photoItem = trigger.closest('.photo-item, .photo-tile');
        if (!photoItem) return;

        // Create lightbox if it doesn't exist
        if (!this.lightbox) {
            this.createLightbox();
        }

        // Populate photo data
        this.loadPhotoData();
        this.currentPhotoIndex = this.findPhotoIndex(photoItem);
        this.showPhoto(this.currentPhotoIndex);

        // Show lightbox
        this.lightbox.classList.add('active');
        document.body.classList.add('lightbox-open');

        // Focus management
        const closeButton = this.lightbox.querySelector('.lightbox-close');
        if (closeButton) closeButton.focus();

        // Announce to screen readers
        this.announceToScreenReader('Galeria zdjęć otwarta. Użyj strzałek do nawigacji, Escape aby zamknąć.');
    }

    createLightbox() {
        this.lightbox = document.createElement('div');
        this.lightbox.className = 'lightbox';
        this.lightbox.setAttribute('role', 'dialog');
        this.lightbox.setAttribute('aria-modal', 'true');
        this.lightbox.setAttribute('aria-label', 'Galeria zdjęć');

        this.lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close" aria-label="Zamknij galerię">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <button class="lightbox-prev" aria-label="Poprzednie zdjęcie">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button class="lightbox-next" aria-label="Następne zdjęcie">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
                <div class="lightbox-image-container">
                    <img class="lightbox-image" src="" alt="" />
                    <div class="lightbox-loading">
                        <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="lightbox-info">
                    <h3 class="lightbox-title"></h3>
                    <p class="lightbox-description"></p>
                    <div class="lightbox-meta">
                        <span class="lightbox-counter" aria-live="polite"></span>
                        <div class="lightbox-tags"></div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(this.lightbox);

        // Bind lightbox events
        this.bindLightboxEvents();
    }

    bindLightboxEvents() {
        const overlay = this.lightbox.querySelector('.lightbox-overlay');
        const closeButton = this.lightbox.querySelector('.lightbox-close');
        const prevButton = this.lightbox.querySelector('.lightbox-prev');
        const nextButton = this.lightbox.querySelector('.lightbox-next');

        // Close events
        overlay.addEventListener('click', () => this.closeLightbox());
        closeButton.addEventListener('click', () => this.closeLightbox());

        // Navigation events
        prevButton.addEventListener('click', () => this.previousPhoto());
        nextButton.addEventListener('click', () => this.nextPhoto());

        // Prevent content clicks from closing lightbox
        this.lightbox.querySelector('.lightbox-content').addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    loadPhotoData() {
        this.photos = Array.from(document.querySelectorAll('.photo-item')).map(item => ({
            element: item,
            image: item.querySelector('.photo-main-image'),
            title: this.getPhotoTitle(item),
            description: this.getPhotoDescription(item),
            tags: this.getPhotoTags(item),
            url: this.getPhotoUrl(item)
        }));
    }

    getPhotoDescription(photoItem) {
        const descElement = photoItem.querySelector('.photo-description');
        return descElement ? descElement.textContent.trim() : '';
    }

    getPhotoTags(photoItem) {
        return Array.from(photoItem.querySelectorAll('.tag')).map(tag => ({
            name: tag.textContent.trim(),
            url: tag.href
        }));
    }

    getPhotoUrl(photoItem) {
        const link = photoItem.querySelector('.photo-title a');
        return link ? link.href : null;
    }

    findPhotoIndex(photoItem) {
        return this.photos.findIndex(photo => photo.element === photoItem);
    }

    showPhoto(index) {
        if (index < 0 || index >= this.photos.length) return;

        const photo = this.photos[index];
        const img = this.lightbox.querySelector('.lightbox-image');
        const title = this.lightbox.querySelector('.lightbox-title');
        const description = this.lightbox.querySelector('.lightbox-description');
        const counter = this.lightbox.querySelector('.lightbox-counter');
        const tags = this.lightbox.querySelector('.lightbox-tags');
        const loading = this.lightbox.querySelector('.lightbox-loading');
        const prevButton = this.lightbox.querySelector('.lightbox-prev');
        const nextButton = this.lightbox.querySelector('.lightbox-next');

        // Show loading
        loading.style.display = 'block';
        img.style.opacity = '0';

        // Load image
        const newImg = new Image();
        newImg.onload = () => {
            img.src = newImg.src;
            img.alt = photo.title;
            img.style.opacity = '1';
            loading.style.display = 'none';
        };

        newImg.onerror = () => {
            img.alt = 'Błąd ładowania obrazu';
            loading.style.display = 'none';
            this.showNotification('Nie udało się załadować obrazu', 'error');
        };

        // Use large image if available, fallback to regular
        const imageSrc = photo.image.dataset.large || photo.image.src;
        newImg.src = imageSrc;

        // Update info
        title.textContent = photo.title;
        description.textContent = photo.description;
        description.style.display = photo.description ? 'block' : 'none';

        // Update counter
        counter.textContent = `${index + 1} z ${this.photos.length}`;

        // Update tags
        tags.innerHTML = '';
        photo.tags.forEach(tag => {
            const tagElement = document.createElement('a');
            tagElement.href = tag.url;
            tagElement.className = 'tag';
            tagElement.textContent = tag.name;
            tags.appendChild(tagElement);
        });

        // Update navigation buttons
        prevButton.disabled = index === 0;
        nextButton.disabled = index === this.photos.length - 1;

        // Update current index
        this.currentPhotoIndex = index;

        // Preload adjacent images
        this.preloadImage(index - 1);
        this.preloadImage(index + 1);
    }

    preloadImage(index) {
        if (index < 0 || index >= this.photos.length) return;

        const photo = this.photos[index];
        const img = new Image();
        img.src = photo.image.dataset.large || photo.image.src;
    }

    preloadAdjacentImages() {
        // Preload images for current photo view
        const currentUrl = window.location.pathname;
        const match = currentUrl.match(/\/gallery\/view\/(\d+)/);
        
        if (match) {
            const photoId = parseInt(match[1]);
            
            // Preload previous and next images
            [photoId - 1, photoId + 1].forEach(id => {
                if (id > 0) {
                    const link = document.createElement('link');
                    link.rel = 'prefetch';
                    link.href = `/gallery/view/${id}`;
                    document.head.appendChild(link);
                }
            });
        }
    }

    previousPhoto() {
        if (this.currentPhotoIndex > 0) {
            this.showPhoto(this.currentPhotoIndex - 1);
            this.announceNavigation(`Poprzednie zdjęcie: ${this.photos[this.currentPhotoIndex].title}`);
        }
    }

    nextPhoto() {
        if (this.currentPhotoIndex < this.photos.length - 1) {
            this.showPhoto(this.currentPhotoIndex + 1);
            this.announceNavigation(`Następne zdjęcie: ${this.photos[this.currentPhotoIndex].title}`);
        }
    }

    closeLightbox() {
        if (!this.lightbox) return;

        this.lightbox.classList.remove('active');
        document.body.classList.remove('lightbox-open');

        // Return focus to trigger element
        const currentPhoto = this.photos[this.currentPhotoIndex];
        if (currentPhoto && currentPhoto.element) {
            currentPhoto.element.focus();
        }

        this.announceToScreenReader('Galeria zdjęć zamknięta');
    }

    initTouchSupport() {
        let startX = 0;
        let startY = 0;
        let distX = 0;
        let distY = 0;
        let minSwipeDistance = 100;

        document.addEventListener('touchstart', (e) => {
            if (!this.lightbox || !this.lightbox.classList.contains('active')) return;

            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!this.lightbox || !this.lightbox.classList.contains('active')) return;

            // Prevent scrolling while swiping
            e.preventDefault();
        }, { passive: false });

        document.addEventListener('touchend', (e) => {
            if (!this.lightbox || !this.lightbox.classList.contains('active')) return;

            distX = e.changedTouches[0].clientX - startX;
            distY = e.changedTouches[0].clientY - startY;

            // Check if swipe is horizontal and meets minimum distance
            if (Math.abs(distX) > Math.abs(distY) && Math.abs(distX) > minSwipeDistance) {
                if (distX > 0) {
                    this.previousPhoto();
                } else {
                    this.nextPhoto();
                }
            }
        }, { passive: true });
    }

    // Utility methods
    announceNavigation(message) {
        this.announceToScreenReader(message);
    }

    announceToScreenReader(message) {
        const statusRegion = document.getElementById('aria-status');
        if (statusRegion) {
            statusRegion.textContent = message;
            setTimeout(() => {
                statusRegion.textContent = '';
            }, 1000);
        }
    }

    showNotification(message, type = 'info') {
        // Use global notification system if available
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }

    // Public API
    refresh() {
        this.loadPhotoData();
        this.setupKeyboardNavigation();
    }

    destroy() {
        if (this.lightbox) {
            this.lightbox.remove();
            this.lightbox = null;
        }
        document.body.classList.remove('lightbox-open');
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.photoGallery = new PhotoGallery();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PhotoGallery;
}