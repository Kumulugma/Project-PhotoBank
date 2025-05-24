/**
 * Photos Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class PhotosController {
    constructor() {
        this.init();
    }

    init() {
        // Initialize based on current page
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

    // Page detection methods
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

    // Common features for all photo pages
    initCommonFeatures() {
        this.initBatchOperations();
        this.initImagePreview();
        this.initTooltips();
    }

    // Photos index page functionality
    initIndexPage() {
        this.initQuickSearch();
        this.initBatchUpdateModal();
        this.initFilters();
    }

    // Quick search functionality
    initQuickSearch() {
        const quickSearchInput = document.getElementById('quick-search-code');
        const quickSearchBtn = document.getElementById('quick-search-btn');
        const searchStatus = document.getElementById('search-status');

        if (!quickSearchInput || !quickSearchBtn) return;

        // Auto uppercase
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

        // Visual feedback
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
            
            // Clear other filters
            const titleFilter = document.querySelector('input[name="PhotoSearch[title]"]');
            if (titleFilter) titleFilter.value = '';

            // Submit form
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

    // Batch operations
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

        // Initialize Select2 if available
        this.initSelect2(['#batch-categories', '#batch-tags'], '#batchUpdateModal');
    }

    // Photos queue page functionality
    initQueuePage() {
        this.initQueueBatchOperations();
    }

    initQueueBatchOperations() {
        const updateBatchButtons = this.initBatchOperations();

        // Batch approve
        const batchApproveSubmit = document.getElementById('batch-approve-submit');
        if (batchAppriveSubmit) {
            batchApproveSubmit.addEventListener('click', () => {
                const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                document.getElementById('approve-photo-ids').value = ids.join(',');
                document.getElementById('batch-approve-form').submit();
            });
        }

        // Batch delete
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

    // Photos update page functionality
    initUpdatePage() {
        this.initUpdateForm();
        this.initAiFields();
        this.initSelect2(['#photo-tags', '#photo-categories']);
        this.initAutoResize();
    }

    initUpdateForm() {
        // AI analysis form
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

    // Photos view page functionality
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

    // Photos upload page functionality
    initUploadPage() {
        // Dropzone functionality would be initialized here
        // Remove any unwanted file input wrappers
        setTimeout(() => {
            const unwantedElements = document.querySelectorAll('body > input[type="file"], body > .file-input-wrapper');
            unwantedElements.forEach(el => el.remove());
        }, 300);
    }

    // Common utility methods
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

    // Toast notifications
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PhotosController();
});