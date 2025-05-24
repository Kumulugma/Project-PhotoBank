// backend/web/js/components/modals.js
/**
 * Modal Components JavaScript
 */

class ModalsComponent {
    constructor() {
        this.init();
    }

    init() {
        this.initModalTriggers();
        this.initModalEvents();
        this.initConfirmDialogs();
    }

    initModalTriggers() {
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                const targetModal = document.querySelector(trigger.getAttribute('data-bs-target'));
                if (targetModal) {
                    this.prepareModal(targetModal, trigger);
                }
            });
        });
    }

    initModalEvents() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                const firstInput = modal.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    initConfirmDialogs() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    prepareModal(modal, trigger) {
        // Modal-specific preparation based on trigger data
        const dataId = trigger.getAttribute('data-id');
        if (dataId) {
            const idInput = modal.querySelector('input[name="id"]');
            if (idInput) {
                idInput.value = dataId;
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ModalsComponent();
});

// backend/web/js/components/forms.js
/**
 * Forms Components JavaScript
 */

class FormsComponent {
    constructor() {
        this.init();
    }

    init() {
        this.initValidation();
        this.initAutoResize();
        this.initFileInputs();
        this.initFormSubmission();
    }

    initValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                form.classList.add('was-validated');
            });
        });
    }

    initAutoResize() {
        const textareas = document.querySelectorAll('textarea[data-auto-resize]');
        textareas.forEach(textarea => {
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };
            
            textarea.addEventListener('input', resize);
            resize(); // Initial resize
        });
    }

    initFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"]:not(.processed)');
        
        fileInputs.forEach(input => {
            input.classList.add('processed');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'file-input-wrapper';
            
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary';
            button.innerHTML = '<i class="fas fa-file me-2"></i>Wybierz plik';
            
            const label = document.createElement('span');
            label.className = 'file-input-label ms-2';
            label.textContent = 'Nie wybrano pliku';
            
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(button);
            wrapper.appendChild(label);
            wrapper.appendChild(input);
            
            button.addEventListener('click', () => input.click());
            
            input.addEventListener('change', () => {
                if (input.files.length > 0) {
                    const fileName = input.files[0].name;
                    label.innerHTML = '<i class="fas fa-check text-success me-1"></i>' + fileName;
                } else {
                    label.textContent = 'Nie wybrano pliku';
                }
            });
        });
    }

    initFormSubmission() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML || submitBtn.value;
                    
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Przetwarzanie...';
                    } else {
                        submitBtn.value = 'Przetwarzanie...';
                    }
                    
                    submitBtn.disabled = true;
                    
                    // Re-enable after delay (in case of validation errors)
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        if (submitBtn.tagName === 'BUTTON') {
                            submitBtn.innerHTML = originalText;
                        } else {
                            submitBtn.value = originalText;
                        }
                    }, 5000);
                }
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new FormsComponent();
});

// backend/web/js/components/tables.js
/**
 * Tables Components JavaScript
 */

class TablesComponent {
    constructor() {
        this.init();
    }

    init() {
        this.initSortableHeaders();
        this.initRowSelection();
        this.initResponsiveTables();
        this.initImagePreviews();
    }

    initSortableHeaders() {
        document.querySelectorAll('th.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const link = header.querySelector('a');
                if (link) {
                    link.click();
                }
            });
        });
    }

    initRowSelection() {
        const selectAllCheckbox = document.querySelector('th input[type="checkbox"]');
        const rowCheckboxes = document.querySelectorAll('td input[type="checkbox"]');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                this.updateBatchActions();
            });
        }
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectAllState();
                this.updateBatchActions();
            });
        });
    }

    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector('th input[type="checkbox"]');
        const rowCheckboxes = document.querySelectorAll('td input[type="checkbox"]');
        
        if (selectAllCheckbox && rowCheckboxes.length > 0) {
            const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
            
            selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
        }
    }

    updateBatchActions() {
        const checkedBoxes = document.querySelectorAll('td input[type="checkbox"]:checked');
        const batchActions = document.querySelectorAll('.batch-action-btn, .batch-actions');
        
        batchActions.forEach(action => {
            if (checkedBoxes.length > 0) {
                action.style.display = 'inline-block';
                action.classList.add('show');
            } else {
                action.style.display = 'none';
                action.classList.remove('show');
            }
        });
        
        // Update counters
        const counters = document.querySelectorAll('.selection-counter');
        counters.forEach(counter => {
            counter.textContent = checkedBoxes.length;
        });
    }

    initResponsiveTables() {
        const tables = document.querySelectorAll('.table-responsive');
        
        tables.forEach(tableWrapper => {
            const table = tableWrapper.querySelector('table');
            if (table) {
                this.makeTableResponsive(table);
            }
        });
    }

    makeTableResponsive(table) {
        // Add responsive behaviors
        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');
        
        // Store header texts for mobile view
        headers.forEach((header, index) => {
            const headerText = header.textContent.trim();
            rows.forEach(row => {
                const cell = row.cells[index];
                if (cell) {
                    cell.setAttribute('data-label', headerText);
                }
            });
        });
    }

    initImagePreviews() {
        const images = document.querySelectorAll('.table img');
        
        images.forEach(img => {
            img.addEventListener('mouseenter', () => {
                img.style.transform = 'scale(1.1)';
                img.style.zIndex = '10';
                img.style.position = 'relative';
                img.style.transition = 'transform 0.2s ease';
            });
            
            img.addEventListener('mouseleave', () => {
                img.style.transform = 'scale(1)';
                img.style.zIndex = 'auto';
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new TablesComponent();
});

// backend/web/js/components/alerts.js
/**
 * Alerts Components JavaScript
 */

class AlertsComponent {
    constructor() {
        this.init();
    }

    init() {
        this.initAutoHide();
        this.initAlertAnimations();
        this.initProgressBars();
    }

    initAutoHide() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-success')) {
                setTimeout(() => this.hideAlert(alert), 5000);
            } else if (alert.classList.contains('alert-info')) {
                setTimeout(() => this.hideAlert(alert), 10000);
            }
        });
    }

    hideAlert(alert) {
        if (!alert || !alert.parentNode) return;
        
        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }

    initAlertAnimations() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach((alert, index) => {
            alert.style.animationDelay = (index * 0.1) + 's';
        });
    }

    initProgressBars() {
        const progressAlerts = document.querySelectorAll('.alert-progress');
        
        progressAlerts.forEach(alert => {
            const duration = alert.dataset.duration || 5000;
            
            setTimeout(() => {
                this.hideAlert(alert);
            }, parseInt(duration));
        });
    }

    static showToast(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease;';
        
        const iconMap = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        toast.innerHTML = `
            <i class="fas fa-${iconMap[type] || 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
        
        return toast;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new AlertsComponent();
});

// Global toast function
window.showToast = AlertsComponent.showToast;