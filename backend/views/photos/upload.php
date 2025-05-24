<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\DropzoneAsset;
\backend\assets\AppAsset::registerControllerCss($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */

$this->title = 'Prześlij zdjęcia';
$this->params['breadcrumbs'][] = ['label' => 'Wszystkie zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register Dropzone asset
DropzoneAsset::register($this);
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