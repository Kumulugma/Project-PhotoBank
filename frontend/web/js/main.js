/**
 * Main JavaScript file for PersonalPhotoBank Frontend
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initPhotoModal();
        initInfiniteScroll();
        initLazyLoading();
        initSearchFilters();
        initPhotoTiles();
    });

    /**
     * Initialize photo modal for gallery
     */
    function initPhotoModal() {
        // Handle photo clicks in gallery
        $(document).on('click', '.photo-item img, .photo-tile', function(e) {
            e.preventDefault();
            
            var $img = $(this).is('img') ? $(this) : $(this).find('img');
            var largeUrl = $img.data('large') || $img.attr('src');
            var title = $img.data('title') || $img.attr('alt');
            
            $('#photoModalLabel').text(title);
            $('#photoModalImage').attr('src', largeUrl).attr('alt', title);
            $('#photoModal').modal('show');
        });

        // Handle modal image loading
        $('#photoModal').on('shown.bs.modal', function() {
            var $img = $('#photoModalImage');
            $img.addClass('loading');
            
            $img.on('load', function() {
                $img.removeClass('loading');
            });
        });
    }

    /**
     * Initialize infinite scroll for gallery
     */
    function initInfiniteScroll() {
        if (!$('.photo-gallery').length) return;

        var loading = false;
        var page = 1;
        
        $(window).scroll(function() {
            if (loading) return;
            
            var scrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();
            var documentHeight = $(document).height();
            
            // Load more when 200px from bottom
            if (scrollTop + windowHeight >= documentHeight - 200) {
                loadMorePhotos();
            }
        });

        function loadMorePhotos() {
            loading = true;
            page++;
            
            // Show loading indicator
            showLoadingIndicator();
            
            $.ajax({
                url: window.location.href,
                data: { page: page },
                dataType: 'html',
                success: function(data) {
                    var $newItems = $(data).find('.photo-item');
                    if ($newItems.length > 0) {
                        $('.photo-gallery .row').append($newItems);
                        initLazyLoading(); // Re-initialize for new images
                    } else {
                        // No more items, remove scroll listener
                        $(window).off('scroll');
                        showNoMoreMessage();
                    }
                },
                error: function() {
                    page--; // Reset page on error
                    showErrorMessage();
                },
                complete: function() {
                    loading = false;
                    hideLoadingIndicator();
                }
            });
        }

        function showLoadingIndicator() {
            if (!$('.loading-indicator').length) {
                $('.photo-gallery').after(
                    '<div class="loading-indicator text-center py-4">' +
                    '<i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
                    '<p class="text-muted mt-2">Ładowanie...</p>' +
                    '</div>'
                );
            }
        }

        function hideLoadingIndicator() {
            $('.loading-indicator').remove();
        }

        function showNoMoreMessage() {
            $('.photo-gallery').after(
                '<div class="text-center py-4">' +
                '<p class="text-muted">Nie ma więcej zdjęć do załadowania.</p>' +
                '</div>'
            );
        }

        function showErrorMessage() {
            $('.photo-gallery').after(
                '<div class="alert alert-warning text-center">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                'Wystąpił błąd podczas ładowania zdjęć. ' +
                '<a href="javascript:void(0)" onclick="location.reload()">Odśwież stronę</a>' +
                '</div>'
            );
        }
    }

    /**
     * Initialize lazy loading for images
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            $('.lazy').each(function() {
                observer.observe(this);
            });
        } else {
            // Fallback for older browsers
            $('.lazy').each(function() {
                this.src = this.dataset.src;
                $(this).removeClass('lazy');
            });
        }
    }

    /**
     * Initialize search filters
     */
    function initSearchFilters() {
        // Auto-submit search form on filter change
        $('.search-form').on('change', 'input, select', function() {
            var $form = $(this).closest('form');
            clearTimeout($form.data('submitTimer'));
            
            $form.data('submitTimer', setTimeout(function() {
                $form.submit();
            }, 500));
        });

        // Tag checkbox toggle
        $('.tag-checkbox').on('click', function() {
            var $checkbox = $(this).find('input[type="checkbox"]');
            $checkbox.prop('checked', !$checkbox.prop('checked'));
            $(this).toggleClass('checked', $checkbox.prop('checked'));
        });

        // Clear all filters
        $('.clear-filters').on('click', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            $form.find('input[type="text"], input[type="search"]').val('');
            $form.find('input[type="checkbox"]').prop('checked', false);
            $form.find('select').prop('selectedIndex', 0);
            $form.submit();
        });
    }

    /**
     * Initialize photo tiles animations
     */
    function initPhotoTiles() {
        // Stagger animation for photo tiles
        $('.photo-tile').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
        });

        // Parallax effect for hero section
        if ($('.hero-section').length) {
            $(window).scroll(function() {
                var scrolled = $(window).scrollTop();
                var parallax = scrolled * 0.5;
                $('.hero-section').css('transform', 'translateY(' + parallax + 'px)');
            });
        }

        // Smooth reveal on scroll
        initScrollReveal();
    }

    /**
     * Initialize scroll reveal animations
     */
    function initScrollReveal() {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        $('.reveal-on-scroll').each(function() {
            observer.observe(this);
        });
    }

    /**
     * Utility functions
     */
    window.PhotoBank = {
        // Show notification
        notify: function(message, type) {
            type = type || 'info';
            var alertClass = 'alert-' + type;
            var iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            }[type] || 'fa-info-circle';

            var $alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                '<i class="fas ' + iconClass + ' me-2"></i>' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');

            $('main .container-fluid').prepend($alert);

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $alert.alert('close');
            }, 5000);
        },

        // Copy to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    PhotoBank.notify('Skopiowano do schowka!', 'success');
                });
            } else {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                PhotoBank.notify('Skopiowano do schowka!', 'success');
            }
        },

        // Format file size
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };

})(jQuery);