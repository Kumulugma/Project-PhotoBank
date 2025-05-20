<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\DropzoneAsset;

/* @var $this yii\web\View */

$this->title = 'Prześlij zdjęcia';
$this->params['breadcrumbs'][] = ['label' => 'Wszystkie zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register Dropzone asset
DropzoneAsset::register($this);

// Dodaj style dla Dropzone
$this->registerCss('
    /* Naprawa stylów Dropzone */
    .dropzone {
        border: 2px dashed #0087F7;
        border-radius: 5px;
        background: #f9f9f9;
        min-height: 300px;
        padding: 20px;
        position: relative;
    }

    .dropzone .dz-preview {
        position: relative;
        display: inline-block;
        margin: 15px;
        vertical-align: top;
    }

    .dropzone .dz-preview .dz-image {
        border-radius: 5px;
        overflow: hidden;
        width: 120px;
        height: 120px;
        position: relative;
        display: block;
        z-index: 10;
    }

    .dropzone .dz-preview .dz-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dropzone .dz-preview .dz-details {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0;
        font-size: 13px;
        text-align: center;
        padding: 10px;
        color: rgba(0, 0, 0, 0.9);
        background-color: rgba(255, 255, 255, 0.8);
        transition: opacity .2s linear;
        z-index: 20;
    }

    .dropzone .dz-preview:hover .dz-details {
        opacity: 1;
    }

    .dropzone .dz-preview .dz-filename {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dropzone .dz-preview .dz-size {
        margin-bottom: 5px;
        font-size: 12px;
    }

    .dropzone .dz-preview .dz-success-mark,
    .dropzone .dz-preview .dz-error-mark {
        position: absolute;
        display: none;
        top: 50%;
        left: 50%;
        margin-left: -27px;
        margin-top: -27px;
        z-index: 30;
    }

    .dropzone .dz-preview .dz-success-mark svg,
    .dropzone .dz-preview .dz-error-mark svg {
        width: 54px;
        height: 54px;
        fill: white;
    }

    .dropzone .dz-preview.dz-success .dz-success-mark {
        display: block;
        animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
    }

    .dropzone .dz-preview.dz-error .dz-error-mark {
        display: block;
    }

    .dropzone .dz-preview .dz-progress {
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -8px;
        margin-left: -40px;
        height: 16px;
        width: 80px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        overflow: hidden;
        z-index: 30;
    }

    .dropzone .dz-preview .dz-progress .dz-upload {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 0%;
        background-color: #0087F7;
        transition: width 300ms ease-in-out;
    }

    .dropzone .dz-preview.dz-success .dz-progress {
        display: none;
    }

    .dropzone .dz-preview .dz-error-message {
        position: absolute;
        top: 130px;
        left: 0;
        display: block;
        background: #be2626;
        padding: 8px 10px;
        color: white;
        max-width: 100%;
        width: 120px;
        min-width: 140px;
        border-radius: 5px;
        opacity: 0;
        z-index: 30;
        transition: opacity .3s ease;
        font-size: 12px;
        line-height: 1.2;
    }

    .dropzone .dz-preview.dz-error:hover .dz-error-message {
        opacity: 1;
    }

    .dropzone .dz-remove {
        display: block;
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
        color: #0087F7;
        text-decoration: none;
    }

    .dropzone .dz-remove:hover {
        text-decoration: underline;
    }

    .dropzone .dz-message {
        text-align: center;
        margin: 3em 0;
    }

    .dropzone .dz-message .dz-button {
        background: transparent;
        border: 2px solid #0087F7;
        border-radius: 4px;
        padding: 10px 20px;
        color: #0087F7;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .dropzone .dz-message .dz-button:hover {
        background: #0087F7;
        color: white;
    }

    /* Style dla miniatur wgranych zdjęć */
    #uploaded-photos-container .thumbnail {
        margin-bottom: 20px;
        transition: transform 0.2s;
        overflow: hidden;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    #uploaded-photos-container .thumbnail:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Inne style */
    .card {
        margin-bottom: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .photo-upload {
        margin-bottom: 3rem;
    }

    @keyframes passing-through {
        0% {
            opacity: 0;
            transform: translateY(40px);
        }
        30%, 70% {
            opacity: 1;
            transform: translateY(0px);
        }
        100% {
            opacity: 0;
            transform: translateY(-40px);
        }
    }
    .file-input-wrapper{
display:none;    
}
');
?>

<div class="photo-upload">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-cloud-upload-alt me-2"></i> Wgraj zdjęcia
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <span>Przeciągnij i upuść zdjęcia tutaj lub kliknij, aby przeglądać pliki. Możesz wgrać wiele zdjęć jednocześnie.</span>
                <p class="mb-0 mt-2">Dozwolone typy plików: JPG, PNG, GIF. Maksymalny rozmiar pliku: <?= ini_get('upload_max_filesize') ?></p>
            </div>
            
            <!-- Używamy standardowej klasy 'dropzone' dla formularza, ale bez auto-discover -->
            <form action="<?= Url::to(['upload-ajax']) ?>" class="dropzone" id="my-dropzone">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="fallback">
                    <input name="file" type="file" multiple>
                </div>
                <div class="dz-message">
                    <div>
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3em; color: #0087F7;"></i>
                        <h3>Przeciągnij i upuść pliki tutaj</h3>
                        <p>lub</p>
                        <button type="button" class="dz-button">Wybierz pliki</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <button type="button" id="submit-all" class="btn btn-success" style="display: none;">
                <i class="fas fa-upload me-2"></i> Wgraj wszystkie zdjęcia
            </button>
        </div>
    </div>
    
    <div id="uploaded-photos-panel" class="card" style="display: none;">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-check-circle me-2"></i> Wgrane zdjęcia
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <p><i class="fas fa-check me-2"></i> Twoje zdjęcia zostały pomyślnie wgrane. Są teraz w poczekalni oczekując na zatwierdzenie.</p>
                <p class="mb-0">Możesz je zatwierdzić na stronie <a href="<?= Url::to(['queue']) ?>" class="alert-link">Poczekalnia zdjęć</a>.</p>
            </div>
            
            <div id="uploaded-photos-container" class="row g-3">
                <!-- Tutaj będą dynamicznie dodawane miniatury -->
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <button type="button" id="upload-more-btn" class="btn btn-outline-primary">
                <i class="fas fa-plus me-2"></i> Wgraj więcej zdjęć
            </button>
            <a href="<?= Url::to(['queue']) ?>" class="btn btn-primary">
                <i class="fas fa-tasks me-2"></i> Przejdź do poczekalni
            </a>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Wyłącz automatyczne wykrywanie dla Dropzone
    Dropzone.autoDiscover = false;
    
    var uploadedPhotos = [];
    
    // Zaczekaj aż dokument się załaduje
    $(document).ready(function() {
        // Inicjalizacja Dropzone
        var myDropzone = new Dropzone('#my-dropzone', {
            url: '" . Url::to(['upload-ajax']) . "',
            paramName: 'file',
            maxFilesize: 20, // MB
            acceptedFiles: 'image/jpeg,image/png,image/gif',
            chunking: true,
            forceChunking: true,
            chunkSize: 1000000, // 1MB chunks
            parallelChunkUploads: false,
            maxFiles: 100,
            autoProcessQueue: false, // Nie rozpoczynaj wgrywania automatycznie
            addRemoveLinks: true,
            dictDefaultMessage: 'Przeciągnij i upuść pliki tutaj lub kliknij, aby przeglądać',
            dictResponseError: 'Błąd wgrywania pliku!',
            dictFallbackMessage: 'Twoja przeglądarka nie wspiera przeciągania i upuszczania plików.',
            dictFileTooBig: 'Plik jest zbyt duży ({{filesize}}MB). Maksymalny rozmiar: {{maxFilesize}}MB.',
            dictInvalidFileType: 'Nie możesz wgrać plików tego typu.',
            dictRemoveFile: 'Usuń',
            dictMaxFilesExceeded: 'Możesz wgrać maksymalnie {{maxFiles}} plików jednocześnie.',
            dictCancelUpload: 'Anuluj wgrywanie',
            // Dodaj token CSRF do wysyłanych danych
            params: {
                '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->csrfToken . "'
            }
        });
        
        // Pokazuj przycisk wgrywania tylko gdy są pliki w kolejce
        myDropzone.on('addedfile', function() {
            $('#submit-all').show();
        });
        
        myDropzone.on('removedfile', function() {
            if (myDropzone.files.length === 0) {
                $('#submit-all').hide();
            }
        });
        
        // Obsługa przycisku Submit All
        $('#submit-all').on('click', function() {
            $(this).prop('disabled', true);
            $(this).html('<span class=\"spinner-border spinner-border-sm me-2\"></span>Wgrywanie...');
            myDropzone.processQueue();
        });
        
        myDropzone.on('success', function(file, response) {
            if (response.success) {
                // Dodaj do tablicy wgranych zdjęć
                uploadedPhotos.push(response.photo);
                
                // Zapisz ID zdjęcia w obiekcie pliku
                file.photoId = response.photo.id;
                
                // Wizualne oznaczenie sukcesu
                $(file.previewElement).addClass('dz-success');
            } else {
                // Pokaż komunikat błędu
                this.emit('error', file, response.message || 'Wgrywanie nie powiodło się');
                $(file.previewElement).addClass('dz-error');
            }
        });
        
        myDropzone.on('queuecomplete', function() {
            // Resetuj przycisk
            $('#submit-all').prop('disabled', false).html('<i class=\"fas fa-upload me-2\"></i>Wgraj wszystkie zdjęcia');
            
            if (uploadedPhotos.length > 0) {
                // Pokaż panel z wgranymi zdjęciami
                $('#uploaded-photos-panel').show();
                
                // Generuj miniatury
                var html = '';
                for (var i = 0; i < uploadedPhotos.length; i++) {
                    var photo = uploadedPhotos[i];
                    var thumbnail = photo.thumbnails.small || photo.thumbnails.medium || Object.values(photo.thumbnails)[0];
                    
                    html += '<div class=\"col-6 col-sm-4 col-md-3 col-lg-2\">' +
                              '<div class=\"thumbnail shadow-sm\">' +
                                '<img src=\"' + thumbnail + '\" alt=\"' + photo.title + '\" class=\"img-fluid rounded\">' +
                                '<div class=\"caption p-2 bg-light\">' +
                                  '<h6 class=\"mb-1 text-truncate\">' + photo.title + '</h6>' +
                                  '<div class=\"btn-group btn-group-sm d-flex\">' +
                                    '<a href=\"" . Url::to(['update']) . "?id=' + photo.id + '\" class=\"btn btn-outline-primary\"><i class=\"fas fa-edit\"></i></a> ' +
                                    '<a href=\"" . Url::to(['view']) . "?id=' + photo.id + '\" class=\"btn btn-outline-info\"><i class=\"fas fa-eye\"></i></a>' +
                                  '</div>' +
                                '</div>' +
                              '</div>' +
                            '</div>';
                }
                
                $('#uploaded-photos-container').html(html);
            }
        });
        
        // Obsługa przycisku Wgraj więcej
        $('#upload-more-btn').on('click', function() {
            // Wyczyść dropzone
            myDropzone.removeAllFiles();
            
            // Ukryj panel z wgranymi zdjęciami
            $('#uploaded-photos-panel').hide();
            
            // Wyczyść tablicę wgranych zdjęć
            uploadedPhotos = [];
            
            // Wyczyść kontener z miniaturami
            $('#uploaded-photos-container').html('');
            
            // Ukryj przycisk wgrywania
            $('#submit-all').hide();
        });
        
        // Usuń niepożądane elementy input file
        setTimeout(function() {
            $('body > input[type=\"file\"]').remove();
            $('body > .file-input-wrapper').remove();
        }, 300);
    });
");
?>