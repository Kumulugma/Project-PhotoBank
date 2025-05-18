/**
 * Custom JavaScript for PersonalPhotoBank Frontend
 * Replaces Bootstrap JS with custom interactions
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initNavigation();
        initPhotoGallery();
        initModals();
        initForms();
        initScrollAnimations();
        initUtilities();
    });

    /**
     * Navigation functionality
     */
    function initNavigation() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        const header = document.querySelector('.header');
        
        // Mobile menu toggle
        if (mobileToggle && navMenu) {
            mobileToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                
                const icon = this.querySelector('i');
                if (navMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                    document.body.style.overflow = 'hidden';
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Close mobile menu on link click
            navMenu.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    navMenu.classList.remove('active');
                    mobileToggle.querySelector('i').classList.remove('fa-times');
                    mobileToggle.querySelector('i').classList.add('fa-bars');
                    document.body.style.overflow = 'auto';
                });
            });
            
            // Close mobile menu on outside click
            document.addEventListener('click', function(e) {
                if (!header.contains(e.target) && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    mobileToggle.querySelector('i').classList.remove('fa-times');
                    mobileToggle.querySelector('i').classList.add('fa-bars');
                    document.body.style.overflow = 'auto';
                }
            });
        }
        
        // Header scroll behavior
        let lastScrollTop = 0;
        let scrollTimeout;
        
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                if (currentScroll > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                // Hide/show header on scroll
                if (currentScroll > lastScrollTop && currentScroll > 150) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            }, 100);
        });
    }

    /**
     * Photo gallery functionality
     */
    function initPhotoGallery() {
        // Masonry-like layout adjustment
        const gallery = document.querySelector('.photo-gallery');
        if (gallery) {
            adjustGalleryLayout();
            window.addEventListener('resize', debounce(adjustGalleryLayout, 250));
        }
        
        // Lazy loading for images
        initLazyLoading();
        
        // Photo hover effects
        document.querySelectorAll('.photo-item').forEach(item => {
            const img = item.querySelector('img');
            if (img) {
                img.addEventListener('load', function() {
                    item.classList.add('loaded');
                });
                
                // Parallax effect on scroll
                if (window.innerWidth > 768) {
                    initParallaxEffect(item);
                }
            }
        });
    }

    /**
     * Modal functionality
     */
    function initModals() {
        // Photo modal
        const photoModal = document.getElementById('photoModal');
        if (photoModal) {
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            const modalTags = document.getElementById('modalTags');
            
            // Open modal on photo click
            document.addEventListener('click', function(e) {
                const photoItem = e.target.closest('.photo-item');
                const isPhotoClick = e.target.matches('.photo-item img, .photo-modal-trigger');
                
                if (isPhotoClick && photoItem) {
                    e.preventDefault();
                    
                    const img = photoItem.querySelector('img');
                    const title = photoItem.querySelector('.photo-title').textContent.trim();
                    const description = photoItem.querySelector('.photo-description')?.textContent.trim() || '';
                    const tags = photoItem.querySelectorAll('.tag');
                    
                    // Set modal content
                    modalTitle.textContent = title;
                    modalImage.src = img.dataset.large || img.src;
                    modalImage.alt = img.alt;
                    modalDescription.textContent = description;
                    
                    // Copy tags
                    modalTags.innerHTML = '';
                    tags.forEach(tag => {
                        modalTags.appendChild(tag.cloneNode(true));
                    });
                    
                    // Show modal
                    openModal(photoModal);
                }
            });
            
            // Close modal
            const closeBtn = photoModal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => closeModal(photoModal));
            }
            
            // Close on backdrop click
            photoModal.addEventListener('click', function(e) {
                if (e.target === photoModal) {
                    closeModal(photoModal);
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && photoModal.classList.contains('active')) {
                    closeModal(photoModal);
                }
            });
        }
        
        // Generic modal functions
        window.openModal = function(modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            modal.focus();
        };
        
        window.closeModal = function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        };
    }

    /**
     * Form enhancements
     */
    function initForms() {
        // Enhanced form controls
        document.querySelectorAll('.form-control').forEach(control => {
            // Floating labels
            const label = control.previousElementSibling;
            if (label && label.classList.contains('form-label')) {
                // Check initial state
                if (control.value) {
                    control.classList.add('has-value');
                }
                
                control.addEventListener('focus', function() {
                    this.classList.add('focused');
                });
                
                control.addEventListener('blur', function() {
                    this.classList.remove('focused');
                    this.classList.toggle('has-value', this.value !== '');
                });
                
                control.addEventListener('input', function() {
                    this.classList.toggle('has-value', this.value !== '');
                });
            }
        });
        
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate required fields
                this.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'To pole jest wymagane');
                    } else {
                        field.classList.remove('error');
                        hideFieldError(field);
                    }
                });
                
                // Validate email fields
                this.querySelectorAll('input[type="email"]').forEach(field => {
                    if (field.value && !isValidEmail(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Nieprawidłowy adres email');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Focus first error field
                    const firstError = this.querySelector('.error');
                    if (firstError) {
                        firstError.focus();
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
        
        // Real-time validation
        document.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            field.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    }

    /**
     * Scroll animations
     */
    function initScrollAnimations() {
        // Intersection Observer for reveal animations
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        
                        // Staggered animations for gallery items
                        if (entry.target.classList.contains('photo-item')) {
                            const items = Array.from(entry.target.parentNode.children);
                            const index = items.indexOf(entry.target);
                            entry.target.style.setProperty('--animation-delay', `${index * 0.1}s`);
                        }
                    }
                });
            }, observerOptions);
            
            // Observe elements with reveal-on-scroll class
            document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                observer.observe(el);
            });
            
            // Auto-add reveal-on-scroll to certain elements
            document.querySelectorAll('.photo-item, .card, .info-section').forEach(el => {
                if (!el.classList.contains('reveal-on-scroll')) {
                    el.classList.add('reveal-on-scroll');
                    observer.observe(el);
                }
            });
        } else {
            // Fallback for browsers without Intersection Observer
            document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                el.classList.add('revealed');
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    /**
     * Utility functions and features
     */
    function initUtilities() {
        // Tooltip-like functionality
        document.querySelectorAll('[title]').forEach(el => {
            el.addEventListener('mouseenter', showTooltip);
            el.addEventListener('mouseleave', hideTooltip);
        });
        
        // Copy to clipboard functionality
        document.querySelectorAll('[data-copy]').forEach(el => {
            el.addEventListener('click', function() {
                const text = this.dataset.copy || this.textContent;
                copyToClipboard(text);
            });
        });
        
        // Back to top button
        createBackToTopButton();
        
        // Keyboard navigation improvements
        improveKeyboardNavigation();
        
        // Print optimizations
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('print');
            mediaQuery.addListener(handlePrintChange);
        }
    }

    /**
     * Helper functions
     */
    function adjustGalleryLayout() {
        const gallery = document.querySelector('.photo-gallery');
        if (!gallery) return;
        
        const items = gallery.querySelectorAll('.photo-item');
        const containerWidth = gallery.offsetWidth;
        const minItemWidth = 300;
        const gap = 32;
        
        // Calculate optimal number of columns
        const columns = Math.floor((containerWidth + gap) / (minItemWidth + gap));
        const itemWidth = (containerWidth - (columns - 1) * gap) / columns;
        
        // Apply calculated width to items
        items.forEach(item => {
            item.style.width = `${itemWidth}px`;
        });
    }

    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.onload = () => {
                                img.classList.add('loaded');
                                img.classList.remove('loading');
                            };
                            img.onerror = () => {
                                img.classList.add('error');
                                img.classList.remove('loading');
                            };
                            observer.unobserve(img);
                        }
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                img.classList.add('loading');
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without Intersection Observer
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                img.onload = () => img.classList.add('loaded');
            });
        }
    }

    function initParallaxEffect(element) {
        let ticking = false;
        
        function updateParallax() {
            const rect = element.getBoundingClientRect();
            const speed = 0.5;
            const yPos = -(rect.top * speed);
            
            const img = element.querySelector('img');
            if (img) {
                img.style.transform = `translateY(${yPos}px)`;
            }
            
            ticking = false;
        }
        
        function requestParallaxUpdate() {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestParallaxUpdate);
    }

    function validateField(field) {
        let isValid = true;
        let message = '';
        
        // Remove existing error
        field.classList.remove('error');
        hideFieldError(field);
        
        // Required validation
        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            message = 'To pole jest wymagane';
        }
        
        // Email validation
        if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
            isValid = false;
            message = 'Nieprawidłowy adres email';
        }
        
        // Password strength validation
        if (field.type === 'password' && field.value && field.value.length < 6) {
            isValid = false;
            message = 'Hasło musi mieć co najmniej 6 znaków';
        }
        
        // Number validation
        if (field.type === 'number') {
            const min = parseFloat(field.min);
            const max = parseFloat(field.max);
            const value = parseFloat(field.value);
            
            if (!isNaN(min) && value < min) {
                isValid = false;
                message = `Wartość musi być większa lub równa ${min}`;
            } else if (!isNaN(max) && value > max) {
                isValid = false;
                message = `Wartość musi być mniejsza lub równa ${max}`;
            }
        }
        
        // Update field state
        if (!isValid) {
            field.classList.add('error');
            showFieldError(field, message);
        }
        
        return isValid;
    }

    function showFieldError(field, message) {
        hideFieldError(field); // Remove existing error
        
        const errorEl = document.createElement('div');
        errorEl.className = 'field-error';
        errorEl.textContent = message;
        errorEl.style.cssText = `
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        `;
        
        // Add icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-exclamation-circle';
        errorEl.insertBefore(icon, errorEl.firstChild);
        
        field.parentNode.appendChild(errorEl);
    }

    function hideFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }

    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = e.target.title;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--text-primary);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            z-index: 1000;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: var(--shadow);
        `;
        
        document.body.appendChild(tooltip);
        
        // Position tooltip
        const rect = e.target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        let top = rect.top - tooltipRect.height - 8;
        
        // Adjust if tooltip goes outside viewport
        if (left < 8) left = 8;
        if (left + tooltipRect.width > window.innerWidth - 8) {
            left = window.innerWidth - tooltipRect.width - 8;
        }
        if (top < 8) {
            top = rect.bottom + 8;
        }
        
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
        
        // Store tooltip reference
        e.target._tooltip = tooltip;
        
        // Remove title to prevent native tooltip
        e.target._originalTitle = e.target.title;
        e.target.title = '';
    }

    function hideTooltip(e) {
        if (e.target._tooltip) {
            e.target._tooltip.remove();
            e.target._tooltip = null;
        }
        
        // Restore title
        if (e.target._originalTitle) {
            e.target.title = e.target._originalTitle;
            e.target._originalTitle = null;
        }
    }

    function createBackToTopButton() {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = '<i class="fas fa-chevron-up"></i>';
        button.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: var(--primary-color);
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--animation);
            transform: scale(0);
            opacity: 0;
            z-index: 1000;
        `;
        
        button.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        document.body.appendChild(button);
        
        // Show/hide on scroll
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 500) {
                    button.style.transform = 'scale(1)';
                    button.style.opacity = '1';
                } else {
                    button.style.transform = 'scale(0)';
                    button.style.opacity = '0';
                }
            }, 100);
        });
    }

    function improveKeyboardNavigation() {
        // Add focus visible styles
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-nav');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-nav');
        });
        
        // Escape key handlers
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close any open modals
                document.querySelectorAll('.modal.active').forEach(modal => {
                    closeModal(modal);
                });
                
                // Close mobile menu
                const navMenu = document.getElementById('navMenu');
                if (navMenu && navMenu.classList.contains('active')) {
                    const mobileToggle = document.getElementById('mobileToggle');
                    mobileToggle.click();
                }
            }
        });
    }

    function handlePrintChange(mediaQuery) {
        if (mediaQuery.matches) {
            // Entering print mode
            document.body.classList.add('print-mode');
        } else {
            // Exiting print mode
            document.body.classList.remove('print-mode');
        }
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('Skopiowano do schowka!', 'success');
            }).catch(function() {
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    }

    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('Skopiowano do schowka!', 'success');
        } catch (err) {
            showNotification('Nie udało się skopiować', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    function debounce(func, wait) {
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

    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Global notification system
    window.showNotification = function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        notification.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: ${colors[type]};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 3000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 350px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        `;
        
        notification.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span>${message}</span>
            <button class="notification-close" style="
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: auto;
                padding: 0.25rem;
                border-radius: 4px;
                transition: background-color 0.2s ease;
            ">×</button>
        `;
        
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function() {
            removeNotification(notification);
        });
        
        closeBtn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        });
        
        closeBtn.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            removeNotification(notification);
        }, duration);
        
        function removeNotification(notif) {
            notif.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notif.parentNode) {
                    notif.parentNode.removeChild(notif);
                }
            }, 300);
        }
    };

    // Global utility functions
    window.PersonalPhotoBank = {
        showNotification: window.showNotification,
        copyToClipboard: copyToClipboard,
        openModal: window.openModal,
        closeModal: window.closeModal,
        debounce: debounce,
        throttle: throttle,
        version: '1.0.0'
    };

    // Add styles for keyboard navigation and other dynamic styles
    const additionalStyles = document.createElement('style');
    additionalStyles.textContent = `
        /* Keyboard navigation styles */
        .keyboard-nav *:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Loading state for images */
        img.loading {
            background: linear-gradient(90deg, var(--surface) 25%, var(--border) 50%, var(--surface) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Error state for images */
        img.error {
            background: var(--surface);
            border: 2px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        img.error::after {
            content: '⚠ Błąd ładowania';
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        /* Print mode styles */
        .print-mode .header,
        .print-mode .footer,
        .print-mode .modal,
        .print-mode .back-to-top,
        .print-mode .notification {
            display: none !important;
        }
        
        /* Smooth transitions for all elements */
        * {
            transition-property: transform, opacity, background-color, border-color, color, box-shadow;
            transition-duration: 0.2s;
            transition-timing-function: ease-in-out;
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    `;
    document.head.appendChild(additionalStyles);

})();