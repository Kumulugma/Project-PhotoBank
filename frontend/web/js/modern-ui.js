/**
 * Bezpieczny Material Design dla PersonalPhotoBank
 * Naprawiona wersja bez psujących efektów
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        // Najpierw sprawdź czy buttony istnieją
        if ($('.btn').length === 0) {
            console.warn('Nie znaleziono buttonów podczas inicjalizacji Material UI');
            return;
        }
        
        // Bezpieczna inicjalizacja
        initSafeAnimations();
        initSafeScrollAnimations();
        initSafePhotoGallery();
        
        // Odłóż material design do momentu załadowania Bootstrap
        setTimeout(function() {
            initSafeMaterialDesign();
        }, 100);
    });

    /**
     * Bezpieczna inicjalizacja Material Design - bez psujących buttonów
     */
    function initSafeMaterialDesign() {
        // Sprawdź czy Bootstrap się załadował
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap nie został załadowany');
            return;
        }

        // NIE dodawaj ripple do buttonów - to je psuje!
        // Zamiast tego, dodaj tylko subtelne efekty hover

        // Tylko bezpieczne klasy CSS
        $('.card').each(function() {
            if (!$(this).hasClass('material-enhanced')) {
                $(this).addClass('material-enhanced');
            }
        });

        // Bezpieczne tooltip (tylko jeśli MDB dostępne)
        if (typeof mdb !== 'undefined') {
            try {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-mdb-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new mdb.Tooltip(tooltipTriggerEl);
                });
            } catch (e) {
                console.log('MDB tooltips nie mogły zostać zainicjalizowane:', e);
            }
        }
    }

    /**
     * BEZPIECZNE animacje scroll - bez wpływu na buttony
     */
    function initSafeScrollAnimations() {
        // Intersection Observer dla reveal animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        
                        // Staggered animation tylko dla photo items
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

            // Obserwuj tylko elementy które nie są buttonami
            document.querySelectorAll('.reveal-on-scroll:not(.btn)').forEach(function(el) {
                observer.observe(el);
            });
        } else {
            // Fallback - ale tylko dla nie-buttonów
            $('.reveal-on-scroll:not(.btn)').addClass('revealed');
        }

        // Bezpieczny parallax
        if ($('.hero-section').length) {
            $(window).on('scroll', throttle(function() {
                const scrolled = $(window).scrollTop();
                const parallax = scrolled * 0.5;
                $('.hero-section').css('transform', 'translateY(' + parallax + 'px)');
            }, 16));
        }
    }

    /**
     * Bezpieczne animacje podstawowe - NIE dotykają buttonów
     */
    function initSafeAnimations() {
        // Animacje tylko dla form inputs
        $('.form-control').on('focus blur', function() {
            const $this = $(this);
            const $label = $this.prev('label');
            
            if ($this.val() !== '' || $this.is(':focus')) {
                $label.addClass('active');
            } else {
                $label.removeClass('active');
            }
        });

        // Initialize labels on page load
        $('.form-control').each(function() {
            const $this = $(this);
            const $label = $this.prev('label');
            
            if ($this.val() !== '') {
                $label.addClass('active');
            }
        });

        // Search box animations
        $('.search-box').on('focus', 'input', function() {
            $(this).closest('.search-box').addClass('focused');
        }).on('blur', 'input', function() {
            if ($(this).val() === '') {
                $(this).closest('.search-box').removeClass('focused');
            }
        });
    }

    /**
     * Bezpieczne ulepszenia galerii - bez wpływu na buttony
     */
    function initSafePhotoGallery() {
        // Bezpieczne efekty hover dla kart (NIE dla buttonów)
        $('.photo-item .card').each(function() {
            const $card = $(this);
            const $img = $card.find('img');
            
            // Loading state
            $img.on('load', function() {
                $card.addClass('loaded');
            });
            
            // Bezpieczny hover effect - tylko dla card, nie buttonów
            $card.hover(
                function() {
                    // Znajdź buttony w tej karcie i zabezpiecz je
                    const $buttons = $(this).find('.btn');
                    $buttons.each(function() {
                        // Zapisz oryginalne style
                        $(this).data('original-display', $(this).css('display'));
                        $(this).data('original-visibility', $(this).css('visibility'));
                    });
                    
                    $(this).addClass('card-hover');
                },
                function() {
                    $(this).removeClass('card-hover');
                    
                    // Przywróć buttony jeśli zostały zmienione
                    const $buttons = $(this).find('.btn');
                    $buttons.each(function() {
                        const originalDisplay = $(this).data('original-display');
                        const originalVisibility = $(this).data('original-visibility');
                        
                        if (originalDisplay) {
                            $(this).css('display', originalDisplay);
                        }
                        if (originalVisibility) {
                            $(this).css('visibility', originalVisibility);
                        }
                    });
                }
            );
        });

        // Modal enhancements - bez wpływu na buttony
        $('#photoModal').on('show.bs.modal', function() {
            $('body').addClass('modal-open-material');
        }).on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open-material');
        });

        // FAB scroll to top - ale z bezpiecznym buttonem
        if ($('.photo-gallery').length && $(window).height() < $(document).height()) {
            const fab = $('<button class="fab" id="scrollToTop" title="Przewiń do góry"><i class="fas fa-arrow-up"></i></button>');
            
            // Dodaj bezpieczne style inline
            fab.css({
                'position': 'fixed',
                'bottom': '20px',
                'right': '20px',
                'width': '56px',
                'height': '56px',
                'border-radius': '50%',
                'border': 'none',
                'background-color': '#007bff',
                'color': 'white',
                'box-shadow': '0 4px 8px rgba(0,0,0,0.2)',
                'cursor': 'pointer',
                'z-index': '1000',
                'transition': 'all 0.3s ease',
                'opacity': '0',
                'visibility': 'hidden',
                'transform': 'scale(0)',
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'center'
            });
            
            $('body').append(fab);
            
            fab.on('click', function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 500);
            });

            // Show/hide FAB
            $(window).on('scroll', throttle(function() {
                if ($(window).scrollTop() > 300) {
                    fab.css({
                        'opacity': '1',
                        'visibility': 'visible',
                        'transform': 'scale(1)'
                    });
                } else {
                    fab.css({
                        'opacity': '0',
                        'visibility': 'hidden',
                        'transform': 'scale(0)'
                    });
                }
            }, 100));
        }
    }

    /**
     * Throttle function
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

    // Bezpieczne style CSS - NIE wpływają na buttony
    $('<style>').text(`
        /* Bezpieczne style które nie psują buttonów */
        
        .card.material-enhanced {
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        
        .card.card-hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .modal-open-material {
            overflow: hidden;
        }
        
        .loaded {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Form enhancements */
        .form-control:focus + label,
        label.active {
            color: #007bff;
            font-size: 0.85em;
            transform: translateY(-0.5rem);
        }
        
        .search-box.focused {
            box-shadow: 0 0 20px rgba(0,123,255,0.1);
        }
        
        /* Reveal animations - ale NIE dla buttonów */
        .reveal-on-scroll:not(.btn) {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .reveal-on-scroll.revealed:not(.btn) {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* FAB styles już są inline, więc nie potrzebujemy ich tutaj */
        
    `).appendTo('head');

    // Bezpieczne utilities - NIE dotykają buttonów
    window.SafeMaterialPhotoBank = {
        showSnackbar: function(message, actionText, actionCallback) {
            const snackbar = $(`
                <div class="safe-snackbar">
                    <span class="snackbar-text">${message}</span>
                    ${actionText ? `<button class="snackbar-action" type="button">${actionText}</button>` : ''}
                </div>
            `);
            
            // Inline styles dla snackbar
            snackbar.css({
                'position': 'fixed',
                'bottom': '0',
                'left': '50%',
                'transform': 'translateX(-50%) translateY(100%)',
                'background': '#323232',
                'color': 'white',
                'padding': '14px 24px',
                'border-radius': '4px 4px 0 0',
                'box-shadow': '0 4px 6px rgba(0,0,0,0.3)',
                'z-index': '9999',
                'max-width': '568px',
                'min-width': '288px',
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'space-between'
            });
            
            $('body').append(snackbar);
            
            setTimeout(() => {
                snackbar.css('transform', 'translateX(-50%) translateY(0)');
            }, 100);
            
            if (actionText && actionCallback) {
                snackbar.find('.snackbar-action').on('click', actionCallback);
            }
            
            setTimeout(() => {
                snackbar.css('transform', 'translateX(-50%) translateY(100%)');
                setTimeout(() => snackbar.remove(), 300);
            }, 4000);
        }
    };

    // Debug function do sprawdzania buttonów
    window.debugButtons = function() {
        const buttons = $('.btn');
        console.log(`Znaleziono ${buttons.length} buttonów`);
        
        buttons.each(function(i) {
            const $btn = $(this);
            const styles = {
                display: $btn.css('display'),
                visibility: $btn.css('visibility'),
                opacity: $btn.css('opacity'),
                width: $btn.width(),
                height: $btn.height()
            };
            console.log(`Button ${i}:`, styles, $btn[0]);
        });
    };

})(jQuery);