/**
 * Photos Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class PhotosController {
    constructor() {
        this.debugMode = window.location.hostname === 'localhost' || window.location.hostname.includes('dev');
        this.log('PhotosController initialized');
        
        this.initBatchOperations();
        this.initPjaxHandlers();
        this.initQuickSearch();
        this.initTooltips();
    }

    log(message, data = null) {
        if (this.debugMode) {
            console.log(`[PhotosController] ${message}`, data || '');
        }
    }

    initBatchOperations() {
        this.log('Initializing batch operations');
        this.setupBatchButtons();
        this.setupCheckboxHandlers();
        this.setupSelectAll();
        this.setupBatchModals();
    }

    initPjaxHandlers() {
        this.log('Setting up PJAX handlers');
        
        // Główna obsługa PJAX dla tabeli zdjęć
        $(document).on('pjax:complete', '#photos-grid-pjax', () => {
            this.log('PJAX completed - reinitializing');
            setTimeout(() => {
                this.initBatchOperations();
                this.initTooltips();
            }, 100);
        });

        // Dodatkowa obsługa dla pjax:end (bardziej niezawodne)
        $(document).on('pjax:end', '#photos-grid-pjax', () => {
            this.log('PJAX ended - reinitializing');
            setTimeout(() => {
                this.initBatchOperations();
            }, 50);
        });

        // Obsługa przed PJAX - ukryj przyciski wsadowe
        $(document).on('pjax:start', '#photos-grid-pjax', () => {
            this.log('PJAX started - hiding batch buttons');
            this.hideBatchButtons();
        });
    }

    initQuickSearch() {
        const quickSearchBtn = document.getElementById('quick-search-btn');
        const quickSearchInput = document.getElementById('quick-search-code');
        const searchStatus = document.getElementById('search-status');
        
        if (!quickSearchBtn || !quickSearchInput) return;

        // Obsługa przycisku wyszukiwania
        quickSearchBtn.addEventListener('click', () => {
            this.performQuickSearch(quickSearchInput.value, searchStatus);
        });

        // Obsługa Enter w polu wyszukiwania
        quickSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performQuickSearch(quickSearchInput.value, searchStatus);
            }
        });

        // Auto-uppercase dla kodów
        quickSearchInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
        });
    }

    initTooltips() {
        // Inicjalizacja tooltipów Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => {
                // Usuń stary tooltip jeśli istnieje
                const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                // Utwórz nowy
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    setupBatchButtons() {
        const batchButtons = document.querySelectorAll('.batch-action-btn');
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        
        this.log('Setting up batch buttons', { 
            buttons: batchButtons.length, 
            checked: checkedBoxes.length 
        });
        
        // Pokaż/ukryj przyciski w zależności od liczby zaznaczonych elementów
        this.updateBatchButtonsVisibility();

        // Obsługa kliknięć w przyciski wsadowe
        batchButtons.forEach(btn => {
            // Usuń poprzednie listenery (żeby nie dublować)
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });

        // Dodaj nowe listenery do zaktualizowanych przycisków
        document.querySelectorAll('.batch-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleBatchAction(e);
            });
        });
    }

    setupCheckboxHandlers() {
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        
        this.log('Setting up checkbox handlers', { count: checkboxes.length });
        
        checkboxes.forEach((checkbox, index) => {
            // Usuń poprzedni listener
            const newCheckbox = checkbox.cloneNode(true);
            checkbox.parentNode.replaceChild(newCheckbox, checkbox);
            
            // Dodaj nowy listener
            newCheckbox.addEventListener('change', (e) => {
                this.onCheckboxChange(e);
            });
        });
    }

    setupSelectAll() {
        const selectAllCheckbox = document.querySelector('input[name="selection_all"]');
        
        if (!selectAllCheckbox) {
            this.log('Select all checkbox not found');
            return;
        }

        this.log('Setting up select all checkbox');
        
        // Usuń poprzedni listener
        const newSelectAll = selectAllCheckbox.cloneNode(true);
        selectAllCheckbox.parentNode.replaceChild(newSelectAll, selectAllCheckbox);
        
        // Dodaj nowy listener
        newSelectAll.addEventListener('change', (e) => {
            this.onSelectAllChange(e);
        });
    }
    setupBatchModals() {
        // Obsługa submitów w modalach wsadowych
        const batchUpdateSubmit = document.getElementById('batch-update-submit');
        const batchDeleteSubmit = document.getElementById('batch-delete-submit');
        const batchApproveSubmit = document.getElementById('batch-approve-submit');

        if (batchUpdateSubmit) {
            batchUpdateSubmit.addEventListener('click', () => {
                this.submitBatchForm('batch-update-form');
            });
        }

        if (batchDeleteSubmit) {
            batchDeleteSubmit.addEventListener('click', () => {
                this.submitBatchForm('batch-delete-form');
            });
        }

        if (batchApproveSubmit) {
            batchApproveSubmit.addEventListener('click', () => {
                this.submitBatchForm('batch-approve-form');
            });
        }
    }

    onCheckboxChange = (e) => {
        this.log('Checkbox changed', { checked: e.target.checked, value: e.target.value });
        this.updateBatchButtonsVisibility();
        this.updateSelectAllState();
    }

    onSelectAllChange = (e) => {
        this.log('Select all changed', { checked: e.target.checked });
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
        });
        
        this.updateBatchButtonsVisibility();
    }

    updateBatchButtonsVisibility() {
        const batchButtons = document.querySelectorAll('.batch-action-btn');
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        
        this.log('Updating batch buttons visibility', { 
            buttons: batchButtons.length, 
            checked: checkedBoxes.length 
        });
        
        if (checkedBoxes.length > 0) {
            batchButtons.forEach(btn => {
                btn.style.display = 'inline-block';
                btn.style.opacity = '1';
                btn.style.transition = 'opacity 0.3s ease';
                btn.classList.remove('d-none');
            });
        } else {
            batchButtons.forEach(btn => {
                btn.style.display = 'none';
                btn.classList.add('d-none');
            });
        }

        // Aktualizuj liczniki w przyciskach jeśli istnieją
        batchButtons.forEach(btn => {
            const countSpan = btn.querySelector('.selection-count');
            if (countSpan) {
                countSpan.textContent = checkedBoxes.length;
            }
            
            // Aktualizuj tekst przycisku z liczbą
            const buttonText = btn.textContent;
            if (buttonText.includes('zaznaczone')) {
                const baseText = buttonText.split('(')[0].trim();
                btn.innerHTML = btn.innerHTML.replace(/\(\d+\)/, `(${checkedBoxes.length})`);
                if (!buttonText.includes('(')) {
                    btn.innerHTML += ` (${checkedBoxes.length})`;
                }
            }
        });
    }

    hideBatchButtons() {
        const batchButtons = document.querySelectorAll('.batch-action-btn');
        batchButtons.forEach(btn => {
            btn.style.display = 'none';
            btn.classList.add('d-none');
        });
    }

    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector('input[name="selection_all"]');
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        
        if (!selectAllCheckbox) return;
        
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    handleBatchAction(e) {
        const button = e.target.closest('.batch-action-btn');
        const action = button.getAttribute('data-action') || button.getAttribute('data-bs-target');
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        
        this.log('Handling batch action', { action, selectedCount: checkedBoxes.length });
        
        if (checkedBoxes.length === 0) {
            this.showAlert('Nie wybrano żadnych zdjęć do operacji wsadowej.', 'warning');
            return;
        }

        // Wypełnij hidden input z ID zdjęć w modalach
        const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        // Znajdź odpowiedni modal i wypełnij pole ids
        if (action && action.includes('Modal')) {
            const modalId = action.replace('#', '');
            const modal = document.querySelector('#' + modalId);
            
            if (modal) {
                const idsInput = modal.querySelector('input[name="ids"]');
                if (idsInput) {
                    idsInput.value = selectedIds.join(',');
                    this.log('Updated modal IDs input', { modalId, ids: selectedIds });
                }
                
                // Aktualizuj komunikaty w modalu
                const countSpans = modal.querySelectorAll('.selected-count');
                countSpans.forEach(span => {
                    span.textContent = selectedIds.length;
                });

                // Aktualizuj listy w modalu jeśli istnieją
                const selectedList = modal.querySelector('.selected-items-list');
                if (selectedList) {
                    this.updateSelectedItemsList(selectedList, selectedIds);
                }
            }
        }
    }

    submitBatchForm(formId) {
        const form = document.getElementById(formId);
        const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
        
        if (!form) {
            this.log('Form not found', { formId });
            return;
        }

        if (checkedBoxes.length === 0) {
            this.showAlert('Nie wybrano żadnych zdjęć do operacji.', 'warning');
            return;
        }

        const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
        const idsInput = form.querySelector('input[name="ids"]');
        
        if (idsInput) {
            idsInput.value = selectedIds.join(',');
        }

        this.log('Submitting batch form', { formId, selectedIds });
        form.submit();
    }

    updateSelectedItemsList(listElement, selectedIds) {
        // Opcjonalna funkcja do aktualizacji listy wybranych elementów w modalu
        if (!listElement) return;
        
        const items = selectedIds.map(id => {
            const checkbox = document.querySelector(`input[name="selection[]"][value="${id}"]`);
            const row = checkbox ? checkbox.closest('tr') : null;
            const titleCell = row ? row.querySelector('td:nth-child(4)') : null; // Zakładając że tytuł jest w 4. kolumnie
            const title = titleCell ? titleCell.textContent.trim() : `ID: ${id}`;
            
            return `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${title}</span>
                        <span class="badge bg-secondary">${id}</span>
                    </li>`;
        }).join('');
        
        listElement.innerHTML = `<ul class="list-group list-group-flush">${items}</ul>`;
    }
    performQuickSearch(searchCode, statusElement) {
        if (!searchCode || searchCode.length < 3) {
            this.updateSearchStatus(statusElement, 'Wprowadź co najmniej 3 znaki', 'warning');
            return;
        }

        // Wyczyść kod wyszukiwania
        searchCode = searchCode.toUpperCase().trim();
        
        this.log('Performing quick search', { searchCode });
        this.updateSearchStatus(statusElement, 'Wyszukiwanie...', 'info');

        // Jeśli to pełny kod (12 znaków), przekieruj bezpośrednio
        if (searchCode.length === 12) {
            this.searchByFullCode(searchCode, statusElement);
        } else {
            // Filtruj aktualną listę
            this.filterCurrentList(searchCode, statusElement);
        }
    }

    searchByFullCode(searchCode, statusElement) {
        // AJAX wyszukiwanie po pełnym kodzie
        fetch(`/photos/find-by-code?code=${encodeURIComponent(searchCode)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateSearchStatus(statusElement, 'Znaleziono! Przekierowywanie...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                this.updateSearchStatus(statusElement, data.message || 'Nie znaleziono zdjęcia', 'danger');
            }
        })
        .catch(error => {
            this.log('Search error', error);
            this.updateSearchStatus(statusElement, 'Błąd wyszukiwania', 'danger');
        });
    }

    filterCurrentList(searchCode, statusElement) {
        const rows = document.querySelectorAll('#photos-grid-pjax tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const codeCell = row.querySelector('td:first-child code, td:nth-child(2) code');
            const titleCell = row.querySelector('td:nth-child(4), td:nth-child(5)');
            
            const code = codeCell ? codeCell.textContent.trim() : '';
            const title = titleCell ? titleCell.textContent.trim() : '';
            
            const matchesCode = code.includes(searchCode);
            const matchesTitle = title.toLowerCase().includes(searchCode.toLowerCase());
            
            if (matchesCode || matchesTitle) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (visibleCount > 0) {
            this.updateSearchStatus(statusElement, `Znaleziono ${visibleCount} pasujących zdjęć`, 'success');
        } else {
            this.updateSearchStatus(statusElement, 'Brak wyników. Spróbuj innego kodu.', 'warning');
        }
        
        // Ukryj przyciski wsadowe podczas filtrowania
        this.hideBatchButtons();
    }

    updateSearchStatus(statusElement, message, type) {
        if (!statusElement) return;
        
        const iconClass = {
            'info': 'fa-search',
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'danger': 'fa-times-circle'
        };
        
        const icon = iconClass[type] || 'fa-info-circle';
        statusElement.innerHTML = `<i class="fas ${icon} me-1"></i>${message}`;
        statusElement.className = `form-text text-${type}`;
        
        // Automatycznie ukryj po 5 sekundach dla sukcesów i błędów
        if (type === 'success' || type === 'danger') {
            setTimeout(() => {
                statusElement.innerHTML = '';
                statusElement.className = 'form-text';
            }, 5000);
        }
    }

    showAlert(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const iconClass = {
            'success': 'fa-check-circle',
            'danger': 'fa-times-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass[type]} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        alert.innerHTML = `
            <i class="fas ${iconClass[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remove po 5 sekundach
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    // Funkcje pomocnicze do debugowania
    getDebugInfo() {
        return {
            checkboxes: document.querySelectorAll('input[name="selection[]"]').length,
            checked: document.querySelectorAll('input[name="selection[]"]:checked').length,
            batchButtons: document.querySelectorAll('.batch-action-btn').length,
            visibleBatchButtons: document.querySelectorAll('.batch-action-btn[style*="inline-block"]').length,
            selectAll: !!document.querySelector('input[name="selection_all"]'),
            pjaxContainer: !!document.querySelector('#photos-grid-pjax')
        };
    }

    // Publiczna metoda do ponownej inicjalizacji
    reinitialize() {
        this.log('Manual reinitialize called');
        this.initBatchOperations();
        this.initTooltips();
    }
}

// Funkcje globalne dla debugowania (dostępne w konsoli)
window.debugPhotos = function() {
    if (window.photosController) {
        console.table(window.photosController.getDebugInfo());
    } else {
        console.warn('PhotosController not initialized');
    }
};

window.reinitializePhotos = function() {
    if (window.photosController) {
        window.photosController.reinitialize();
        console.log('PhotosController reinitialized');
    } else {
        console.warn('PhotosController not initialized');
    }
};

// Inicjalizacja główna
let photosController;

// Uruchom przy załadowaniu DOM
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded - initializing PhotosController');
    photosController = new PhotosController();
    
    // Ustaw globalną referencję
    window.photosController = photosController;
    
    // Debug info w trybie rozwoju
    if (photosController.debugMode) {
        console.log('PhotosController ready. Use debugPhotos() for info.');
        window.debugPhotos();
    }
});

// Dodatkowa inicjalizacja po jQuery ready (fallback)
$(document).ready(function() {
    console.log('jQuery ready - ensuring PhotosController is initialized');
    
    // Jeśli controller jeszcze nie istnieje, utwórz go
    if (!window.photosController) {
        window.photosController = new PhotosController();
    }
    
    // Obsługa uniwersalna dla wszystkich PJAX kontenerów
    $(document).on('pjax:complete', function(event) {
        const containerId = event.target.id;
        console.log(`PJAX complete for container: ${containerId}`);
        
        // Tylko dla kontenerów związanych ze zdjęciami
        if (containerId.includes('photos') || containerId.includes('grid')) {
            setTimeout(() => {
                if (window.photosController) {
                    window.photosController.reinitialize();
                }
            }, 100);
        }
    });
    
    // Obsługa błędów PJAX
    $(document).on('pjax:error', function(event, xhr, textStatus, error) {
        console.error('PJAX error:', textStatus, error);
        if (window.photosController) {
            window.photosController.showAlert('Błąd ładowania strony. Odśwież stronę.', 'danger');
        }
    });
});

// Export dla modułów (jeśli używane)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PhotosController;
}