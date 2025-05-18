/**
 * Main JavaScript for PersonalPhotoBank Frontend
 * Modern ES6+ code with proper error handling and accessibility
 */

class PersonalPhotoBank {
    constructor() {
        this.init();
    }

    async init() {
        try {
            // Wait for DOM to be fully loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initComponents());
            } else {
                this.initComponents();
            }
        } catch (error) {
            console.error('Error initializing PersonalPhotoBank:', error);
        }
    }

    initComponents() {
        // Initialize all components
        this.initNavigation();
        this.initPhotoGallery();
        this.initModals();
        this.initForms();
        this.initScrollAnimations();
        this.initUtilities();
        this.initAccessibility();

        // Add body class to indicate JS is loaded
        document.body.classList.add('js-loaded');
        document.documentElement.classList.remove('no-js');
    }

    /**
     * Navigation functionality
     */
    initNavigation() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        const header = document.querySelector('.header');

        if (!mobileToggle || !navMenu)
            return;

        // Mobile menu toggle
        mobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleMobileMenu();
        });

        // Close mobile menu on link click
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (navMenu.classList.contains('active')) {
                    this.closeMobileMenu();
                }
            });
        });

        // Close mobile menu on outside click
        document.addEventListener('click', (e) => {
            if (!header.contains(e.target) && navMenu.classList.contains('active')) {
                this.closeMobileMenu();
            }
        });

        // Handle dropdown menus
        this.initDropdowns();

        // Header scroll behavior
        this.initHeaderScroll();

        // Keyboard navigation
        this.initKeyboardNavigation();
    }

    toggleMobileMenu() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        const isActive = navMenu.classList.toggle('active');

        // Update aria attributes
        mobileToggle.setAttribute('aria-expanded', isActive);

        // Update body overflow
        document.body.style.overflow = isActive ? 'hidden' : '';

        // Focus management
        if (isActive) {
            // Focus first menu item when opening
            const firstLink = navMenu.querySelector('.nav-link');
            if (firstLink)
                firstLink.focus();
        } else {
            // Return focus to toggle button when closing
            mobileToggle.focus();
        }
    }

    closeMobileMenu() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        navMenu.classList.remove('active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
    initDropdowns() {
        document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (!toggle || !menu)
                return;

            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const isOpen = menu.classList.contains('show');

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                        const otherToggle = otherMenu.previousElementSibling;
                        if (otherToggle)
                            otherToggle.setAttribute('aria-expanded', 'false');
                    }
                });

                // Toggle current dropdown
                menu.classList.toggle('show', !isOpen);
                toggle.setAttribute('aria-expanded', !isOpen);

                // Focus first menu item when opening
                if (!isOpen) {
                    const firstItem = menu.querySelector('.dropdown-item');
                    if (firstItem)
                        firstItem.focus();
                }
            });

            // Handle escape key and outside clicks
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                }
            });

            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    initHeaderScroll() {
        const header = document.querySelector('.header');
        if (!header)
            return;

        let lastScrollTop = 0;
        let scrollTimeout;

        const handleScroll = () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

                // Add scrolled class for styling
                header.classList.toggle('scrolled', currentScroll > 100);

                // Hide/show header on scroll (only on larger screens)
                if (window.innerWidth > 768) {
                    if (currentScroll > lastScrollTop && currentScroll > 150) {
                        header.style.transform = 'translateY(-100%)';
                    } else {
                        header.style.transform = 'translateY(0)';
                    }
                }

                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            }, 100);
        };

        window.addEventListener('scroll', handleScroll, {passive: true});
    }

    initKeyboardNavigation() {
        // Arrow key navigation in navigation menu
        document.querySelectorAll('.nav-menu, .dropdown-menu').forEach(menu => {
            menu.addEventListener('keydown', (e) => {
                const items = Array.from(menu.querySelectorAll('.nav-link, .dropdown-item'));
                const currentIndex = items.indexOf(document.activeElement);

                let newIndex;
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        newIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        newIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                        break;
                    case 'Home':
                        e.preventDefault();
                        newIndex = 0;
                        break;
                    case 'End':
                        e.preventDefault();
                        newIndex = items.length - 1;
                        break;
                    default:
                        return;
                }

                if (items[newIndex]) {
                    items[newIndex].focus();
                }
            });
        });
    }

    /**
     * Photo gallery functionality
     */
    initPhotoGallery() {
        // Initialize lazy loading
        this.initLazyLoading();

        // Initialize photo interactions
        this.initPhotoInteractions();

        // Gallery layout optimization
        this.optimizeGalleryLayout();

        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.optimizeGalleryLayout();
        }, 250));
    }
    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px'
            });

            // Observe all images with data-src
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.classList.add('loading');
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without Intersection Observer
            document.querySelectorAll('img[data-src]').forEach(img => {
                this.loadImage(img);
            });
        }
    }

    loadImage(img) {
        return new Promise((resolve, reject) => {
            const actualImg = new Image();

            actualImg.onload = () => {
                img.src = actualImg.src;
                img.classList.remove('loading');
                img.classList.add('loaded');
                resolve(img);
            };

            actualImg.onerror = () => {
                img.classList.remove('loading');
                img.classList.add('error');
                img.alt = img.alt || 'Błąd ładowania obrazu';
                reject(new Error('Failed to load image'));
            };

            actualImg.src = img.dataset.src || img.src;
        });
    }

    initPhotoInteractions() {
        // Handle photo clicks for modal
        document.addEventListener('click', (e) => {
            const photoTrigger = e.target.closest('.photo-modal-trigger, .photo-main-image');
            if (photoTrigger) {
                e.preventDefault();
                this.openPhotoModal(photoTrigger);
            }
        });

        // Handle photo tile clicks
        document.addEventListener('click', (e) => {
            const photoTile = e.target.closest('.photo-tile');
            if (photoTile && !e.target.closest('a, button')) {
                const link = photoTile.querySelector('.photo-tile-link');
                if (link) {
                    link.click();
                }
            }
        });
    }

    openPhotoModal(trigger) {
        const photoItem = trigger.closest('.photo-item, .photo-tile');
        if (!photoItem)
            return;

        const modal = document.getElementById('photoModal');
        if (!modal)
            return;

        try {
            // Extract photo data
            const img = photoItem.querySelector('img');
            const titleElement = photoItem.querySelector('.photo-title, .photo-tile-title');
            const title = titleElement ? titleElement.textContent.trim() : 'Zdjęcie';

            const descriptionElement = photoItem.querySelector('.photo-description, .photo-tile-description');
            const description = descriptionElement ? descriptionElement.textContent.trim() : '';
            const tags = photoItem.querySelectorAll('.tag');
            const photoId = photoItem.dataset.photoId;

            // Update modal content
            const modalTitle = document.getElementById('modalTitle');
            const modalImage = document.getElementById('modalImage');
            const modalDescription = document.getElementById('modalDescription');
            const modalTags = document.getElementById('modalTags');
            const modalView = document.getElementById('modalView');

            if (modalTitle)
                modalTitle.textContent = title;
            if (modalDescription) {
                modalDescription.textContent = description;
                modalDescription.style.display = description ? 'block' : 'none';
            }

            if (modalImage) {
                modalImage.src = img.dataset.large || img.src;
                modalImage.alt = img.alt || title;
            }

            // Copy tags
            if (modalTags) {
                modalTags.innerHTML = '';
                tags.forEach(tag => {
                    modalTags.appendChild(tag.cloneNode(true));
                });
            }

            // Update view link
            if (modalView && photoId) {
                modalView.href = `/gallery/view/${photoId}`;
            }

            // Show modal
            this.showModal(modal);
        } catch (error) {
            console.error('Error opening photo modal:', error);
        }
    }

    optimizeGalleryLayout() {
        const galleries = document.querySelectorAll('.photo-gallery, .gallery-grid');

        galleries.forEach(gallery => {
            const items = gallery.querySelectorAll('.photo-item');
            const containerWidth = gallery.offsetWidth;
            const minItemWidth = 300;
            const gap = 32;

            // Calculate optimal number of columns
            const columns = Math.floor((containerWidth + gap) / (minItemWidth + gap));
            const itemWidth = (containerWidth - (columns - 1) * gap) / columns;

            // Apply calculated width to items (if not using CSS Grid)
            if (gallery.style.display !== 'grid') {
                items.forEach(item => {
                    item.style.width = `${itemWidth}px`;
                });
            }
        });
    }
    /**
     * Modal functionality
     */
    initModals() {
        // Handle modal close buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close, [data-dismiss="modal"]')) {
                e.preventDefault();
                const modal = e.target.closest('.modal');
                if (modal)
                    this.hideModal(modal);
            }
        });

        // Handle backdrop clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.hideModal(e.target);
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal.active');
                if (activeModal) {
                    this.hideModal(activeModal);
                }
            }
        });

        // Handle share button in modal
        const shareButton = document.getElementById('modalShare');
        if (shareButton) {
            shareButton.addEventListener('click', () => {
                this.sharePhoto();
            });
        }
    }

    showModal(modal) {
        if (!modal)
            return;

        // Store previously focused element
        modal._previousFocus = document.activeElement;

        // Show modal
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        // Prevent body scroll
        document.body.classList.add('modal-open');

        // Focus management
        const focusableElement = modal.querySelector('.modal-close, .btn, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElement) {
            setTimeout(() => focusableElement.focus(), 100);
        }

        // Trap focus within modal
        this.trapFocus(modal);
    }

    hideModal(modal) {
        if (!modal)
            return;

        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        // Restore focus
        if (modal._previousFocus) {
            modal._previousFocus.focus();
            delete modal._previousFocus;
        }
    }

    trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
                'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
                );

        if (focusableElements.length === 0)
            return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const handleTabKey = (e) => {
            if (e.key !== 'Tab')
                return;

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        modal.addEventListener('keydown', handleTabKey);

        // Remove event listener when modal is hidden
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (!modal.classList.contains('active')) {
                        modal.removeEventListener('keydown', handleTabKey);
                        observer.disconnect();
                    }
                }
            });
        });

        observer.observe(modal, {attributes: true});
    }

    sharePhoto() {
        const modal = document.getElementById('photoModal');
        const modalTitleElement = document.getElementById('modalTitle');
        const title = modalTitleElement ? modalTitleElement.textContent : '';
        const image = document.getElementById('modalImage');

        if (!modal || !image)
            return;

        const shareData = {
            title: `PersonalPhotoBank - ${title}`,
            text: `Sprawdź to zdjęcie: ${title}`,
            url: window.location.href
        };

        if (navigator.share) {
            navigator.share(shareData).catch(err => {
                console.log('Error sharing:', err);
                this.fallbackShare(shareData);
            });
        } else {
            this.fallbackShare(shareData);
        }
    }

    fallbackShare(shareData) {
        // Copy URL to clipboard as fallback
        const url = shareData.url || window.location.href;

        this.copyToClipboard(url).then(() => {
            this.showNotification('Link skopiowany do schowka!', 'success');
        }).catch(() => {
            this.showNotification('Nie udało się skopiować linku', 'error');
        });
    }
    /**
     * Form enhancements
     */
    initForms() {
        // Enhanced form controls
        this.initFormControls();

        // Form validation
        this.initFormValidation();

        // Search form enhancements
        this.initSearchForm();
    }

    initFormControls() {
        document.querySelectorAll('.form-control').forEach(control => {
            // Floating labels
            this.initFloatingLabel(control);

            // Real-time validation
            control.addEventListener('blur', () => this.validateField(control));
            control.addEventListener('input', () => {
                if (control.classList.contains('error')) {
                    this.validateField(control);
                }
            });
        });

        // Checkbox/radio styling
        document.querySelectorAll('.form-check-input').forEach(input => {
            input.addEventListener('change', function () {
                const label = this.closest('.form-check, .tag-checkbox');
                if (label) {
                    label.classList.toggle('checked', this.checked);
                }
            });
        });
    }

    initFloatingLabel(control) {
        const label = control.previousElementSibling;
        if (!label || !label.classList.contains('form-label'))
            return;

        // Check initial state
        if (control.value) {
            control.classList.add('has-value');
        }

        control.addEventListener('focus', function () {
            this.classList.add('focused');
        });

        control.addEventListener('blur', function () {
            this.classList.remove('focused');
            this.classList.toggle('has-value', this.value !== '');
        });

        control.addEventListener('input', function () {
            this.classList.toggle('has-value', this.value !== '');
        });
    }

    initFormValidation() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();

                    // Focus first error field
                    const firstError = form.querySelector('.form-control.error');
                    if (firstError) {
                        firstError.focus();
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });
        });
    }

    validateForm(form) {
        let isValid = true;

        // Validate all form controls
        form.querySelectorAll('.form-control').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        let isValid = true;
        let message = '';

        // Remove existing error
        field.classList.remove('error');
        this.hideFieldError(field);

        const value = field.value.trim();

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'To pole jest wymagane';
        }

        // Type-specific validation
        if (isValid && value) {
            switch (field.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        isValid = false;
                        message = 'Nieprawidłowy adres email';
                    }
                    break;
                case 'password':
                    if (value.length < 6) {
                        isValid = false;
                        message = 'Hasło musi mieć co najmniej 6 znaków';
                    }
                    break;
                case 'number':
                    const min = parseFloat(field.min);
                    const max = parseFloat(field.max);
                    const numValue = parseFloat(value);

                    if (!isNaN(min) && numValue < min) {
                        isValid = false;
                        message = `Wartość musi być większa lub równa ${min}`;
                    } else if (!isNaN(max) && numValue > max) {
                        isValid = false;
                        message = `Wartość musi być mniejsza lub równa ${max}`;
                    }
                    break;
            }
        }

        // Pattern validation
        if (isValid && value && field.pattern) {
            const pattern = new RegExp(field.pattern);
            if (!pattern.test(value)) {
                isValid = false;
                message = field.title || 'Nieprawidłowy format';
            }
        }

        // Update field state
        if (!isValid) {
            field.classList.add('error');
            this.showFieldError(field, message);
        }

        return isValid;
    }

    showFieldError(field, message) {
        this.hideFieldError(field); // Remove existing error

        const errorEl = document.createElement('div');
        errorEl.className = 'field-error';
        errorEl.innerHTML = `<i class="fas fa-exclamation-circle" aria-hidden="true"></i> ${message}`;

        field.parentNode.appendChild(errorEl);
        field.setAttribute('aria-describedby', errorEl.id = `error-${Date.now()}`);
    }

    hideFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
            field.removeAttribute('aria-describedby');
        }
    }

    initSearchForm() {
        // Tag cloud interactions
        document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
            checkbox.addEventListener('click', function (e) {
                e.preventDefault();
                const input = this.querySelector('input[type="checkbox"]');
                if (input) {
                    input.checked = !input.checked;
                    this.classList.toggle('checked', input.checked);

                    // Trigger change event for form handling
                    input.dispatchEvent(new Event('change'));
                }
            });
        });

        // Search form auto-submit (optional)
        const searchForms = document.querySelectorAll('form[data-auto-submit]');
        searchForms.forEach(form => {
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('change', this.debounce(() => {
                    form.submit();
                }, 500));
            });
        });
    }
    initLoadingOverlay() {
        // Show loading overlay for AJAX requests
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.matches('form:not([data-no-loading])')) {
                this.showLoadingOverlay();

                // Hide after timeout as failsafe
                setTimeout(() => {
                    this.hideLoadingOverlay();
                }, 10000);
            }
        });

        // Hide on page load
        window.addEventListener('load', () => {
            this.hideLoadingOverlay();
        });
    }

    showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
        }
    }

    hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }
    }

    initPerformanceMonitoring() {
        // Monitor page performance
        if ('PerformanceObserver' in window) {
            try {
                // Monitor large layout shifts
                const observer = new PerformanceObserver((list) => {
                    list.getEntries().forEach((entry) => {
                        if (entry.value > 0.1) {
                            console.warn('Large layout shift detected:', entry.value);
                        }
                    });
                });

                observer.observe({entryTypes: ['layout-shift']});
            } catch (error) {
                // Silently fail if not supported
            }
        }

        // Log page load time
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData) {
                    console.log(`Page loaded in ${Math.round(perfData.loadEventEnd - perfData.fetchStart)}ms`);
                }
            }, 0);
        });
    }

    detectCapabilities() {
        const body = document.body;

        // Touch device detection
        if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
            body.classList.add('touch-device');
        }

        // Reduced motion preference
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            body.classList.add('reduced-motion');
        }

        // High contrast preference
        if (window.matchMedia('(prefers-contrast: high)').matches) {
            body.classList.add('high-contrast');
        }

        // Dark mode preference (for future implementation)
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.classList.add('prefers-dark');
        }

        // Feature detection
        const features = {
            webp: this.supportsWebP(),
            intersectionObserver: 'IntersectionObserver' in window,
            serviceWorker: 'serviceWorker' in navigator,
            webShare: 'share' in navigator
        };

        Object.entries(features).forEach(([feature, supported]) => {
            body.classList.toggle(`supports-${feature}`, supported);
        });
    }

    supportsWebP() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('webp') !== -1;
    }

    /**
     * Accessibility improvements
     */
    initAccessibility() {
        // Focus management
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-nav');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-nav');
        });

        // Skip links
        this.initSkipLinks();

        // ARIA live regions for dynamic content
        this.initAriaLiveRegions();

        // Keyboard shortcuts
        this.initKeyboardShortcuts();
    }

    initSkipLinks() {
        document.querySelectorAll('.skip-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const targetId = link.getAttribute('href').substring(1);
                const target = document.getElementById(targetId);

                if (target) {
                    e.preventDefault();
                    target.setAttribute('tabindex', '-1');
                    target.focus();

                    // Remove tabindex after focus
                    target.addEventListener('blur', () => {
                        target.removeAttribute('tabindex');
                    }, {once: true});
                }
            });
        });
    }

    initAriaLiveRegions() {
        // Create status region for announcements
        if (!document.getElementById('aria-status')) {
            const statusRegion = document.createElement('div');
            statusRegion.id = 'aria-status';
            statusRegion.setAttribute('aria-live', 'polite');
            statusRegion.setAttribute('aria-atomic', 'true');
            statusRegion.className = 'sr-only';
            document.body.appendChild(statusRegion);
        }
    }

    announceToScreenReader(message) {
        const statusRegion = document.getElementById('aria-status');
        if (statusRegion) {
            statusRegion.textContent = message;

            // Clear after announcement
            setTimeout(() => {
                statusRegion.textContent = '';
            }, 1000);
        }
    }

    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Global shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'k': // Ctrl+K for search
                        e.preventDefault();
                        const searchInput = document.querySelector('input[type="search"], input[name*="search"]');
                        if (searchInput) {
                            searchInput.focus();
                            this.announceToScreenReader('Pole wyszukiwania aktywowane');
                        }
                        break;

                    case 'h': // Ctrl+H for home
                        e.preventDefault();
                        window.location.href = '/';
                        break;
                }
            }

            // Escape key handlers
            if (e.key === 'Escape') {
                // Close any open modals
                const activeModal = document.querySelector('.modal.active');
                if (activeModal) {
                    this.hideModal(activeModal);
                    return;
                }

                // Close mobile menu
                const navMenu = document.getElementById('navMenu');
                if (navMenu && navMenu.classList.contains('active')) {
                    this.closeMobileMenu();
                    return;
                }

                // Clear search
                const searchInput = document.querySelector('input[type="search"]');
                if (searchInput && document.activeElement === searchInput && searchInput.value) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                }
            }
        });
    }

    /**
     * Notification system
     */
    showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('flashMessages') || this.createNotificationContainer();

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        notification.innerHTML = `
            <i class="fas ${icons[type]}" aria-hidden="true"></i>
            <span>${this.escapeHtml(message)}</span>
            <button class="notification-close" aria-label="Zamknij powiadomienie">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        `;

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.removeNotification(notification));

        container.appendChild(notification);

        // Animate in
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Auto remove
        setTimeout(() => {
            this.removeNotification(notification);
        }, duration);

        return notification;
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'flashMessages';
        container.className = 'flash-messages';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
        return container;
    }

    removeNotification(notification) {
        if (!notification.parentNode)
            return;

        notification.classList.remove('show');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    /**
     * Utility functions
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for insecure contexts
            return new Promise((resolve, reject) => {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);

                try {
                    textArea.select();
                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);

                    if (successful) {
                        resolve();
                    } else {
                        reject(new Error('Copy command failed'));
                    }
                } catch (err) {
                    document.body.removeChild(textArea);
                    reject(err);
                }
            });
        }
    }

    // Public API methods
    refresh() {
        // Re-initialization method for dynamic content
        this.initPhotoGallery();
        this.initForms();
        this.initScrollAnimations();
    }

    destroy() {
        // Cleanup method
        document.removeEventListener('DOMContentLoaded', this.initComponents);
        window.removeEventListener('scroll', this.handleScroll);
        window.removeEventListener('resize', this.handleResize);
    }
}

// Initialize the application
window.PersonalPhotoBank = PersonalPhotoBank;

// Auto-initialize
const app = new PersonalPhotoBank();

// Expose methods globally for backward compatibility
window.showNotification = (message, type, duration) => app.showNotification(message, type, duration);
window.openModal = (modal) => app.showModal(modal);
window.closeModal = (modal) => app.hideModal(modal);

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PersonalPhotoBank;
}