// Check if AlertsComponent already exists to prevent redeclaration
if (typeof window.AlertsComponent !== 'undefined') {
    console.warn('AlertsComponent already defined, skipping redefinition');
} else {
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
            this.initCollapsibleAlerts();
            this.initAlertActions();
        }

        initAutoHide() {
            const alerts = document.querySelectorAll('.alert[data-auto-hide]');
            
            alerts.forEach(alert => {
                const delay = parseInt(alert.dataset.autoHide) || this.getDefaultDelay(alert);
                
                setTimeout(() => {
                    this.hideAlert(alert);
                }, delay);
            });

            // Default auto-hide for specific types
            document.querySelectorAll('.alert-success:not([data-auto-hide])').forEach(alert => {
                setTimeout(() => this.hideAlert(alert), 5000);
            });
            
            document.querySelectorAll('.alert-info:not([data-auto-hide])').forEach(alert => {
                setTimeout(() => this.hideAlert(alert), 8000);
            });
        }

        getDefaultDelay(alert) {
            if (alert.classList.contains('alert-success')) return 5000;
            if (alert.classList.contains('alert-info')) return 8000;
            if (alert.classList.contains('alert-warning')) return 10000;
            if (alert.classList.contains('alert-danger')) return 0; // Don't auto-hide errors
            return 7000;
        }

        hideAlert(alert) {
            if (!alert || !alert.parentNode) return;
            
            alert.style.transition = 'all 0.4s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            alert.style.maxHeight = '0';
            alert.style.padding = '0';
            alert.style.margin = '0';
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 400);
        }

        initAlertAnimations() {
            const alerts = document.querySelectorAll('.alert');
            
            alerts.forEach((alert, index) => {
                // Stagger animations
                alert.style.animationDelay = (index * 0.1) + 's';
                alert.classList.add('alert-animate-in');
                
                // Add hover effects
                alert.addEventListener('mouseenter', () => {
                    alert.style.transform = 'translateY(-2px)';
                    alert.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                });
                
                alert.addEventListener('mouseleave', () => {
                    alert.style.transform = 'translateY(0)';
                    alert.style.boxShadow = '';
                });
            });
        }

        initProgressBars() {
            const progressAlerts = document.querySelectorAll('.alert-progress');
            
            progressAlerts.forEach(alert => {
                const duration = parseInt(alert.dataset.duration) || 5000;
                const progressBar = alert.querySelector('.progress-bar') || this.createProgressBar(alert);
                
                // Animate progress bar
                progressBar.style.width = '100%';
                progressBar.style.transition = `width ${duration}ms linear`;
                
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);
                
                setTimeout(() => {
                    this.hideAlert(alert);
                }, duration);
            });
        }

        createProgressBar(alert) {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'progress mt-2';
            progressContainer.style.height = '3px';
            
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-bar';
            progressBar.style.backgroundColor = 'currentColor';
            progressBar.style.opacity = '0.3';
            
            progressContainer.appendChild(progressBar);
            alert.appendChild(progressContainer);
            
            return progressBar;
        }

        initCollapsibleAlerts() {
            const collapsibleAlerts = document.querySelectorAll('.alert-collapsible');
            
            collapsibleAlerts.forEach(alert => {
                const toggle = alert.querySelector('.alert-toggle');
                const content = alert.querySelector('.alert-content');
                
                if (toggle && content) {
                    toggle.addEventListener('click', () => {
                        const isCollapsed = alert.classList.contains('collapsed');
                        
                        if (isCollapsed) {
                            alert.classList.remove('collapsed');
                            content.style.maxHeight = content.scrollHeight + 'px';
                        } else {
                            alert.classList.add('collapsed');
                            content.style.maxHeight = '0';
                        }
                    });
                    
                    // Initial state
                    if (alert.classList.contains('collapsed')) {
                        content.style.maxHeight = '0';
                    }
                }
            });
        }

        initAlertActions() {
            // Handle alert action buttons
            document.querySelectorAll('.alert .btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const alert = btn.closest('.alert');
                    const action = btn.dataset.action;
                    
                    switch (action) {
                        case 'dismiss':
                            e.preventDefault();
                            this.hideAlert(alert);
                            break;
                        case 'snooze':
                            e.preventDefault();
                            this.snoozeAlert(alert);
                            break;
                        case 'details':
                            this.toggleAlertDetails(alert);
                            break;
                    }
                });
            });
        }

        snoozeAlert(alert) {
            this.hideAlert(alert);
            
            // Show snooze notification
            this.showToast('Alert został wyciszony na 10 minut', 'info', 3000);
            
            // Re-show after 10 minutes
            setTimeout(() => {
                if (document.body.contains(alert)) return; // Already visible
                
                const newAlert = alert.cloneNode(true);
                newAlert.style.opacity = '0';
                newAlert.style.transform = 'translateX(100%)';
                
                document.body.appendChild(newAlert);
                
                setTimeout(() => {
                    newAlert.style.transition = 'all 0.4s ease-out';
                    newAlert.style.opacity = '1';
                    newAlert.style.transform = 'translateX(0)';
                }, 100);
            }, 10 * 60 * 1000);
        }

        toggleAlertDetails(alert) {
            let details = alert.querySelector('.alert-details');
            
            if (!details) {
                details = document.createElement('div');
                details.className = 'alert-details mt-2 pt-2 border-top';
                details.innerHTML = alert.dataset.details || 'Brak dodatkowych szczegółów.';
                alert.appendChild(details);
            }
            
            const isVisible = details.style.display !== 'none';
            details.style.display = isVisible ? 'none' : 'block';
        }

        static showToast(message, type = 'info', duration = 5000, options = {}) {
            const toast = document.createElement('div');
            const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            
            const position = options.position || 'top-right';
            const positionClasses = {
                'top-right': 'top-0 end-0',
                'top-left': 'top-0 start-0', 
                'bottom-right': 'bottom-0 end-0',
                'bottom-left': 'bottom-0 start-0',
                'top-center': 'top-0 start-50 translate-middle-x',
                'bottom-center': 'bottom-0 start-50 translate-middle-x'
            };
            
            toast.id = toastId;
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed ${positionClasses[position]}`;
            toast.style.cssText = `
                z-index: 9999; 
                min-width: 300px; 
                max-width: 500px;
                margin: 20px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                border: none;
                animation: slideInRight 0.4s ease-out;
            `;
            
            const iconMap = {
                success: 'check-circle',
                error: 'exclamation-circle', 
                warning: 'exclamation-triangle',
                info: 'info-circle',
                danger: 'exclamation-circle'
            };
            
            toast.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="fas fa-${iconMap[type] || 'info-circle'} me-2 mt-1"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Add to container or body
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            
            // Auto-hide
            if (duration > 0) {
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.style.animation = 'slideOutRight 0.4s ease-out';
                        setTimeout(() => toast.remove(), 400);
                    }
                }, duration);
            }
            
            // Handle close button
            const closeBtn = toast.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    toast.style.animation = 'slideOutRight 0.4s ease-out';
                    setTimeout(() => toast.remove(), 400);
                });
            }
            
            return toast;
        }

        static showConfirmDialog(message, title = 'Potwierdzenie', options = {}) {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-question-circle fa-2x text-warning me-3"></i>
                                    <div>${message}</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    ${options.cancelText || 'Anuluj'}
                                </button>
                                <button type="button" class="btn btn-${options.confirmType || 'primary'}" id="confirm-btn">
                                    ${options.confirmText || 'Potwierdź'}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                const confirmBtn = modal.querySelector('#confirm-btn');
                const cancelBtns = modal.querySelectorAll('[data-bs-dismiss="modal"]');
                
                confirmBtn.addEventListener('click', () => {
                    resolve(true);
                    if (typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                });
                
                cancelBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        resolve(false);
                    });
                });
                
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
                
                if (typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                }
            });
        }
    }

    // Store reference globally to prevent redefinition
    window.AlertsComponent = AlertsComponent;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.alertsComponentInstance) {
            window.alertsComponentInstance = new AlertsComponent();
        }
    });

    // Global functions
    window.showToast = AlertsComponent.showToast;
    window.showConfirmDialog = AlertsComponent.showConfirmDialog;

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        .alert-animate-in {
            animation: slideInRight 0.4s ease-out;
        }
        
        .drag-over {
            border-color: var(--bs-primary) !important;
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        
        .character-counter {
            font-size: 0.75rem;
            text-align: right;
        }
        
        .field-error-tooltip {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            margin-top: 0.25rem;
        }
        
        .file-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 2px dashed #ced4da;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .file-input-wrapper:hover {
            border-color: var(--bs-primary);
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .file-input-wrapper.drag-over {
            border-color: var(--bs-success);
            background-color: rgba(25, 135, 84, 0.1);
        }
    `;
    document.head.appendChild(style);
}