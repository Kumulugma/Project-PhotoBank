/**
 * Simple Tags Controller
 * Podstawowe funkcje bez jQuery
 */

class SimpleTagsController {
    constructor() {
        this.init();
    }

    init() {
        this.initBasicFeatures();
        
        if (this.isFormPage()) {
            this.initFormFeatures();
        }
        
        console.log('🏷️ Tags Controller initialized');
    }

    isFormPage() {
        return window.location.pathname.includes('/tags/create') || 
               window.location.pathname.includes('/tags/update');
    }

    // ========================================
    // Basic Features
    // ========================================

    initBasicFeatures() {
        this.initTooltips();
        this.initPopularityBars();
        this.initTagHoverEffects();
    }

    initTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    initPopularityBars() {
        const popularityBars = document.querySelectorAll('.popularity-fill');
        
        popularityBars.forEach((bar, index) => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, index * 100 + 300);
        });
    }

    initTagHoverEffects() {
        const tagItems = document.querySelectorAll('.tag-item');
        
        tagItems.forEach(tag => {
            tag.addEventListener('mouseenter', () => {
                tag.style.transform = 'translateY(-2px)';
            });
            
            tag.addEventListener('mouseleave', () => {
                tag.style.transform = 'translateY(0)';
            });
        });
    }

    // ========================================
    // Form Features
    // ========================================

    initFormFeatures() {
        this.initLivePreview();
        this.initBasicValidation();
        this.initExampleTags();
    }

    initLivePreview() {
        const nameInput = document.querySelector('#tag-name-input');
        const preview = document.querySelector('#preview-text');
        const previewBadge = document.querySelector('#preview-badge');
        
        if (!nameInput || !preview) return;

        nameInput.addEventListener('input', (e) => {
            const value = e.target.value;
            const cleanName = this.cleanTagName(value);
            const displayName = cleanName || 'wprowadź-nazwę';
            
            preview.textContent = displayName;
            this.updatePreviewBadge(previewBadge, cleanName);
        });
    }

    cleanTagName(name) {
        return name
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-ąćęłńóśźż]/g, '')
            .replace(/[ąćęłńóśźż]/g, (match) => {
                const map = {
                    'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l',
                    'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z', 'ż': 'z'
                };
                return map[match] || match;
            })
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    updatePreviewBadge(badge, name) {
        if (!badge) return;
        
        // Reset classes
        badge.className = 'badge fs-4';
        
        if (!name) {
            badge.classList.add('bg-secondary');
        } else if (name.length > 20) {
            badge.classList.add('bg-danger');
        } else if (name.length > 15) {
            badge.classList.add('bg-warning');
        } else if (name.length >= 3) {
            badge.classList.add('bg-success');
        } else {
            badge.classList.add('bg-info');
        }
    }

    initBasicValidation() {
        const nameInput = document.querySelector('#tag-name-input');
        if (!nameInput) return;

        nameInput.addEventListener('input', (e) => {
            const value = e.target.value;
            const cleanName = this.cleanTagName(value);
            
            // Simple validation feedback
            if (cleanName.length < 2 && cleanName.length > 0) {
                nameInput.style.borderColor = '#ffc107';
            } else if (cleanName.length > 25) {
                nameInput.style.borderColor = '#dc3545';
            } else if (cleanName.length >= 2) {
                nameInput.style.borderColor = '#28a745';
            } else {
                nameInput.style.borderColor = '#e9ecef';
            }
        });
    }

    initExampleTags() {
        const exampleTags = document.querySelectorAll('.example-tag');
        const nameInput = document.querySelector('#tag-name-input');
        
        if (!nameInput) return;
        
        exampleTags.forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.preventDefault();
                const name = tag.getAttribute('data-name');
                nameInput.value = name;
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameInput.focus();
            });
        });
    }

    // ========================================
    // Utility Functions
    // ========================================

    showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${this.getAlertClass(type)} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-${this.getIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    getAlertClass(type) {
        const map = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return map[type] || 'info';
    }

    getIcon(type) {
        const map = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return map[type] || 'info-circle';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new SimpleTagsController();
});

// Copy to clipboard function (for view page)
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showSimpleToast('Tekst skopiowany do schowka', 'success');
        });
    } else {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showSimpleToast('Tekst skopiowany do schowka', 'success');
    }
}

// Simple toast function for global use
function showSimpleToast(message, type = 'info') {
    const alertClass = {
        'success': 'success',
        'error': 'danger',
        'warning': 'warning',
        'info': 'info'
    }[type] || 'info';
    
    const icon = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    }[type] || 'info-circle';
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${alertClass} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}