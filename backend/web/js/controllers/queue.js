/**
 * Queue Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class QueueController {
    constructor() {
        this.init();
    }

    init() {
        if (this.isCreatePage()) {
            this.initCreatePage();
        }
        
        if (this.isIndexPage()) {
            this.initIndexPage();
        }
        
        if (this.isViewPage()) {
            this.initViewPage();
        }
        
        this.initCommonFeatures();
    }

    isCreatePage() {
        return window.location.pathname.includes('/queue/create');
    }

    isIndexPage() {
        return window.location.pathname.includes('/queue/index') || 
               window.location.pathname.endsWith('/queue');
    }

    isViewPage() {
        return window.location.pathname.includes('/queue/view');
    }

    initCommonFeatures() {
        this.initTooltips();
        this.initBatchOperations();
    }

    initCreatePage() {
        this.initJsonEditor();
        this.initTemplateButtons();
        this.initFormValidation();
    }

    initIndexPage() {
        this.initAutoRefresh();
        this.initStatusFilters();
    }

    initViewPage() {
        this.initJobDetails();
    }

    initJsonEditor() {
        const paramsField = document.getElementById('job-params');
        if (!paramsField) return;
        
        // Helper function to format JSON
        const formatJSON = (json) => {
            try {
                return JSON.stringify(JSON.parse(json), null, 2);
            } catch (e) {
                return json;
            }
        };
        
        // Format initial JSON
        if (paramsField.value) {
            paramsField.value = formatJSON(paramsField.value);
        }
        
        // Auto-format JSON when input changes
        paramsField.addEventListener('blur', function() {
            if (this.value) {
                try {
                    this.value = formatJSON(this.value);
                } catch (e) {
                    // Leave as is if not valid JSON
                }
            }
        });
    }

    initTemplateButtons() {
        // Add template buttons functionality
        document.querySelectorAll('.use-template').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.getAttribute('data-type');
                const template = button.getAttribute('data-template');
                
                const typeSelect = document.getElementById('job-type');
                const paramsField = document.getElementById('job-params');
                
                if (typeSelect && paramsField) {
                    typeSelect.value = type;
                    
                    try {
                        const formattedTemplate = JSON.stringify(JSON.parse(template), null, 2);
                        paramsField.value = formattedTemplate;
                    } catch (e) {
                        paramsField.value = template;
                    }
                }
            });
        });
    }

    initFormValidation() {
        const form = document.querySelector('form');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            const paramsField = document.getElementById('job-params');
            
            if (paramsField && paramsField.value) {
                try {
                    JSON.parse(paramsField.value);
                } catch (e) {
                    e.preventDefault();
                    this.showToast('Wprowadź poprawny format JSON dla parametrów.', 'error');
                    return false;
                }
            }
        });
    }

    initAutoRefresh() {
        // Auto-refresh queue index every 30 seconds
        if (this.isIndexPage()) {
            setInterval(() => {
                this.refreshQueueStatus();
            }, 30000);
        }
    }

    async refreshQueueStatus() {
        try {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update status cards
                const newCards = doc.querySelectorAll('.queue-stats-cards .card');
                const currentCards = document.querySelectorAll('.queue-stats-cards .card');
                
                newCards.forEach((newCard, index) => {
                    if (currentCards[index]) {
                        const newValue = newCard.querySelector('h3').textContent;
                        const currentValue = currentCards[index].querySelector('h3');
                        
                        if (currentValue.textContent !== newValue) {
                            currentValue.textContent = newValue;
                            currentValue.style.animation = 'pulse 0.6s ease-in-out';
                            setTimeout(() => {
                                currentValue.style.animation = '';
                            }, 600);
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Failed to refresh queue status:', error);
        }
    }

    initStatusFilters() {
        const statusFilter = document.querySelector('select[name*="status"]');
        const typeFilter = document.querySelector('select[name*="type"]');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                this.applyFilters();
            });
        }

        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                this.applyFilters();
            });
        }
    }

    applyFilters() {
        const rows = document.querySelectorAll('tbody tr');
        const statusFilter = document.querySelector('select[name*="status"]')?.value || '';
        const typeFilter = document.querySelector('select[name*="type"]')?.value || '';

        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(4)'); // Assuming status is 4th column
            const typeCell = row.querySelector('td:nth-child(3)');   // Assuming type is 3rd column
            
            const statusMatch = !statusFilter || (statusCell && statusCell.textContent.includes(statusFilter));
            const typeMatch = !typeFilter || (typeCell && typeCell.textContent.includes(typeFilter));
            
            row.style.display = (statusMatch && typeMatch) ? '' : 'none';
        });
    }

    initJobDetails() {
        // Expandable JSON viewer for job parameters
        const jsonElements = document.querySelectorAll('pre code');
        jsonElements.forEach(element => {
            try {
                const json = JSON.parse(element.textContent);
                element.innerHTML = this.syntaxHighlightJson(json);
            } catch (e) {
                // Not JSON, leave as is
            }
        });
    }

    syntaxHighlightJson(json) {
        if (typeof json !== 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            let cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

    initBatchOperations() {
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        const batchButtons = document.querySelectorAll('.batch-action-btn');

        const updateBatchButtons = () => {
            const checkedCount = document.querySelectorAll('input[name="selection[]"]:checked').length;
            batchButtons.forEach(btn => {
                btn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
            });
        };

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButtons);
        });

        const selectAll = document.querySelector('input[name="selection_all"]');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBatchButtons();
            });
        }
    }

    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
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
    new QueueController();
});