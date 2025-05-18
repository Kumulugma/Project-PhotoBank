// Główny plik JavaScript dla backendu (panelu administratora)
$(document).ready(function() {
    // Inicjalizacja tooltipów Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Inicjalizacja Select2 dla tagów i kategorii
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Wybierz opcje'
        });
        
        // Select2 z możliwością dodawania nowych opcji
        $('.select2-tags').select2({
            theme: 'bootstrap4',
            placeholder: 'Wybierz lub dodaj tagi',
            tags: true,
            tokenSeparators: [',', ' '],
            createTag: function(params) {
                var term = $.trim(params.term);
                
                if (term === '') {
                    return null;
                }
                
                return {
                    id: 'new:' + term,
                    text: term + ' (nowy)',
                    newTag: true
                };
            }
        });
    }
    
    // Obsługa Dropzone dla uploadu zdjęć
    if (typeof Dropzone !== 'undefined' && $('#dropzone').length) {
        Dropzone.autoDiscover = false;
        
        var uploadUrl = $('#dropzone').data('url');
        var redirectUrl = $('#dropzone').data('redirect') || '';
        var chunkSize = 1024 * 1024; // 1MB
        
        var myDropzone = new Dropzone('#dropzone', {
            url: uploadUrl,
            paramName: 'file',
            maxFilesize: 100, // MB
            acceptedFiles: 'image/*',
            chunking: true,
            forceChunking: true,
            chunkSize: chunkSize,
            parallelChunkUploads: true,
            maxFiles: 10,
            addRemoveLinks: true,
            dictDefaultMessage: 'Upuść zdjęcia tutaj lub kliknij aby wybrać',
            dictFileTooBig: 'Plik jest zbyt duży ({{filesize}}MB). Maksymalny rozmiar: {{maxFilesize}}MB.',
            dictInvalidFileType: 'Nie możesz wgrać plików tego typu.',
            dictResponseError: 'Serwer zwrócił błąd {{statusCode}}.',
            dictCancelUpload: 'Anuluj wgrywanie',
            dictUploadCanceled: 'Wgrywanie anulowane.',
            dictRemoveFile: 'Usuń plik',
            dictMaxFilesExceeded: 'Nie możesz wgrać więcej plików.',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        myDropzone.on('sending', function(file, xhr, formData) {
            let chunks = Math.ceil(file.size / chunkSize);
            formData.append('chunks', chunks);
            formData.append('name', file.name);
        });
        
        myDropzone.on('success', function(file, response) {
            console.log('File successfully uploaded:', response);
        });
        
        myDropzone.on('queuecomplete', function() {
            if (redirectUrl) {
                setTimeout(function() {
                    window.location.href = redirectUrl;
                }, 1500);
            }
        });
    }
    
    // Obsługa zatwierdzania zdjęć w poczekalni
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var url = $btn.attr('href');
        
        if (confirm('Czy na pewno chcesz zatwierdzić to zdjęcie?')) {
            $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_csrf': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Wystąpił błąd: ' + (response.message || 'Nieznany błąd'));
                        $btn.removeClass('disabled').html('<i class="fa fa-check"></i>');
                    }
                },
                error: function() {
                    alert('Wystąpił błąd podczas komunikacji z serwerem.');
                    $btn.removeClass('disabled').html('<i class="fa fa-check"></i>');
                }
            });
        }
    });
    
    // Obsługa masowego zatwierdzania zdjęć
    $('.approve-selected').on('click', function(e) {
        e.preventDefault();
        
        var selectedIds = [];
        $('input[name="selection[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length === 0) {
            alert('Wybierz przynajmniej jedno zdjęcie.');
            return;
        }
        
        if (confirm('Czy na pewno chcesz zatwierdzić ' + selectedIds.length + ' zdjęć?')) {
            var $btn = $(this);
            $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i> Przetwarzanie...');
            
            $.ajax({
                url: $btn.data('url'),
                type: 'POST',
                dataType: 'json',
                data: {
                    ids: selectedIds,
                    '_csrf': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Zatwierdzono ' + response.updated + ' zdjęć.');
                        location.reload();
                    } else {
                        alert('Wystąpił błąd: ' + (response.message || 'Nieznany błąd'));
                        $btn.removeClass('disabled').html('Zatwierdź zaznaczone');
                    }
                },
                error: function() {
                    alert('Wystąpił błąd podczas komunikacji z serwerem.');
                    $btn.removeClass('disabled').html('Zatwierdź zaznaczone');
                }
            });
        }
    });
    
    // Obsługa zaznaczania wielu elementów
    $('#select-all').on('change', function() {
        $('input[name="selection[]"]').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });
    
    $('input[name="selection[]"]').on('change', function() {
        updateBulkButtons();
    });
    
    function updateBulkButtons() {
        var count = $('input[name="selection[]"]:checked').length;
        if (count > 0) {
            $('.bulk-action-btn').removeClass('disabled');
            $('.selected-count').text(count);
        } else {
            $('.bulk-action-btn').addClass('disabled');
            $('.selected-count').text('0');
        }
    }
    
    // Obsługa testowania połączenia z S3
    $('#test-s3-connection').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i> Testowanie...');
        
        $.ajax({
            url: $btn.data('url'),
            type: 'POST',
            dataType: 'json',
            data: {
                '_csrf': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                   alert('Test połączenia zakończony powodzeniem: ' + response.message);
               } else {
                   alert('Błąd połączenia: ' + response.message);
               }
               $btn.removeClass('disabled').html('Testuj połączenie');
           },
           error: function() {
               alert('Wystąpił błąd podczas komunikacji z serwerem.');
               $btn.removeClass('disabled').html('Testuj połączenie');
           }
       });
   });
   
   // Obsługa analizy AI
   $('.analyze-photo-btn').on('click', function(e) {
       e.preventDefault();
       
       var $btn = $(this);
       var url = $btn.attr('href');
       
       $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i> Analizowanie...');
       
       $.ajax({
           url: url,
           type: 'POST',
           dataType: 'json',
           data: {
               '_csrf': $('meta[name="csrf-token"]').attr('content'),
               'analyze_tags': true,
               'analyze_description': true
           },
           success: function(response) {
               if (response.success) {
                   var tagsHtml = '';
                   if (response.tags && response.tags.length) {
                       $.each(response.tags, function(i, tag) {
                           tagsHtml += '<span class="badge badge-info m-1" title="Pewność: ' + tag.confidence.toFixed(2) + '%">' + 
                               tag.name + 
                               '<button type="button" class="btn btn-sm btn-link add-tag-btn" data-tag="' + tag.name + '">+</button></span>';
                       });
                   }
                   
                   $('#ai-suggestions').html(
                       '<div class="card mt-3">' +
                       '<div class="card-header">Sugestie AI</div>' +
                       '<div class="card-body">' +
                       '<h5>Proponowany opis:</h5>' +
                       '<p>' + response.description + '</p>' +
                       '<button type="button" class="btn btn-sm btn-success mb-3 use-description-btn">Użyj tego opisu</button>' +
                       '<h5>Proponowane tagi:</h5>' +
                       '<div class="ai-tags">' + tagsHtml + '</div>' +
                       '</div></div>'
                   ).show();
                   
                   // Obsługa przycisku dodawania opisu
                   $('.use-description-btn').on('click', function() {
                       $('#photo-description').val(response.description);
                   });
                   
                   // Obsługa przycisków dodawania tagów
                   $('.add-tag-btn').on('click', function() {
                       var tagName = $(this).data('tag');
                       var $select2 = $('#photo-tags');
                       
                       // Sprawdzenie czy tag już istnieje w Select2
                       var exists = false;
                       $select2.find('option').each(function() {
                           if ($(this).text().toLowerCase() === tagName.toLowerCase()) {
                               exists = true;
                               return false;
                           }
                       });
                       
                       if (!exists) {
                           // Dodanie nowego tagu do Select2
                           var newOption = new Option(tagName, 'new:' + tagName, true, true);
                           $select2.append(newOption).trigger('change');
                       } else {
                           // Zaznaczenie istniejącego tagu
                           $select2.find('option').each(function() {
                               if ($(this).text().toLowerCase() === tagName.toLowerCase()) {
                                   $select2.val($select2.val().concat($(this).val())).trigger('change');
                                   return false;
                               }
                           });
                       }
                       
                       $(this).prop('disabled', true);
                   });
               } else {
                   alert('Wystąpił błąd podczas analizy: ' + (response.message || 'Nieznany błąd'));
               }
               $btn.removeClass('disabled').html('<i class="fa fa-magic"></i> Analizuj AI');
           },
           error: function() {
               alert('Wystąpił błąd podczas komunikacji z serwerem.');
               $btn.removeClass('disabled').html('<i class="fa fa-magic"></i> Analizuj AI');
           }
       });
   });
   
   // Obsługa regeneracji miniatur
   $('#regenerate-thumbnails').on('click', function(e) {
       e.preventDefault();
       
       if (confirm('Czy na pewno chcesz zregenerować miniatury? Może to zająć dużo czasu.')) {
           var $btn = $(this);
           var url = $btn.attr('href');
           var photoId = $btn.data('photo-id') || null;
           
           $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i> Regenerowanie...');
           
           $.ajax({
               url: url,
               type: 'POST',
               dataType: 'json',
               data: {
                   '_csrf': $('meta[name="csrf-token"]').attr('content'),
                   'photo_id': photoId
               },
               success: function(response) {
                   if (response.success) {
                       alert('Zregenerowano ' + response.regenerated + ' miniatur.');
                       if (photoId) {
                           location.reload();
                       }
                   } else {
                       alert('Wystąpił błąd: ' + (response.message || 'Nieznany błąd'));
                   }
                   $btn.removeClass('disabled').html('<i class="fa fa-refresh"></i> Regeneruj miniatury');
               },
               error: function() {
                   alert('Wystąpił błąd podczas komunikacji z serwerem.');
                   $btn.removeClass('disabled').html('<i class="fa fa-refresh"></i> Regeneruj miniatury');
               }
           });
       }
   });
});