/**
 * Admin Panel JavaScript for Bootstrap 5
 * Zasobnik B - Photo Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initAlerts();
    initFileUploads();
    initFormValidation();
    initTooltips();
});

/**
 * Initialize sidebar functionality
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    
    // Toggle sidebar on mobile
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close sidebar
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
}

/**
 * Initialize alerts
 */
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // Auto-hide success alerts after 5 seconds
        if (alert.classList.contains('alert-success')) {
            setTimeout(function() {
                hideAlert(alert);
            }, 5000);
        }
        
        // Auto-hide info alerts after 10 seconds
        if (alert.classList.contains('alert-info')) {
            setTimeout(function() {
                hideAlert(alert);
            }, 10000);
        }
    });
}

/**
 * Hide alert with animation
 */
function hideAlert(alert) {
    if (!alert) return;
    
    alert.style.transition = 'opacity 0.3s ease';
    alert.style.opacity = '0';
    setTimeout(function() {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 300);
}

/**
 * Initialize file uploads with better UX
 */
function initFileUploads() {
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        if (input.closest('.file-input-wrapper')) return; // Skip if already wrapped
        
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
        
        button.addEventListener('click', function() {
            input.click();
        });
        
        input.addEventListener('change', function() {
            if (input.files.length > 0) {
                const fileName = input.files[0].name;
                label.innerHTML = '<i class="fas fa-check text-success me-1"></i>' + fileName;
            } else {
                label.textContent = 'Nie wybrano pliku';
            }
        });
        
        // Drag and drop support
        wrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            wrapper.classList.add('drag-over');
        });
        
        wrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            wrapper.classList.remove('drag-over');
        });
        
        wrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            wrapper.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
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
                
                // Focus first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Show loading overlay
 */
window.showLoading = function() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="d-flex align-items-center">
                <strong>Ładowanie...</strong>
                <div class="spinner-border ms-auto" role="status"></div>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
};

/**
 * Hide loading overlay
 */
window.hideLoading = function() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
};

/**
 * AJAX helper with CSRF support
 */
window.ajaxRequest = function(url, options = {}) {
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
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            showToast('Wystąpił błąd podczas wykonywania żądania', 'error');
            throw error;
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
    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Initialize Bootstrap toast
    if (typeof bootstrap !== 'undefined') {
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: type === 'error' ? 8000 : 5000
        });
        bsToast.show();
        
        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    } else {
        // Fallback without Bootstrap
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};

/**
 * Batch operations helper
 */
window.initBatchOperations = function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selection[]"]');
    const batchButtons = document.querySelectorAll('.batch-action-btn');
    
    // Toggle batch buttons based on selections
    function toggleBatchButtons() {
        const checked = document.querySelectorAll('input[type="checkbox"][name="selection[]"]:checked');
        batchButtons.forEach(btn => {
            btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        });
    }
    
    // Select all checkbox
    const selectAll = document.querySelector('input[type="checkbox"][name="selection_all"]');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleBatchButtons();
        });
    }
    
    // Individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleBatchButtons);
    });
    
    // Initial state
    toggleBatchButtons();
};

// Initialize batch operations if checkboxes exist
if (document.querySelector('input[type="checkbox"][name="selection[]"]')) {
    initBatchOperations();
}