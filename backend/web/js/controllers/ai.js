/**
 * AI Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class AiController {
    constructor() {
        this.init();
    }

    init() {
        this.initProviderSelection();
        this.initTestButton();
        this.initFormValidation();
        this.initTooltips();
    }

    initProviderSelection() {
        const providerSelect = document.getElementById('ai-provider');
        if (!providerSelect) return;

        // Initialize fields visibility
        this.toggleProviderFields();
        
        // Handle provider change
        providerSelect.addEventListener('change', () => {
            this.toggleProviderFields();
        });
    }

    toggleProviderFields() {
        const providerSelect = document.getElementById('ai-provider');
        const provider = providerSelect ? providerSelect.value : '';
        
        // Hide all provider fields
        document.querySelectorAll('.provider-field, .provider-info').forEach(el => {
            el.style.display = 'none';
        });
        
        if (provider) {
            // Show relevant fields
            document.querySelectorAll('.' + provider + '-field, .' + provider + '-info').forEach(el => {
                el.style.display = 'block';
                
                // Add animation
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.3s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 10);
            });
        }
    }

    initTestButton() {
        const testBtn = document.getElementById('test-ai-btn');
        if (!testBtn) return;

        testBtn.addEventListener('click', () => {
            this.testAiConnection();
        });
    }

    async testAiConnection() {
        const button = document.getElementById('test-ai-btn');
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testowanie...';
        
        try {
            const response = await fetch('/ai/test', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.getCsrfToken(),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Test AI zakończony sukcesem!', 'success');
                this.updateConnectionStatus('connected');
            } else {
                this.showToast('Test AI nieudany: ' + (data.message || 'Nieznany błąd'), 'error');
                this.updateConnectionStatus('disconnected');
            }
        } catch (error) {
            console.error('AI Test Error:', error);
            this.showToast('Błąd podczas testowania AI', 'error');
            this.updateConnectionStatus('error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    updateConnectionStatus(status) {
        const indicators = document.querySelectorAll('.ai-status-indicator');
        indicators.forEach(indicator => {
            indicator.className = `ai-status-indicator ${status}`;
        });
    }

    initFormValidation() {
        const form = document.getElementById('ai-settings-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const provider = document.getElementById('ai-provider').value;
            const apiKey = document.querySelector('input[name="api_key"]').value;

            if (!provider) {
                e.preventDefault();
                this.showToast('Wybierz dostawcę AI', 'error');
                return;
            }

            if (!apiKey.trim()) {
                e.preventDefault();
                this.showToast('Wprowadź klucz API', 'error');
                return;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Zapisywanie...';

            // Form will submit normally, re-enable button after delay
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 3000);
        });
    }

    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    getCsrfToken() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        return csrfToken ? csrfToken.getAttribute('content') : '';
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new AiController();
});