// Główny plik JavaScript dla frontendu
$(document).ready(function() {
    // Inicjalizacja tooltipów Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Inicjalizacja powiększania obrazów
    $('.photo-gallery img').on('click', function() {
        var imgSrc = $(this).data('large') || $(this).attr('src');
        $('#imageModal .modal-body img').attr('src', imgSrc);
        $('#imageModal .modal-title').text($(this).data('title') || '');
        $('#imageModal').modal('show');
    });
    
    // Obsługa filtrów wyszukiwania
    $('#search-form').on('submit', function(e) {
        var searchTerm = $('#search-term').val().trim();
        if (searchTerm === '' && $('.filter:checked').length === 0) {
            e.preventDefault();
            alert('Wprowadź frazę wyszukiwania lub wybierz co najmniej jeden filtr.');
        }
    });
    
    // Inicjalizacja infinite scroll (opcjonalnie)
    if ($('.photo-gallery').length && $('#load-more').length) {
        $(window).scroll(function() {
            if($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
                if (!$('#load-more').hasClass('loading')) {
                    $('#load-more').click();
                }
            }
        });
        
        $('#load-more').on('click', function() {
            var $this = $(this);
            if ($this.hasClass('loading')) return;
            
            $this.addClass('loading').text('Ładowanie...');
            
            $.ajax({
                url: $this.data('url'),
                data: {
                    page: $this.data('page')
                },
                success: function(response) {
                    if (response.photos && response.photos.length > 0) {
                        var html = '';
                        $.each(response.photos, function(i, photo) {
                            html += createPhotoItemHtml(photo);
                        });
                        
                        $('.photo-gallery .row').append(html);
                        $this.data('page', parseInt($this.data('page')) + 1);
                        
                        if (response.pagination && response.pagination.currentPage >= response.pagination.lastPage) {
                            $this.remove();
                        }
                    } else {
                        $this.text('Brak więcej zdjęć').prop('disabled', true);
                    }
                },
                error: function() {
                    $this.text('Wystąpił błąd, spróbuj ponownie');
                },
                complete: function() {
                    $this.removeClass('loading');
                }
            });
        });
    }
    
    // Funkcja pomocnicza do tworzenia HTML dla zdjęcia
    function createPhotoItemHtml(photo) {
        var html = '<div class="col-md-4 col-sm-6 photo-item">' +
            '<div class="card">' +
            '<img src="' + photo.thumbnail + '" class="card-img-top" alt="' + photo.title + '" ' +
            'data-large="' + photo.thumbnails.large + '" data-title="' + photo.title + '">' +
            '<div class="card-body">' +
            '<h5 class="card-title">' + photo.title + '</h5>';
            
        if (photo.description) {
            html += '<p class="card-text">' + photo.description.substring(0, 100) + 
                (photo.description.length > 100 ? '...' : '') + '</p>';
        }
        
        html += '<div class="tag-list">';
        if (photo.tags && photo.tags.length) {
            $.each(photo.tags, function(i, tag) {
                html += '<a href="/search?tag=' + tag.id + '" class="tag">' + tag.name + '</a> ';
            });
        }
        html += '</div>';
        
        html += '</div></div></div>';
        
        return html;
    }
});