/**
 * Photos Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class PhotosController {
    constructor() {
        this.init();
    }

    init() {
        this.initCommonFeatures();
        
        if (this.isIndexPage()) {
            this.initIndexPage();
        }
        
        if (this.isQueuePage()) {
            this.initQueuePage();
        }
        
        if (this.isUpdatePage()) {
            this.initUpdatePage();
        }
        
        if (this.isViewPage()) {
            this.initViewPage();
        }
        
        if (this.isUploadPage()) {
            this.initUploadPage();
        }
    }

    isIndexPage() {
        return window.location.pathname.includes('/photos/index') || 
               window.location.pathname.endsWith('/photos');
    }

    isQueuePage() {
        return window.location.pathname.includes('/photos/queue');
    }

    isUpdatePage() {
        return window.location.pathname.includes('/photos/update');
    }

    isViewPage() {
        return window.location.pathname.includes('/photos/view');
    }

    isUploadPage() {
        return window.location.pathname.includes('/photos/upload');
    }

    initCommonFeatures() {
        this.initBatchOperations();
        this.initImagePreview();
        this.initTooltips();
    }

    initIndexPage() {
        this.initQuickSearch();
        this.initBatchUpdateModal();
        this.initFilters();
    }

    initQuickSearch() {
        const quickSearchInput = document.getElementById('quick-search-code');
        const quickSearchBtn = document.getElementById('quick-search-btn');
        const searchStatus = document.getElementById('search-status');

        if (!quickSearchInput || !quickSearchBtn) return;

        quickSearchInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        const performSearch = () => {
            const code = quickSearchInput.value.trim();
            if (code.length > 0) {
                if (code.length === 12) {
                    this.searchByCompleteCode(code);
                } else {
                    this.filterByPartialCode(code);
                }
            }
        };

        quickSearchBtn.addEventListener('click', performSearch);
        quickSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        quickSearchInput.addEventListener('keyup', () => {
            const code = quickSearchInput.value.trim();
            this.updateSearchVisualFeedback(code, quickSearchBtn, searchStatus);
        });
    }

    searchByCompleteCode(code) {
        const searchStatus = document.getElementById('search-status');
        searchStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Szukam zdjęcia...';
        searchStatus.className = 'form-text mt-1 text-primary';
        
        window.location.href = `/photos/find-by-code?code=${encodeURIComponent(code)}`;
    }

    filterByPartialCode(code) {
        const searchStatus = document.getElementById('search-status');
        searchStatus.innerHTML = '<i class="fas fa-filter"></i> Filtruję wyniki...';
        searchStatus.className = 'form-text mt-1 text-info';

        const filterInput = document.querySelector('input[name="PhotoSearch[search_code]"]');
        if (filterInput) {
            filterInput.value = code;
            
            const titleFilter = document.querySelector('input[name="PhotoSearch[title]"]');
            if (titleFilter) titleFilter.value = '';

            const form = filterInput.closest('form');
            if (form) {
                form.submit();
            } else {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('PhotoSearch[search_code]', code);
                window.location.href = currentUrl.toString();
            }
        }
    }

    updateSearchVisualFeedback(code, button, status) {
        if (code.length === 12) {
            document.getElementById('quick-search-code').classList.add('border-success');
            document.getElementById('quick-search-code').classList.remove('border-warning');
            button.innerHTML = '<i class="fas fa-eye"></i> Zobacz';
            button.className = 'btn btn-success';
            status.innerHTML = '<i class="fas fa-check-circle"></i> Kod kompletny - kliknij aby przejść do zdjęcia';
            status.className = 'form-text mt-1 text-success';
        } else if (code.length > 0) {
            document.getElementById('quick-search-code').classList.add('border-warning');
            document.getElementById('quick-search-code').classList.remove('border-success');
            button.innerHTML = '<i class="fas fa-search"></i> Filtruj';
            button.className = 'btn btn-warning';
            status.innerHTML = '<i class="fas fa-info-circle"></i> Kod niekompletny - będzie użyty jako filtr';
            status.className = 'form-text mt-1 text-warning';
        } else {
            document.getElementById('quick-search-code').classList.remove('border-success', 'border-warning');
            button.innerHTML = '<i class="fas fa-search"></i> Znajdź';
            button.className = 'btn btn-primary';
            status.innerHTML = '';
            status.className = 'form-text mt-1';
        }
    }

    initBatchOperations() {
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        const batchButtons = document.querySelectorAll('.batch-action-btn');
        const selectAll = document.querySelector('input[name="selection_all"]');

        const updateBatchButtons = () => {
            const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
            batchButtons.forEach(btn => {
                btn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
            });
        };

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBatchButtons();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButtons);
        });

        return updateBatchButtons;
    }

    initBatchUpdateModal() {
        const batchUpdateSubmit = document.getElementById('batch-update-submit');
        if (batchUpdateSubmit) {
            batchUpdateSubmit.addEventListener('click', () => {
                const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                document.getElementById('batch-update-photo-ids').value = ids.join(',');
                document.getElementById('batch-update-form').submit();
            });
        }

        this.initSelect2(['#batch-categories', '#batch-tags'], '#batchUpdateModal');
    }

    initQueuePage() {
        this.initQueueBatchOperations();
    }

    initQueueBatchOperations() {
        const updateBatchButtons = this.initBatchOperations();

        const batchApproveSubmit = document.getElementById('batch-approve-submit');
        if (batchApproveSubmit) {
            batchApproveSubmit.addEventListener('click', () => {
                const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                document.getElementById('approve-photo-ids').value = ids.join(',');
                document.getElementById('batch-approve-form').submit();
            });
        }

        const batchDeleteSubmit = document.getElementById('batch-delete-submit');
        if (batchDeleteSubmit) {
            batchDeleteSubmit.addEventListener('click', () => {
                const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                document.getElementById('delete-photo-ids').value = ids.join(',');
                document.getElementById('batch-delete-form').submit();
            });
        }
    }

    initUpdatePage() {
        this.initUpdateForm();
        this.initAiFields();
        this.initSelect2(['#photo-tags', '#photo-categories']);
        this.initAutoResize();
    }

    initUpdateForm() {
        const aiAnalyzeForm = document.querySelector('.ai-analyze-form');
        if (aiAnalyzeForm) {
            aiAnalyzeForm.addEventListener('submit', (e) => {
                const submitBtn = aiAnalyzeForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analizowanie...';

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    this.showToast('Zadanie analizy AI zostało dodane do kolejki', 'info');
                }, 2000);
            });
        }
    }

    initAiFields() {
        const aiCheckbox = document.getElementById('is-ai-generated');
        const aiFields = document.getElementById('ai-fields');
        
        if (aiCheckbox && aiFields) {
            aiCheckbox.addEventListener('change', function() {
                aiFields.style.display = this.checked ? 'block' : 'none';
                
                if (!this.checked) {
                    const promptField = document.querySelector('textarea[name="Photo[ai_prompt]"]');
                    const urlField = document.querySelector('input[name="Photo[ai_generator_url]"]');
                    if (promptField) promptField.value = '';
                    if (urlField) urlField.value = '';
                }
            });
        }
    }

    initViewPage() {
        this.initCopySearchCode();
        this.initImageModal();
    }

    initCopySearchCode() {
        window.copySearchCode = () => {
            const searchCode = document.querySelector('code').textContent;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(searchCode).then(() => {
                    this.showToast('Kod został skopiowany do schowka!', 'success');
                }, () => {
                    this.fallbackCopyTextToClipboard(searchCode);
                });
            } else {
                this.fallbackCopyTextToClipboard(searchCode);
            }
        };
    }

    fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                this.showToast('Kod został skopiowany do schowka!', 'success');
            } else {
                this.showToast('Nie udało się skopiować kodu', 'error');
            }
        } catch (err) {
            this.showToast('Nie udało się skopiować kodu', 'error');
        }

        document.body.removeChild(textArea);
    }

    initUploadPage() {
        this.initDropzone();
        this.cleanupUnwantedElements();
    }

    initDropzone() {
        if (typeof Dropzone === 'undefined') {
            console.error('Dropzone library not loaded');
            return;
        }

        Dropzone.autoDiscover = false;

        const uploadedPhotos = [];
        const dropzoneElement = document.getElementById('my-dropzone');
        
        if (!dropzoneElement) {
            console.error('Dropzone element not found');
            return;
        }

        const myDropzone = new Dropzone('#my-dropzone', {
            url: window.location.origin + '/photos/upload-ajax',
            paramName: 'file',
            maxFilesize: 20,
            acceptedFiles: 'image/jpeg,image/png,image/gif',
            chunking: true,
            forceChunking: true,
            chunkSize: 1000000,
            parallelChunkUploads: false,
            maxFiles: 100,
            autoProcessQueue: false,
            addRemoveLinks: true,
            dictDefaultMessage: 'Przeciągnij i upuść pliki tutaj lub kliknij, aby przeglądać',
            dictResponseError: 'Błąd wgrywania pliku!',
            dictFallbackMessage: 'Twoja przeglądarka nie wspiera przeciągania i upuszczania plików.',
            dictFileTooBig: 'Plik jest zbyt duży ({{filesize}}MB). Maksymalny rozmiar: {{maxFilesize}}MB.',
            dictInvalidFileType: 'Nie możesz wgrać plików tego typu.',
            dictRemoveFile: 'Usuń',
            dictMaxFilesExceeded: 'Możesz wgrać maksymalnie {{maxFiles}} plików jednocześnie.',
            dictCancelUpload: 'Anuluj wgrywanie',
            params: function() {
                const csrfToken = document.querySelector('input[name="_token"], input[name="YII_CSRF_TOKEN"]');
                if (csrfToken) {
                    const params = {};
                    params[csrfToken.name] = csrfToken.value;
                    return params;
                }
                return {};
            }
        });

        const submitAllBtn = document.getElementById('submit-all');
        
        myDropzone.on('addedfile', function() {
            if (submitAllBtn) {
                submitAllBtn.style.display = 'inline-block';
            }
        });
        
        myDropzone.on('removedfile', function() {
            if (myDropzone.files.length === 0 && submitAllBtn) {
                submitAllBtn.style.display = 'none';
            }
        });
        
        if (submitAllBtn) {
            submitAllBtn.addEventListener('click', () => {
                submitAllBtn.disabled = true;
                submitAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Wgrywanie...';
                myDropzone.processQueue();
            });
        }
        
        myDropzone.on('success', (file, response) => {
            if (response.success) {
                uploadedPhotos.push(response.photo);
                file.photoId = response.photo.id;
                file.previewElement.classList.add('dz-success');
            } else {
                myDropzone.emit('error', file, response.message || 'Wgrywanie nie powiodło się');
                file.previewElement.classList.add('dz-error');
            }
        });
        
        myDropzone.on('queuecomplete', () => {
            if (submitAllBtn) {
                submitAllBtn.disabled = false;
                submitAllBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Wgraj wszystkie zdjęcia';
            }
            
            if (uploadedPhotos.length > 0) {
                this.showUploadedPhotos(uploadedPhotos);
                this.initUploadMoreButton(myDropzone, uploadedPhotos);
            }
        });

        myDropzone.on('error', (file, errorMessage) => {
            console.error('Dropzone error:', errorMessage);
            this.showToast(errorMessage, 'error');
        });
    }

    showUploadedPhotos(uploadedPhotos) {
        const uploadedPanel = document.getElementById('uploaded-photos-panel');
        const container = document.getElementById('uploaded-photos-container');
        
        if (!uploadedPanel || !container) return;

        let html = '';
        uploadedPhotos.forEach(photo => {
            const thumbnail = photo.thumbnails?.small || photo.thumbnails?.medium || Object.values(photo.thumbnails || {})[0];
            
            html += `
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="thumbnail shadow-sm">
                        <img src="${thumbnail || '/images/placeholder.jpg'}" alt="${photo.title}" class="img-fluid rounded">
                        <div class="caption p-2 bg-light">
                            <h6 class="mb-1 text-truncate">${photo.title}</h6>
                            <div class="btn-group btn-group-sm d-flex">
                                <a href="/photos/update?id=${photo.id}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/photos/view?id=${photo.id}" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        uploadedPanel.style.display = 'block';
    }

    initUploadMoreButton(dropzone, uploadedPhotos) {
        const uploadMoreBtn = document.getElementById('upload-more-btn');
        const uploadedPanel = document.getElementById('uploaded-photos-panel');
        const container = document.getElementById('uploaded-photos-container');
        const submitAllBtn = document.getElementById('submit-all');
        
        if (uploadMoreBtn) {
            uploadMoreBtn.addEventListener('click', () => {
                dropzone.removeAllFiles();
                
                if (uploadedPanel) uploadedPanel.style.display = 'none';
                if (container) container.innerHTML = '';
                if (submitAllBtn) submitAllBtn.style.display = 'none';
                
                uploadedPhotos.length = 0;
            });
        }
    }

    cleanupUnwantedElements() {
        setTimeout(() => {
            const unwantedElements = document.querySelectorAll('body > input[type="file"], body > .file-input-wrapper');
            unwantedElements.forEach(el => el.remove());
        }, 300);
    }

    initSelect2(selectors, modal) {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            selectors.forEach(selector => {
                const element = document.querySelector(selector);
                if (element) {
                    const options = {
                        placeholder: selector.includes('tags') ? 'Wybierz lub wpisz tagi' : 'Wybierz...',
                        allowClear: true
                    };

                    if (modal) {
                        options.dropdownParent = $(modal);
                    }

                    if (selector.includes('tags')) {
                        options.tags = true;
                        options.tokenSeparators = [',', ' '];
                        options.createTag = (params) => {
                            const term = params.term.trim();
                            if (term === '') return null;
                            return {
                                id: term,
                                text: term,
                                newTag: true
                            };
                        };
                        options.templateResult = (data) => {
                            if (data.newTag) {
                                return $('<span><i class="fas fa-plus me-1"></i>Dodaj: <strong>' + data.text + '</strong></span>');
                            }
                            return data.text;
                        };
                    }

                    $(selector).select2(options);
                }
            });
        }
    }

    initAutoResize() {
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
    }

    initImagePreview() {
        const images = document.querySelectorAll('img[data-bs-toggle="modal"]');
        images.forEach(img => {
            img.style.cursor = 'pointer';
        });
    }

    initImageModal() {
        // Modal functionality is handled by Bootstrap
    }

    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    initFilters() {
        // Advanced filtering functionality could be added here
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PhotosController();
});