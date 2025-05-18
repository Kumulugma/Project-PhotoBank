/**
 * Admin Panel JavaScript
 * Zasobnik B - Photo Management System
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initDropdowns();
        initAlerts();
        initDataTables();
        initFileUploads();
        initFormValidation();
    });

    /**
     * Initialize sidebar functionality
     */
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const mobileToggle = document.querySelector('.mobile-toggle');
        const submenuToggles = document.querySelectorAll('.has-submenu > .nav-link');

        // Desktop sidebar toggle
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

        // Mobile sidebar toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // Submenu toggles
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('open');
            });
        });

        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    /**
     * Initialize dropdown functionality
     */
    function initDropdowns() {
        const dropdowns = document.querySelectorAll('.admin-dropdown');
        
        dropdowns.forEach(function(dropdown) {
            const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdown.classList.toggle('show');
                    menu.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('show');
                        menu.classList.remove('show');
                    }
                });
            }
        });
    }

    /**
     * Initialize alerts
     */
    function initAlerts() {
        const alerts = document.querySelectorAll('.admin-alert');
        
        alerts.forEach(function(alert) {
            // Auto-hide after 5 seconds
            setTimeout(function() {
                hideAlert(alert);
            }, 5000);
            
            // Close button
            const closeBtn = alert.querySelector('.alert-close, .btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    hideAlert(alert);
                });
            }
        });
    }

    /**
     * Hide alert with animation
     */
    function hideAlert(alert) {
        alert.style.transition = 'opacity 0.3s ease';
        alert.style.opacity = '0';
        setTimeout(function() {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }

    /**
     * Initialize DataTables if available
     */
    function initDataTables() {
        if (typeof DataTable !== 'undefined') {
            document.querySelectorAll('.data-table').forEach(function(table) {
                new DataTable(table, {
                    responsive: true,
                    language: {
                        url: '/js/datatables-pl.json'
                    }
                });
            });
        }
    }

    /**
     * Initialize file uploads
     */
    function initFileUploads() {
        // File input styling
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            const wrapper = document.createElement('div');
            wrapper.className = 'file-input-wrapper';
            
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary';
            button.textContent = 'Wybierz plik';
            
            const label = document.createElement('span');
            label.className = 'file-input-label';
            label.textContent = 'Nie wybrano pliku';
            
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(button);
            wrapper.appendChild(label);
            wrapper.appendChild(input);
            
            button.addEventListener('click', function() {
                input.click();
            });
            
            input.addEventListener('change', function() {
                if (input.files.length > 0) {
                    const fileName = input.files[0].name;
                    label.textContent = fileName;
                } else {
                    label.textContent = 'Nie wybrano pliku';
                }
            });
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    /**
     * Show loading overlay
     */
    window.showLoading = function() {
        const template = document.getElementById('loading-template');
        if (template) {
            const overlay = document.createElement('div');
            overlay.innerHTML = template.innerHTML;
            overlay.className = 'admin-loading-overlay';
            document.body.appendChild(overlay);
        }
    };

    /**
     * Hide loading overlay
     */
    window.hideLoading = function() {
        const overlay = document.querySelector('.admin-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    };

    /**
     * AJAX helper
     */
    window.ajaxRequest = function(url, options) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            defaults.headers['X-CSRF-Token'] = csrfToken.getAttribute('content');
        }
        
        const config = Object.assign({}, defaults, options);
        
        showLoading();
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .finally(() => {
                hideLoading();
            });
    };

    /**
     * Confirm dialog
     */
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    /**
     * Show toast notification
     */
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                ${message}
                <button type="button" class="toast-close" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Show animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Auto-hide
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', function() {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    };

})();