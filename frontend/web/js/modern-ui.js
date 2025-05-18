/**
 * Material Design Initialization for PersonalPhotoBank
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initMaterialDesign();
        initRippleEffects();
        initFormAnimations();
        initScrollAnimations();
        enhancePhotoGallery();
    });

    /**
     * Initialize Material Design components
     */
    function initMaterialDesign() {
        // Initialize MDB components if available
        if (typeof mdb !== 'undefined') {
            // Initialize all MDB components
            mdb.Ripple.init(document.querySelector('.btn'));
            mdb.Input.init(document.querySelectorAll('.form-control'));
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-mdb-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new mdb.Tooltip(tooltipTriggerEl);
            });
        }

        // Add material design classes to existing elements
        addMaterialClasses();
    }

    /**
     * Add Material Design classes to existing elements
     */
    function addMaterialClasses() {
        // Add elevation to cards
        $('.card').addClass('elevation-2');
        
        // Add material button classes
        $('.btn').not('.btn-link').addClass('ripple');
        
        // Add material form classes
        $('.form-control').closest('.form-group').addClass('md-form');
        
        // Add reveal animations to cards
        $('.card').addClass('reveal-on-scroll');
    }

    /**
     * Initialize ripple effects for buttons
     */
    function initRippleEffects() {
        // Custom ripple effect for browsers without MDB
        $('.btn, .card').on('click', function(e) {
            if ($(this).find('.ripple-container').length === 0) {
                $(this).prepend('<span class="ripple-container"></span>');
            }

            const $ripple = $(this).find('.ripple-container');
            const $btn = $(this);
            const offset = $btn.offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;
            
            $ripple.html('<span class="ripple" style="left:' + x + 'px; top:' + y + 'px;"></span>');
            
            setTimeout(function() {
                $ripple.find('.ripple').addClass('animate');
            }, 10);
            
            setTimeout(function() {
                $ripple.find('.ripple').remove();
            }, 600);
        });
    }

    /**
     * Initialize form animations
     */
    function initFormAnimations() {
        // Label animations for form inputs
        $('.form-control').on('focus blur', function() {
            const $this = $(this);
            const $label = $this.prev('label');
            
            if ($this.val() !== '' || $this.is(':focus')) {
                $label.addClass('active');
            } else {
                $label.removeClass('active');
            }
        });

        // Initialize on page load
        $('.form-control').each(function() {
            const $this = $(this);
            const $label = $this.prev('label');
            
            if ($this.val() !== '') {
                $label.addClass('active');
            }
        });

        // Animate search form
        $('.search-box').on('focus', 'input', function() {
            $(this).closest('.search-box').addClass('focused');
        }).on('blur', 'input', function() {
            if ($(this).val() === '') {
                $(this).closest('.search-box').removeClass('focused');
            }
        });
    }

    /**
     * Initialize scroll animations
     */
    function initScrollAnimations() {
        // Intersection Observer for reveal animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        
                        // Add staggered animation delay for gallery items
                        if (entry.target.classList.contains('photo-item')) {
                            const index = Array.from(entry.target.parentNode.children).indexOf(entry.target);
                            entry.target.style.animationDelay = (index * 0.1) + 's';
                        }
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            // Observe all elements with reveal-on-scroll class
            document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
                observer.observe(el);
            });
        } else {
            // Fallback for older browsers
            $('.reveal-on-scroll').addClass('revealed');
        }

        // Parallax effect for hero section
        if ($('.hero-section').length) {
            $(window).on('scroll', throttle(function() {
                const scrolled = $(window).scrollTop();
                const parallax = scrolled * 0.5;
                $('.hero-section').css('transform', 'translateY(' + parallax + 'px)');
            }, 16));
        }
    }

    /**
     * Enhance photo gallery with Material Design
     */
    function enhancePhotoGallery() {
        // Enhance photo cards
        $('.photo-item .card').each(function() {
            const $card = $(this);
            const $img = $card.find('img');
            
            // Add loading animation
            $img.on('load', function() {
                $card.addClass('loaded');
            });
            
            // Add hover effects
            $card.hover(
                function() {
                    $(this).addClass('elevation-3').removeClass('elevation-2');
                },
                function() {
                    $(this).addClass('elevation-2').removeClass('elevation-3');
                }
            );
        });

        // Enhance photo modal
        $('#photoModal').on('show.bs.modal', function() {
            $('body').addClass('modal-open-material');
        }).on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open-material');
        });

        // Add floating action button for scroll to top
        if ($('.photo-gallery').length && $(window).height() < $(document).height()) {
            $('body').append('<button class="fab" id="scrollToTop" title="Przewiń do góry"><i class="fas fa-arrow-up"></i></button>');
            
            $('#scrollToTop').on('click', function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 500, 'easeInOutCubic');
            });

            // Show/hide FAB based on scroll position
            $(window).on('scroll', throttle(function() {
                if ($(window).scrollTop() > 300) {
                    $('#scrollToTop').addClass('show');
                } else {
                    $('#scrollToTop').removeClass('show');
                }
            }, 100));
        }
    }

    /**
     * Throttle function for performance
     */
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Custom easing for animations
     */
    $.easing.easeInOutCubic = function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t + b;
        return c/2*((t-=2)*t*t + 2) + b;
    };

    // Add CSS for ripple effect
    $('<style>').text(`
        .ripple-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .ripple {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            opacity: 1;
            pointer-events: none;
        }
        
        .ripple.animate {
            animation: ripple-animation 0.6s linear;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(10);
                opacity: 0;
            }
        }
        
        .fab {
            opacity: 0;
            visibility: hidden;
            transform: scale(0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .fab.show {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }
        
        .modal-open-material {
            overflow: hidden;
        }
        
        .loaded {
            animation: fadeInUp 0.6s ease-out;
        }
    `).appendTo('head');

    // Global Material Design utilities
    window.MaterialPhotoBank = {
        showSnackbar: function(message, actionText, actionCallback) {
            // Create snackbar element
            const snackbar = $(`
                <div class="snackbar">
                    <span class="snackbar-text">${message}</span>
                    ${actionText ? `<button class="snackbar-action" type="button">${actionText}</button>` : ''}
                </div>
            `);
            
            // Add styles if not already added
            if (!$('#snackbar-styles').length) {
                $(`<style id="snackbar-styles">
                    .snackbar {
                        position: fixed;
                        bottom: 0;
                        left: 50%;
                        transform: translateX(-50%) translateY(100%);
                        background: #323232;
                        color: white;
                        padding: 14px 24px;
                        border-radius: 4px 4px 0 0;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                        z-index: 9999;
                        max-width: 568px;
                        min-width: 288px;
                        animation: slideUp 0.3s ease-out;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    
                    .snackbar-text {
                        font-size: 14px;
                        line-height: 20px;
                    }
                    
                    .snackbar-action {
                        background: none;
                        border: none;
                        color: #2196F3;
                        font-weight: 500;
                        text-transform: uppercase;
                        margin-left: 24px;
                        cursor: pointer;
                        font-size: 14px;
                    }
                    
                    @keyframes slideUp {
                        from { transform: translateX(-50%) translateY(100%); }
                        to { transform: translateX(-50%) translateY(0); }
                    }
                </style>`).appendTo('head');
            }
            
            // Add to body
            $('body').append(snackbar);
            
            // Animate in
            setTimeout(() => {
                snackbar.css('transform', 'translateX(-50%) translateY(0)');
            }, 100);
            
            // Handle action
            if (actionText && actionCallback) {
                snackbar.find('.snackbar-action').on('click', actionCallback);
            }
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                snackbar.css('transform', 'translateX(-50%) translateY(100%)');
                setTimeout(() => snackbar.remove(), 300);
            }, 4000);
        },
        
        showLoader: function(target) {
            const loader = $(`
                <div class="material-loader">
                    <div class="material-spinner">
                        <svg class="circular" viewBox="25 25 50 50">
                            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke="#2196F3" stroke-width="2" stroke-miterlimit="10"/>
                        </svg>
                    </div>
                </div>
            `);
            
            if (!$('#loader-styles').length) {
                $(`<style id="loader-styles">
                    .material-loader {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(255,255,255,0.9);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 1000;
                    }
                    
                    .material-spinner {
                        width: 40px;
                        height: 40px;
                    }
                    
                    .circular {
                        animation: rotate 2s linear infinite;
                        width: 40px;
                        height: 40px;
                    }
                    
                    .path {
                        stroke-dasharray: 90, 150;
                        stroke-dashoffset: 0;
                        stroke-linecap: round;
                        animation: dash 1.5s ease-in-out infinite;
                    }
                    
                    @keyframes rotate {
                        100% { transform: rotate(360deg); }
                    }
                    
                    @keyframes dash {
                        0% { stroke-dasharray: 1, 150; stroke-dashoffset: 0; }
                        50% { stroke-dasharray: 90, 150; stroke-dashoffset: -35; }
                        100% { stroke-dasharray: 90, 150; stroke-dashoffset: -124; }
                    }
                </style>`).appendTo('head');
            }
            
            $(target).css('position', 'relative').append(loader);
            return loader;
        },
        
        hideLoader: function(loader) {
            if (loader) {
                loader.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        }
    };

})(jQuery);