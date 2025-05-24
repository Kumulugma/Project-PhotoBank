/**
 * Audit Log Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class AuditLogController {
    constructor() {
        this.init();
    }

    init() {
        if (this.isIndexPage()) {
            this.initIndexPage();
        }
        
        if (this.isDashboardPage()) {
            this.initDashboardPage();
        }
        
        this.initCommonFeatures();
    }

    isIndexPage() {
        return window.location.pathname.includes('/audit-log/index');
    }

    isDashboardPage() {
        return window.location.pathname.includes('/audit-log/dashboard');
    }

    initCommonFeatures() {
        this.initModals();
        this.initTooltips();
    }

    initIndexPage() {
        this.initBulkOperations();
        this.initDeleteHandlers();
    }

    initDashboardPage() {
        this.initActivityChart();
        this.initDashboardModals();
    }

    initBulkOperations() {
        const checkboxes = document.querySelectorAll('.audit-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkDeleteInputs = document.getElementById('bulk-delete-inputs');
        
        const updateBulkDeleteButton = () => {
            const checked = document.querySelectorAll('.audit-checkbox:checked');
            if (checked.length > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
                selectedCountSpan.textContent = checked.length;
                
                bulkDeleteInputs.innerHTML = '';
                checked.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selection[]';
                    input.value = checkbox.value;
                    bulkDeleteInputs.appendChild(input);
                });
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        };
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkDeleteButton);
        });
        
        const selectAllCheckbox = document.querySelector('th input[type="checkbox"]');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                setTimeout(updateBulkDeleteButton, 10);
            });
        }
    }

    initDeleteHandlers() {
        document.querySelectorAll('.delete-single').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('delete-id').value = id;
            });
        });
    }

    initActivityChart() {
        const ctx = document.getElementById('activityChart');
        if (!ctx || typeof Chart === 'undefined') return;

        // Chart data should be passed from PHP
        const chartData = window.auditChartData || {
            labels: [],
            data: []
        };

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Liczba zdarzeń',
                    data: chartData.data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    initDashboardModals() {
        // Quick range selector for export modal
        const quickRangeSelect = document.getElementById('quickRangeSelect');
        const customDateRange = document.getElementById('customDateRange');

        if (quickRangeSelect) {
            quickRangeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'block';
                } else {
                    customDateRange.style.display = 'none';
                    this.setPredefinedeRanges(this.value);
                }
            });
        }
    }

    setPredefinedeRanges(range) {
        const dateFrom = document.querySelector('input[name="date_from"]');
        const dateTo = document.querySelector('input[name="date_to"]');

        if (!dateFrom || !dateTo) return;

        const today = new Date();
        const formatDate = (date) => date.toISOString().split('T')[0];

        switch (range) {
            case 'today':
                dateFrom.value = formatDate(today);
                dateTo.value = formatDate(today);
                break;
            case 'week':
                const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                dateFrom.value = formatDate(weekAgo);
                dateTo.value = formatDate(today);
                break;
            case 'month':
                const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                dateFrom.value = formatDate(monthAgo);
                dateTo.value = formatDate(today);
                break;
            default:
                dateFrom.value = '';
                dateTo.value = '';
        }
    }

    initModals() {
        // Common modal functionality
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const targetModal = document.querySelector(this.getAttribute('data-bs-target'));
                if (targetModal) {
                    // Any modal-specific initialization can go here
                }
            });
        });
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
    new AuditLogController();
});