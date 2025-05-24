/**
 * Categories Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class CategoriesController {
    constructor() {
        this.init();
    }

    init() {
        if (this.isFormPage()) {
            this.initFormPage();
        }
        
        if (this.isIndexPage()) {
            this.initIndexPage();
        }
        
        this.initCommonFeatures();
    }

    isFormPage() {
        return window.location.pathname.includes('/categories/create') || 
               window.location.pathname.includes('/categories/update');
    }

    isIndexPage() {
        return window.location.pathname.includes('/categories/index') || 
               window.location.pathname.endsWith('/categories');
    }

    initCommonFeatures() {
        this.initTooltips();
    }

    initFormPage() {
        this.initAutoResize();
        this.initSlugPreview();
    }

    initIndexPage() {
        this.initCategoryCards();
    }

    initAutoResize() {
        const textarea = document.querySelector('textarea[name="Category[description]"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        }
    }

    initSlugPreview() {
        const nameInput = document.querySelector('input[name="Category[name]"]');
        if (nameInput && !document.querySelector('input[name="Category[slug]"]')) {
            // Create slug preview element
            const slugPreview = document.createElement('div');
            slugPreview.className = 'form-text';
            slugPreview.innerHTML = '<strong>URL slug:</strong> <code id="slug-preview">/category/...</code>';
            nameInput.parentNode.appendChild(slugPreview);
            
            // Update slug preview on name change
            nameInput.addEventListener('input', function() {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                    .replace(/\s+/g, '-') // Replace spaces with hyphens
                    .replace(/-+/g, '-') // Replace multiple hyphens with single
                    .trim('-'); // Remove leading/trailing hyphens
                
                document.getElementById('slug-preview').textContent = 
                    slug ? `/category/${slug}` : '/category/...';
            });
        }
    }

    initCategoryCards() {
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    }

    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new CategoriesController();
});