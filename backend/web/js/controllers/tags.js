/**
 * Tags Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class TagsController {
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
        
        this.initCommonFeatures();
    }

    isIndexPage() {
        return window.location.pathname.includes('/tags/index') || 
               window.location.pathname.endsWith('/tags');
    }

    isFormPage() {
        return window.location.pathname.includes('/tags/create') || 
               window.location.pathname.includes('/tags/update');
    }

    initCommonFeatures() {
        this.initTooltips();
        this.initTagCloud();
    }

    initIndexPage() {
        this.initTagSearch();
        this.initPopularityBars();
    }

    initFormPage() {
        this.initTagPreview();
        this.initSlugGeneration();
        this.initTagSuggestions();
    }

    initTagCloud() {
        const tagItems = document.querySelectorAll('.tag-item');
        tagItems.forEach(tag => {
            tag.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
            });
            
            tag.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    initTagSearch() {
        const searchInput = document.querySelector('#tag-search');
        if (!searchInput) return;

        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.filterTags(e.target.value);
            }, 300);
        });
    }

    filterTags(searchTerm) {
        const tags = document.querySelectorAll('.tag-item');
        const term = searchTerm.toLowerCase().trim();
        
        tags.forEach(tag => {
            const tagName = tag.textContent.toLowerCase();
            const isVisible = term === '' || tagName.includes(term);
            tag.style.display = isVisible ? 'inline-block' : 'none';
        });

        this.updateSearchResults(searchTerm);
    }

    updateSearchResults(searchTerm) {
        const visibleTags = document.querySelectorAll('.tag-item:not([style*="display: none"])');
        const counter = document.querySelector('.search-counter');
        
        if (counter) {
            if (searchTerm && visibleTags.length === 0) {
                counter.textContent = 'Nie znaleziono tagów';
                counter.className = 'search-counter text-warning';
            } else if (searchTerm) {
                counter.textContent = `Znaleziono ${visibleTags.length} tagów`;
                counter.className = 'search-counter text-info';
            } else {
                counter.textContent = '';
            }
        }
    }

    initPopularityBars() {
        const popularityBars = document.querySelectorAll('.popularity-bar');
        
        popularityBars.forEach(bar => {
            const fill = bar.querySelector('.popularity-fill');
            if (fill) {
                const width = fill.dataset.width || '0';
                
                // Animate the bar
                setTimeout(() => {
                    fill.style.width = width + '%';
                }, 100);
            }
        });
    }

    initTagPreview() {
        const nameInput = document.querySelector('input[name="Tag[name]"]');
        const preview = document.querySelector('.tag-preview .badge');
        
        if (nameInput && preview) {
            const updatePreview = () => {
                const name = nameInput.value.trim();
                preview.textContent = name || 'Podgląd tagu';
                
                // Update preview style based on length
                if (name.length > 20) {
                    preview.className = 'badge bg-warning';
                } else if (name.length > 0) {
                    preview.className = 'badge bg-primary';
                } else {
                    preview.className = 'badge bg-secondary';
                }
            };

            nameInput.addEventListener('input', updatePreview);
            updatePreview(); // Initial update
        }
    }

    initSlugGeneration() {
        const nameInput = document.querySelector('input[name="Tag[name]"]');
        const slugDisplay = document.querySelector('#tag-slug-preview');
        
        if (nameInput && slugDisplay) {
            nameInput.addEventListener('input', function() {
                const slug = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
                
                slugDisplay.textContent = slug || 'tag-slug';
            });
        }
    }

    initTagSuggestions() {
        const nameInput = document.querySelector('input[name="Tag[name]"]');
        if (!nameInput) return;

        let suggestionsContainer = document.querySelector('.tag-suggestions');
        
        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'tag-suggestions';
            nameInput.parentNode.appendChild(suggestionsContainer);
        }

        let suggestionTimeout;
        
        nameInput.addEventListener('input', (e) => {
            clearTimeout(suggestionTimeout);
            suggestionTimeout = setTimeout(() => {
                this.fetchTagSuggestions(e.target.value, suggestionsContainer);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!nameInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    async fetchTagSuggestions(query, container) {
        if (query.length < 2) {
            container.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/tags/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const suggestions = await response.json();
                this.displaySuggestions(suggestions, container);
            }
        } catch (error) {
            console.error('Error fetching tag suggestions:', error);
        }
    }

    displaySuggestions(suggestions, container) {
        if (suggestions.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.innerHTML = suggestions.map(suggestion => 
            `<div class="tag-suggestion" data-tag="${suggestion.name}">
                <strong>${suggestion.name}</strong>
                <small class="text-muted ms-2">${suggestion.count} zdjęć</small>
            </div>`
        ).join('');

        // Add click handlers
        container.querySelectorAll('.tag-suggestion').forEach(item => {
            item.addEventListener('click', () => {
                const tagName = item.dataset.tag;
                document.querySelector('input[name="Tag[name]"]').value = tagName;
                container.style.display = 'none';
                
                // Trigger input event to update preview
                const event = new Event('input', { bubbles: true });
                document.querySelector('input[name="Tag[name]"]').dispatchEvent(event);
            });
        });

        container.style.display = 'block';
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
    new TagsController();
});