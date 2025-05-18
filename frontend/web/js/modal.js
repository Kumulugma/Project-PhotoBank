/**
 * Modal JavaScript Module
 * Handles all modal functionality with accessibility features
 */

class ModalManager {
    constructor() {
        this.openModals = [];
        this.focusStack = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupAriaAttributes();
    }

    bindEvents() {
        // Modal trigger buttons
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-target]');
            if (trigger) {
                e.preventDefault();
                const targetId = trigger.dataset.modalTarget;
                const modal = document.getElementById(targetId);
                if (modal) {
                    this.openModal(modal);
                }
            }
        });

        // Close buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close, [data-modal-close]')) {
                e.preventDefault();
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.closeModal(modal);
                }
            }
        });

        // Backdrop clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.openModals.length > 0) {
                const topModal = this.openModals[this.openModals.length - 1];
                this.closeModal(topModal);
            }
        });

        // Handle form submissions within modals
        document.addEventListener('submit', (e) => {
            const modal = e.target.closest('.modal');
            if (modal && modal.classList.contains('active')) {
                this.handleModalFormSubmit(e, modal);
            }
        });
    }

    setupAriaAttributes() {
        document.querySelectorAll('.modal').forEach(modal => {
            // Set initial ARIA attributes
            modal.setAttribute('aria-hidden', 'true');
            modal.setAttribute('aria-modal', 'true');

            if (!modal.getAttribute('role')) {
                modal.setAttribute('role', 'dialog');
            }

            // Ensure modal has a label
            if (!modal.getAttribute('aria-labelledby') && !modal.getAttribute('aria-label')) {
                const title = modal.querySelector('.modal-title');
                if (title) {
                    const titleId = title.id || `modal-title-${Date.now()}`;
                    title.id = titleId;
                    modal.setAttribute('aria-labelledby', titleId);
                } else {
                    modal.setAttribute('aria-label', 'Dialog');
                }
            }
        });
    }

    openModal(modal) {
        if (!modal || modal.classList.contains('active'))
            return;

        // Store currently focused element
        this.focusStack.push(document.activeElement);

        // Add to open modals stack
        this.openModals.push(modal);

        // Show modal
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        // Prevent body scroll
        this.updateBodyScroll();

        // Focus management
        this.setInitialFocus(modal);

        // Trap focus within modal
        this.trapFocus(modal);

        // Announce to screen readers
        this.announceModal(modal, 'opened');

        // Trigger custom event
        modal.dispatchEvent(new CustomEvent('modal:opened', {
            detail: {modal}
        }));

        // Handle specific modal types
        this.handleModalType(modal);
    }
    closeModal(modal) {
        if (!modal || !modal.classList.contains('active'))
            return;

        // Remove from open modals stack
        const index = this.openModals.indexOf(modal);
        if (index > -1) {
            this.openModals.splice(index, 1);
        }

        // Hide modal
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');

        // Update body scroll
        this.updateBodyScroll();

        // Restore focus
        this.restoreFocus();

        // Announce to screen readers
        this.announceModal(modal, 'closed');

        // Trigger custom event
        modal.dispatchEvent(new CustomEvent('modal:closed', {
            detail: {modal}
        }));

        // Clean up modal-specific handlers
        this.cleanupModal(modal);
    }

    closeAllModals() {
        [...this.openModals].forEach(modal => {
            this.closeModal(modal);
        });
    }

    updateBodyScroll() {
        if (this.openModals.length > 0) {
            document.body.classList.add('modal-open');
            // Prevent scroll jumping by calculating scrollbar width
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.paddingRight = `${scrollbarWidth}px`;
        } else {
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }
    }

    setInitialFocus(modal) {
        // Try to focus on the first focusable element with autofocus
        let focusTarget = modal.querySelector('[autofocus]');

        // If no autofocus, try the first input element
        if (!focusTarget) {
            focusTarget = modal.querySelector('input:not([type="hidden"]), textarea, select');
        }

        // If no input, try the primary action button
        if (!focusTarget) {
            focusTarget = modal.querySelector('.btn-primary, .modal-primary-action');
        }

        // If no primary action, try the close button
        if (!focusTarget) {
            focusTarget = modal.querySelector('.modal-close');
        }

        // If nothing else, focus the modal itself
        if (!focusTarget) {
            focusTarget = modal;
            modal.setAttribute('tabindex', '-1');
        }

        setTimeout(() => {
            if (focusTarget) {
                focusTarget.focus();
            }
        }, 100);
    }

    restoreFocus() {
        const previousFocus = this.focusStack.pop();
        if (previousFocus && document.contains(previousFocus)) {
            setTimeout(() => {
                previousFocus.focus();
            }, 100);
        }
    }

    trapFocus(modal) {
        const focusableElements = this.getFocusableElements(modal);
        if (focusableElements.length === 0)
            return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const handleTabKey = (e) => {
            if (e.key !== 'Tab')
                return;

            if (e.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        modal.addEventListener('keydown', handleTabKey);

        // Store handler for cleanup
        modal._focusTrapHandler = handleTabKey;
    }

    getFocusableElements(container) {
        const selector = [
            'a[href]',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([type="hidden"]):not([disabled])',
            'select:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
            '[contenteditable="true"]'
        ].join(', ');

        return Array.from(container.querySelectorAll(selector))
                .filter(el => {
                    return el.offsetWidth > 0 && el.offsetHeight > 0 &&
                            getComputedStyle(el).visibility !== 'hidden';
                });
    }

    announceModal(modal, action) {
        const statusRegion = document.getElementById('aria-status');
        if (!statusRegion)
            return;

        const titleElement = modal.querySelector('.modal-title');
        const title = titleElement ? titleElement.textContent : 'Dialog';
        const message = action === 'opened'
                ? `${title} otwarte`
                : `${title} zamknięte`;

        statusRegion.textContent = message;
        setTimeout(() => {
            statusRegion.textContent = '';
        }, 1000);
    }

    handleModalType(modal) {
        // Photo modal specific handling
        if (modal.id === 'photoModal') {
            this.handlePhotoModal(modal);
        }

        // Confirmation modal handling
        if (modal.classList.contains('modal-confirm')) {
            this.handleConfirmModal(modal);
        }

        // Form modal handling
        if (modal.querySelector('form')) {
            this.handleFormModal(modal);
        }
    }

    handlePhotoModal(modal) {
        // Add keyboard navigation for photo modal
        const prevBtn = modal.querySelector('.modal-prev, [data-action="prev"]');
        const nextBtn = modal.querySelector('.modal-next, [data-action="next"]');

        const keyHandler = (e) => {
            switch (e.key) {
                case 'ArrowLeft':
                    if (prevBtn && !prevBtn.disabled) {
                        e.preventDefault();
                        prevBtn.click();
                    }
                    break;
                case 'ArrowRight':
                    if (nextBtn && !nextBtn.disabled) {
                        e.preventDefault();
                        nextBtn.click();
                    }
                    break;
            }
        };

        modal.addEventListener('keydown', keyHandler);
        modal._photoKeyHandler = keyHandler;

        // Update image alt text for accessibility
        const img = modal.querySelector('img');
        if (img) {
            img.addEventListener('load', () => {
                const titleElement = modal.querySelector('.modal-title');
                const title = titleElement ? titleElement.textContent : '';
                if (title && !img.alt) {
                    img.alt = title;
                }
            });
        }
    }

    handleConfirmModal(modal) {
        // Auto-focus the primary action button in confirmation modals
        const primaryBtn = modal.querySelector('.btn-primary, .btn-danger, [data-action="confirm"]');
        if (primaryBtn) {
            setTimeout(() => primaryBtn.focus(), 100);
        }

        // Handle Enter key for confirmation
        const confirmHandler = (e) => {
            if (e.key === 'Enter' && document.activeElement !== modal.querySelector('.btn-secondary')) {
                const confirmBtn = modal.querySelector('[data-action="confirm"]');
                if (confirmBtn) {
                    confirmBtn.click();
                }
            }
        };

        modal.addEventListener('keydown', confirmHandler);
        modal._confirmHandler = confirmHandler;
    }
    async submitModalFormAjax(form, modal) {
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Success - close modal and show message
                this.closeModal(modal);
                if (result.message) {
                    this.showNotification(result.message, 'success');
                }

                // Trigger success event
                modal.dispatchEvent(new CustomEvent('modal:form-success', {
                    detail: {result, form, modal}
                }));

                // Redirect if specified
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                }
            } else {
                // Error - show errors in form
                if (result.errors) {
                    this.displayFormErrors(form, result.errors);
                }
                if (result.message) {
                    this.showNotification(result.message, 'error');
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('Wystąpił błąd podczas wysyłania formularza', 'error');
        } finally {
            this.setModalLoading(modal, false);
        }
    }

    displayFormErrors(form, errors) {
        // Clear existing errors
        form.querySelectorAll('.field-error').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // Display new errors
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName]);
            }
        });

        // Focus first error
        this.focusFirstError(form);
    }

    cleanupModal(modal) {
        // Remove event handlers specific to modal types
        if (modal._focusTrapHandler) {
            modal.removeEventListener('keydown', modal._focusTrapHandler);
            delete modal._focusTrapHandler;
        }

        if (modal._photoKeyHandler) {
            modal.removeEventListener('keydown', modal._photoKeyHandler);
            delete modal._photoKeyHandler;
        }

        if (modal._confirmHandler) {
            modal.removeEventListener('keydown', modal._confirmHandler);
            delete modal._confirmHandler;
        }

        // Reset loading state
        this.setModalLoading(modal, false);
    }

    // Utility methods
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    showNotification(message, type = 'info') {
        // Use global notification system if available
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
    }
    }

    // Public API methods
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            this.openModal(modal);
        }
    }

    close(modalId = null) {
        if (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                this.closeModal(modal);
            }
        } else {
            this.closeAllModals();
    }
    }

    toggle(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.classList.contains('active')) {
                this.closeModal(modal);
            } else {
                this.openModal(modal);
            }
        }
    }

    isOpen(modalId = null) {
        if (modalId) {
            const modal = document.getElementById(modalId);
            return modal && modal.classList.contains('active');
        }
        return this.openModals.length > 0;
    }

    getOpenModals() {
        return [...this.openModals];
    }

    // Confirmation modal helper
    confirm(options = {}) {
        return new Promise((resolve) => {
            const modalId = `confirm-modal-${Date.now()}`;
            const modal = this.createConfirmModal(modalId, options);

            document.body.appendChild(modal);

            const handleConfirm = () => {
                this.closeModal(modal);
                modal.remove();
                resolve(true);
            };

            const handleCancel = () => {
                this.closeModal(modal);
                modal.remove();
                resolve(false);
            };

            modal.querySelector('[data-action="confirm"]').addEventListener('click', handleConfirm);
            modal.querySelector('[data-action="cancel"]').addEventListener('click', handleCancel);

            this.openModal(modal);
        });
    }

    createConfirmModal(id, options) {
        const {
            title = 'Potwierdzenie',
            message = 'Czy na pewno chcesz kontynuować?',
            confirmText = 'Tak',
            cancelText = 'Anuluj',
            type = 'primary' // primary, danger, warning
        } = options;

        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal modal-confirm';
        modal.setAttribute('role', 'alertdialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', `${id}-title`);

        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="${id}-title">${title}</h4>
                        <button type="button" class="modal-close" data-action="cancel" aria-label="Zamknij">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-action="cancel">
                            ${cancelText}
                        </button>
                        <button type="button" class="btn btn-${type === 'danger' ? 'danger' : 'primary'}" data-action="confirm">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    // Alert modal helper
    alert(options = {}) {
        return new Promise((resolve) => {
            const modalId = `alert-modal-${Date.now()}`;
            const modal = this.createAlertModal(modalId, options);

            document.body.appendChild(modal);

            const handleOk = () => {
                this.closeModal(modal);
                modal.remove();
                resolve();
            };

            modal.querySelector('[data-action="ok"]').addEventListener('click', handleOk);

            this.openModal(modal);
        });
    }

    createAlertModal(id, options) {
        const {
            title = 'Informacja',
            message = '',
            okText = 'OK',
            type = 'info' // info, success, warning, error
        } = options;

        const iconClass = {
            info: 'fa-info-circle',
            success: 'fa-check-circle',
            warning: 'fa-exclamation-triangle',
            error: 'fa-times-circle'
        }[type];

        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal modal-alert';
        modal.setAttribute('role', 'alertdialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', `${id}-title`);

        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="${id}-title">
                            <i class="fas ${iconClass}" aria-hidden="true"></i>
                            ${title}
                        </h4>
                        <button type="button" class="modal-close" data-action="ok" aria-label="Zamknij">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-action="ok">
                            ${okText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    destroy() {
        this.closeAllModals();
        this.openModals = [];
        this.focusStack = [];
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.modalManager = new ModalManager();
});

// Global API functions for backward compatibility
window.openModal = (modal) => {
    const modalEl = typeof modal === 'string' ? document.getElementById(modal) : modal;
    if (modalEl && window.modalManager) {
        window.modalManager.openModal(modalEl);
    }
};

window.closeModal = (modal) => {
    const modalEl = typeof modal === 'string' ? document.getElementById(modal) : modal;
    if (modalEl && window.modalManager) {
        window.modalManager.closeModal(modalEl);
    }
};

window.confirmDialog = (options) => {
    if (window.modalManager) {
        return window.modalManager.confirm(options);
    }
    return Promise.resolve(confirm(options.message || 'Czy na pewno chcesz kontynuować?'));
};

window.alertDialog = (options) => {
    if (window.modalManager) {
        return window.modalManager.alert(options);
    }
    return Promise.resolve(alert(options.message || ''));
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModalManager;
}