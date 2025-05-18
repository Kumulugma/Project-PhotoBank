/**
 * Search-specific JavaScript functionality
 * Handles search forms, filters, and result management
 */

class SearchManager {
    constructor() {
        this.searchForm = null;
        this.searchInput = null;
        this.suggestionsContainer = null;
        this.activeFilters = new Set();
        this.init();
    }

    init() {
        this.searchForm = document.getElementById('searchForm');
        this.searchInput = document.getElementById('keywords-input');
        this.suggestionsContainer = document.getElementById('searchSuggestions');
        
        if (!this.searchForm) return;
        
        this.initSearchInput();
        this.initCategoryFilters();
        this.initTagFilters();
        this.initActiveFilters();
        this.initQuickSuggestions();
        this.initViewControls();
        this.initKeyboardShortcuts();
    }

    initSearchInput() {
        if (!this.searchInput) return;
        
        let searchTimeout;
        
        // Real-time search suggestions
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.showSearchSuggestions(e.target.value);
            }, 300);
        });
        
        // Hide suggestions on click outside
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.suggestionsContainer?.contains(e.target)) {
                this.hideSuggestions();
            }
        });
        
        // Keyboard navigation for suggestions
        this.searchInput.addEventListener('keydown', (e) => {
            if (this.suggestionsContainer?.style.display === 'block') {
                this.handleSuggestionsKeyboard(e);
            }
        });
    }

    showSearchSuggestions(query) {
        if (!this.suggestionsContainer || query.length < 2) {
            this.hideSuggestions();
            return;
        }
        
        // Mock suggestions - replace with actual API call
        const suggestions = this.generateSuggestions(query);
        
        if (suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        this.renderSuggestions(suggestions);
        this.suggestionsContainer.style.display = 'block';
    }

    generateSuggestions(query) {
        const mockSuggestions = [
            'krajobraz', 'portret', 'architektura', 'natura', 'miasto',
            'zachód słońca', 'morze', 'góry', 'las', 'kwiaty',
            'sztuka', 'makro', 'zwierzęta', 'podróże', 'czarno-białe'
        ];
        
        return mockSuggestions
            .filter(item => item.toLowerCase().includes(query.toLowerCase()))
            .slice(0, 5);
    }

    renderSuggestions(suggestions) {
        if (!this.suggestionsContainer) return;
        
        this.suggestionsContainer.innerHTML = '';
        
        suggestions.forEach((suggestion, index) => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = suggestion;
            item.setAttribute('data-suggestion', suggestion);
            item.setAttribute('tabindex', '-1');
            
            item.addEventListener('click', () => {
                this.selectSuggestion(suggestion);
            });
            
            this.suggestionsContainer.appendChild(item);
        });
    }

    selectSuggestion(suggestion) {
        if (this.searchInput) {
            this.searchInput.value = suggestion;
            this.hideSuggestions();
            this.searchInput.focus();
        }
    }

    hideSuggestions() {
        if (this.suggestionsContainer) {
            this.suggestionsContainer.style.display = 'none';
        }
    }

    handleSuggestionsKeyboard(e) {
        const suggestions = this.suggestionsContainer?.querySelectorAll('.suggestion-item');
        if (!suggestions || suggestions.length === 0) return;
        
        let currentIndex = -1;
        suggestions.forEach((item, index) => {
            if (item.classList.contains('highlighted')) {
                currentIndex = index;
            }
        });
        
        let newIndex = currentIndex;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                newIndex = Math.min(currentIndex + 1, suggestions.length - 1);
                break;
            case 'ArrowUp':
                e.preventDefault();
                newIndex = Math.max(currentIndex - 1, -1);
                break;
            case 'Enter':
                e.preventDefault();
                if (currentIndex >= 0) {
                    this.selectSuggestion(suggestions[currentIndex].textContent);
                }
                return;
            case 'Escape':
                e.preventDefault();
                this.hideSuggestions();
                return;
            default:
                return;
        }
        
        // Update highlighting
        suggestions.forEach(item => item.classList.remove('highlighted'));
        if (newIndex >= 0) {
            suggestions[newIndex].classList.add('highlighted');
        }
    }

    initCategoryFilters() {
        const categoryItems = document.querySelectorAll('.category-item');
        
        categoryItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleCategory(item);
            });
            
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleCategory(item);
                }
            });
            
            // Make items focusable and accessible
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'checkbox');
            
            // Set initial state
            const checkbox = item.querySelector('.category-checkbox');
            const isChecked = checkbox?.checked || false;
            item.classList.toggle('selected', isChecked);
            item.setAttribute('aria-checked', isChecked);
        });
    }

    toggleCategory(item) {
        const checkbox = item.querySelector('.category-checkbox');
        if (!checkbox) return;
        
        checkbox.checked = !checkbox.checked;
        item.classList.toggle('selected', checkbox.checked);
        item.setAttribute('aria-checked', checkbox.checked);
        
        // Trigger change event for other listeners
        checkbox.dispatchEvent(new Event('change'));
        
        // Update active filters tracking
        const categoryId = checkbox.value;
        if (checkbox.checked) {
            this.activeFilters.add(`category-${categoryId}`);
        } else {
            this.activeFilters.delete(`category-${categoryId}`);
        }
        
        // Announce to screen readers
        const categoryName = item.querySelector('.category-name')?.textContent || '';
        const status = checkbox.checked ? 'zaznaczona' : 'odznaczona';
        this.announceToScreenReader(`Kategoria ${categoryName} ${status}`);
        
        this.updateSearchStats();
    }

    initTagFilters() {
        const tagItems = document.querySelectorAll('.tag-checkbox');
        
        tagItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleTag(item);
            });
            
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleTag(item);
                }
            });
            
            // Make items focusable and accessible
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'checkbox');
            
            // Set initial state
            const checkbox = item.querySelector('input[type="checkbox"]');
            const isChecked = checkbox?.checked || false;
            item.classList.toggle('selected', isChecked);
            item.setAttribute('aria-checked', isChecked);
        });
    }

    toggleTag(item) {
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (!checkbox) return;
        
        checkbox.checked = !checkbox.checked;
        item.classList.toggle('selected', checkbox.checked);
        item.setAttribute('aria-checked', checkbox.checked);
        
        // Trigger change event for other listeners
        checkbox.dispatchEvent(new Event('change'));
        
        // Update active filters tracking
        const tagId = checkbox.value;
        if (checkbox.checked) {
            this.activeFilters.add(`tag-${tagId}`);
        } else {
            this.activeFilters.delete(`tag-${tagId}`);
        }
        
        // Announce to screen readers
        const tagElement = item.querySelector('.tag');
        const tagName = tagElement?.textContent.trim() || '';
        const status = checkbox.checked ? 'zaznaczony' : 'odznaczony';
        this.announceToScreenReader(`Tag ${tagName} ${status}`);
        
        this.updateSearchStats();
    }

    initActiveFilters() {
        const removeButtons = document.querySelectorAll('.remove-filter');
        
        removeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeFilter(button);
            });
        });
    }

    removeFilter(button) {
        const field = button.dataset.field;
        const value = button.dataset.value;
        
        if (field === 'keywords') {
            if (this.searchInput) {
                this.searchInput.value = '';
                this.searchInput.focus();
            }
        } else if (field === 'categories' && value) {
            const checkbox = document.querySelector(`input[name="SearchForm[categories][]"][value="${value}"]`);
            if (checkbox) {
                checkbox.checked = false;
                const categoryItem = checkbox.closest('.category-item');
                if (categoryItem) {
                    categoryItem.classList.remove('selected');
                    categoryItem.setAttribute('aria-checked', 'false');
                }
                this.activeFilters.delete(`category-${value}`);
            }
        } else if (field === 'tags' && value) {
            const checkbox = document.querySelector(`input[name="SearchForm[tags][]"][value="${value}"]`);
            if (checkbox) {
                checkbox.checked = false;
                const tagItem = checkbox.closest('.tag-checkbox');
                if (tagItem) {
                    tagItem.classList.remove('selected');
                    tagItem.setAttribute('aria-checked', 'false');
                }
                this.activeFilters.delete(`tag-${value}`);
            }
        }
        
        // Submit form to update results
        this.submitForm();
        this.updateSearchStats();
    }

    initQuickSuggestions() {
        const suggestionTags = document.querySelectorAll('.suggestion-tag');
        
        suggestionTags.forEach(tag => {
            tag.addEventListener('click', () => {
                this.selectQuickSuggestion(tag);
            });
            
            tag.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.selectQuickSuggestion(tag);
                }
            });
            
            // Make items focusable
            tag.setAttribute('tabindex', '0');
        });
    }

    selectQuickSuggestion(tag) {
        const tagId = tag.dataset.tagId;
        const tagName = tag.dataset.tagName;
        
        // Find and check the corresponding tag checkbox
        const checkbox = document.querySelector(`input[name="SearchForm[tags][]"][value="${tagId}"]`);
        if (checkbox && !checkbox.checked) {
            checkbox.checked = true;
            const tagItem = checkbox.closest('.tag-checkbox');
            if (tagItem) {
                tagItem.classList.add('selected');
                tagItem.setAttribute('aria-checked', 'true');
            }
            
            this.activeFilters.add(`tag-${tagId}`);
            this.announceToScreenReader(`Tag ${tagName} został dodany do wyszukiwania`);
            this.submitForm();
        }
    }

    initViewControls() {
        const toggleButton = document.getElementById('toggleViewMode');
        const galleryGrid = document.getElementById('galleryGrid');
        const sortSelect = document.getElementById('sortBy');
        
        // View mode toggle
        if (toggleButton && galleryGrid) {
            toggleButton.addEventListener('click', () => {
                this.toggleViewMode(toggleButton, galleryGrid);
            });
        }
        
        // Sort functionality
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                this.sortResults(sortSelect.value, galleryGrid);
            });
        }
    }

    toggleViewMode(button, grid) {
        const isListView = grid.classList.contains('list-view');
        const icon = button.querySelector('i');
        const text = button.querySelector('.view-mode-text');
        
        grid.classList.toggle('list-view');
        
        if (isListView) {
            icon.className = 'fas fa-th';
            text.textContent = 'Siatka';
            button.setAttribute('aria-label', 'Przełącz na widok listy');
            this.announceToScreenReader('Przełączono na widok siatki');
        } else {
            icon.className = 'fas fa-list';
            text.textContent = 'Lista';
            button.setAttribute('aria-label', 'Przełącz na widok siatki');
            this.announceToScreenReader('Przełączono na widok listy');
        }
    }

    sortResults(sortBy, grid) {
        if (!grid) return;
        
        const items = Array.from(grid.querySelectorAll('.photo-item-wrapper'));
        
        items.sort((a, b) => {
            const photoA = a.querySelector('.photo-item');
            const photoB = b.querySelector('.photo-item');
            
            if (!photoA || !photoB) return 0;
            
            switch (sortBy) {
                case 'newest':
                    return parseInt(photoB.dataset.photoId || '0') - parseInt(photoA.dataset.photoId || '0');
                case 'oldest':
                    return parseInt(photoA.dataset.photoId || '0') - parseInt(photoB.dataset.photoId || '0');
                case 'name':
                    const nameA = photoA.querySelector('.photo-title')?.textContent.toLowerCase() || '';
                    const nameB = photoB.querySelector('.photo-title')?.textContent.toLowerCase() || '';
                    return nameA.localeCompare(nameB);
                case 'name-desc':
                    const nameDescA = photoA.querySelector('.photo-title')?.textContent.toLowerCase() || '';
                    const nameDescB = photoB.querySelector('.photo-title')?.textContent.toLowerCase() || '';
                    return nameDescB.localeCompare(nameDescA);
                default:
                    return 0;
            }
        });
        
        // Reorder DOM elements with animation
        items.forEach((item, index) => {
            item.style.order = index;
            item.style.animation = `fadeInUp 0.3s ease-out ${index * 0.05}s both`;
        });
        
        // Announce sort change
        const sortLabels = {
            'newest': 'najnowsze',
            'oldest': 'najstarsze',
            'name': 'nazwa A-Z',
            'name-desc': 'nazwa Z-A'
        };
        this.announceToScreenReader(`Posortowano według: ${sortLabels[sortBy] || sortBy}`);
    }

    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+F - Focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                this.searchInput?.focus();
                return;
            }
            
            // Ctrl+Enter - Submit search
            if (e.ctrlKey && e.key === 'Enter' && document.activeElement === this.searchInput) {
                e.preventDefault();
                this.submitForm();
                return;
            }
            
            // Escape - Clear search or close suggestions
            if (e.key === 'Escape') {
                if (this.suggestionsContainer?.style.display === 'block') {
                    this.hideSuggestions();
                } else if (document.activeElement === this.searchInput) {
                    this.searchInput.blur();
                }
                return;
            }
        });
    }

    updateSearchStats() {
        const selectedCategories = document.querySelectorAll('.category-checkbox:checked').length;
        const selectedTags = document.querySelectorAll('input[name="SearchForm[tags][]"]:checked').length;
        const keywordsLength = this.searchInput?.value.trim().length || 0;
        
        const statsContainer = document.getElementById('searchStats');
        if (!statsContainer) return;
        
        let existingStats = statsContainer.querySelector('.search-stats-info');
        
        if (keywordsLength === 0 && selectedCategories === 0 && selectedTags === 0) {
            if (existingStats) {
                existingStats.remove();
            }
            return;
        }
        
        let message = 'Aktywne filtry: ';
        const parts = [];
        
        if (keywordsLength > 0) parts.push('słowa kluczowe');
        if (selectedCategories > 0) parts.push(`${selectedCategories} kategorii`);
        if (selectedTags > 0) parts.push(`${selectedTags} tagów`);
        
        message += parts.join(', ');
        
        if (!existingStats) {
            existingStats = document.createElement('div');
            existingStats.className = 'search-stats-info';
            statsContainer.appendChild(existingStats);
        }
        
        existingStats.innerHTML = `<small><i class="fas fa-filter" aria-hidden="true"></i> ${message}</small>`;
    }

    submitForm() {
        if (this.searchForm) {
            // Add loading state
            const submitButton = this.searchForm.querySelector('#searchSubmit');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Szukam...';
            }
            
            this.searchForm.submit();
        }
    }

    announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            if (document.body.contains(announcement)) {
                document.body.removeChild(announcement);
            }
        }, 1000);
    }

    // Public API methods
    clearAllFilters() {
        // Clear keywords
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        
        // Clear categories
        document.querySelectorAll('.category-checkbox:checked').forEach(checkbox => {
            checkbox.checked = false;
            const item = checkbox.closest('.category-item');
            if (item) {
                item.classList.remove('selected');
                item.setAttribute('aria-checked', 'false');
            }
        });
        
        // Clear tags
        document.querySelectorAll('input[name="SearchForm[tags][]"]:checked').forEach(checkbox => {
            checkbox.checked = false;
            const item = checkbox.closest('.tag-checkbox');
            if (item) {
                item.classList.remove('selected');
                item.setAttribute('aria-checked', 'false');
            }
        });
        
        this.activeFilters.clear();
        this.updateSearchStats();
        this.announceToScreenReader('Wszystkie filtry zostały wyczyszczone');
    }

    getActiveFilters() {
        return {
            keywords: this.searchInput?.value.trim() || '',
            categories: Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value),
            tags: Array.from(document.querySelectorAll('input[name="SearchForm[tags][]"]:checked')).map(cb => cb.value)
        };
    }
}

// Initialize search manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SearchManager;
}