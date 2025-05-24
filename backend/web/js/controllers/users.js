/**
 * Users Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class UsersController {
    constructor() {
        this.init();
    }

    init() {
        if (this.isIndexPage()) {
            this.initIndexPage();
        }
        
        if (this.isFormPage()) {
            this.initFormPage();
        }
        
        if (this.isViewPage()) {
            this.initViewPage();
        }
        
        this.initCommonFeatures();
    }

    isIndexPage() {
        return window.location.pathname.includes('/users/index') || 
               window.location.pathname.endsWith('/users');
    }

    isFormPage() {
        return window.location.pathname.includes('/users/create') || 
               window.location.pathname.includes('/users/update');
    }

    isViewPage() {
        return window.location.pathname.includes('/users/view');
    }

    initCommonFeatures() {
        this.initTooltips();
        this.initUserCards();
    }

    initIndexPage() {
        this.initUserFilters();
        this.initBatchOperations();
        this.initUserStats();
    }

    initFormPage() {
        this.initPasswordStrength();
        this.initRoleSelection();
        this.initFormValidation();
    }

    initViewPage() {
        this.initActivityTimeline();
        this.initPermissionsToggle();
    }

    initUserCards() {
        const userCards = document.querySelectorAll('.user-card');
        userCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    }

    initUserFilters() {
        const filterInputs = document.querySelectorAll('.user-filter');
        let filterTimeout;

        filterInputs.forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    this.filterUsers();
                }, 300);
            });
        });
    }

    filterUsers() {
        const users = document.querySelectorAll('.user-item');
        const searchTerm = document.getElementById('user-search')?.value.toLowerCase() || '';
        const roleFilter = document.getElementById('role-filter')?.value || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';

        users.forEach(user => {
            const userName = user.dataset.username?.toLowerCase() || '';
            const userRole = user.dataset.role || '';
            const userStatus = user.dataset.status || '';

            const matchesSearch = !searchTerm || userName.includes(searchTerm);
            const matchesRole = !roleFilter || userRole === roleFilter;
            const matchesStatus = !statusFilter || userStatus === statusFilter;

            user.style.display = (matchesSearch && matchesRole && matchesStatus) ? 'block' : 'none';
        });
    }

    initBatchOperations() {
        const checkboxes = document.querySelectorAll('input[name="user_selection[]"]');
        const batchButtons = document.querySelectorAll('.batch-user-action');

        const updateBatchButtons = () => {
            const checkedCount = document.querySelectorAll('input[name="user_selection[]"]:checked').length;
            batchButtons.forEach(btn => {
                btn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
            });
        };

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButtons);
        });

        const selectAll = document.querySelector('#select-all-users');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBatchButtons();
            });
        }
    }

    initPasswordStrength() {
        const passwordInput = document.getElementById('user-password');
        const strengthMeter = document.getElementById('password-strength');
        
        if (!passwordInput || !strengthMeter) return;

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const strength = this.calculatePasswordStrength(password);
            this.updatePasswordStrength(strengthMeter, strength);
        });
    }

    calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return score;
    }

    updatePasswordStrength(meter, strength) {
        const levels = ['Bardzo słabe', 'Słabe', 'Średnie', 'Silne', 'Bardzo silne'];
        const colors = ['danger', 'warning', 'info', 'success', 'success'];
        
        meter.className = `password-strength bg-${colors[strength]} text-white`;
        meter.textContent = levels[strength] || 'Brak hasła';
        meter.style.width = ((strength + 1) * 20) + '%';
    }

    initRoleSelection() {
        const roleSelect = document.getElementById('user-role');
        const permissionsSection = document.getElementById('permissions-section');
        
        if (!roleSelect || !permissionsSection) return;

        roleSelect.addEventListener('change', () => {
            const role = roleSelect.value;
            if (role === 'admin') {
                permissionsSection.style.display = 'block';
            } else {
                permissionsSection.style.display = 'none';
            }
        });
    }

    initFormValidation() {
        const form = document.getElementById('user-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const username = document.getElementById('user-username').value.trim();
            const email = document.getElementById('user-email').value.trim();

            if (username.length < 3) {
                e.preventDefault();
                this.showToast('Nazwa użytkownika musi mieć co najmniej 3 znaki', 'error');
                return;
            }

            if (!this.isValidEmail(email)) {
                e.preventDefault();
                this.showToast('Wprowadź poprawny adres email', 'error');
                return;
            }
        });
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    initActivityTimeline() {
        const timelineItems = document.querySelectorAll('.activity-item');
        timelineItems.forEach((item, index) => {
            item.style.animationDelay = (index * 0.1) + 's';
            item.classList.add('fade-in');
        });
    }

    initPermissionsToggle() {
        const permissionToggles = document.querySelectorAll('.permission-toggle');
        permissionToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const permissionName = this.dataset.permission;
                const isGranted = this.checked;
                
                // Update UI
                const statusSpan = this.parentNode.querySelector('.permission-status');
                if (statusSpan) {
                    statusSpan.textContent = isGranted ? 'Przyznane' : 'Odebrane';
                    statusSpan.className = `permission-status badge ${isGranted ? 'bg-success' : 'bg-secondary'}`;
                }
            });
        });
    }

    initUserStats() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            setTimeout(() => {
                const number = card.querySelector('.stat-number');
                if (number) {
                    this.animateNumber(number, parseInt(number.textContent) || 0);
                }
            }, index * 200);
        });
    }

    animateNumber(element, target) {
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 50);
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
    new UsersController();
});
