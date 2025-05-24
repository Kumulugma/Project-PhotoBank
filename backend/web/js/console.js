/**
 * Console Commands JavaScript functionality
 */

class ConsoleCommands {
    constructor() {
        this.init();
    }

    init() {
        this.setupSearch();
        this.setupCopyButtons();
        this.setupSyntaxHighlighting();
        this.setupCommandExecution();
        this.setupKeyboardShortcuts();
    }

    /**
     * Setup search functionality
     */
    setupSearch() {
        const searchInput = document.getElementById('commandSearch');
        if (!searchInput) return;

        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.filterCommands(e.target.value);
            }, 300);
        });

        // Clear search button
        const clearBtn = document.createElement('button');
        clearBtn.className = 'btn btn-outline-secondary';
        clearBtn.type = 'button';
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.onclick = () => {
            searchInput.value = '';
            this.filterCommands('');
        };

        const inputGroup = searchInput.parentElement;
        const clearWrapper = document.createElement('div');
        clearWrapper.className = 'input-group-append';
        clearWrapper.appendChild(clearBtn);
        inputGroup.appendChild(clearWrapper);
    }

    /**
     * Filter commands based on search term
     */
    filterCommands(searchTerm) {
        const cards = document.querySelectorAll('.command-card');
        const term = searchTerm.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const content = card.textContent.toLowerCase();
            const isVisible = term === '' || content.includes(term);
            
            card.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });

        // Update counter or show no results message
        this.updateSearchResults(visibleCount, cards.length);
    }

    /**
     * Update search results counter
     */
    updateSearchResults(visible, total) {
        let counter = document.querySelector('.search-counter');
        
        if (!counter) {
            counter = document.createElement('div');
            counter.className = 'search-counter text-muted mt-2';
            document.querySelector('.command-search').appendChild(counter);
        }

        if (visible === 0 && total > 0) {
            counter.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Nie znaleziono pasujących komend';
            counter.className = 'search-counter text-warning mt-2';
        } else if (visible < total) {
            counter.innerHTML = `<i class="fas fa-filter me-1"></i>Wyświetlanie ${visible} z ${total} komend`;
            counter.className = 'search-counter text-info mt-2';
        } else {
            counter.innerHTML = '';
        }
    }

    /**
     * Setup copy to clipboard functionality
     */
    setupCopyButtons() {
        document.querySelectorAll('.command-code').forEach(codeBlock => {
            const command = codeBlock.textContent.trim();
            
            // Remove existing copy button if any
            const existingBtn = codeBlock.querySelector('.copy-btn');
            if (existingBtn) existingBtn.remove();
            
            const copyBtn = document.createElement('button');
            copyBtn.className = 'btn btn-outline-light btn-sm copy-btn';
            copyBtn.type = 'button';
            copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i>Kopiuj';
            copyBtn.setAttribute('data-command', command);
            
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.copyToClipboard(command, copyBtn);
            });
            
            codeBlock.appendChild(copyBtn);
        });
    }

    /**
     * Copy text to clipboard with visual feedback
     */
    async copyToClipboard(text, button) {
        try {
            await navigator.clipboard.writeText(text);
            
            // Visual feedback
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i>Skopiowano!';
            button.classList.remove('btn-outline-light');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-light');
            }, 2000);
            
            // Show toast notification
            this.showToast('Komenda skopiowana do schowka!', 'success');
            
        } catch (err) {
            console.error('Failed to copy: ', err);
            this.showToast('Błąd kopiowania do schowka', 'error');
        }
    }

    /**
     * Setup basic syntax highlighting
     */
    setupSyntaxHighlighting() {
        document.querySelectorAll('.command-code').forEach(codeBlock => {
            let html = codeBlock.innerHTML;
            
            // Skip if already highlighted or contains HTML
            if (html.includes('<span') || html.includes('<button')) return;
            
            // Get text content for highlighting
            const text = codeBlock.textContent.trim();
            
            // Basic syntax highlighting patterns
            let highlighted = text
                // Highlight yii command
                .replace(/\byii\b/g, '<span class="keyword">yii</span>')
                // Highlight parameters
                .replace(/--([a-zA-Z_]+)(=[^\s]*)?/g, '<span class="parameter">--$1</span><span class="string">$2</span>')
                // Highlight numbers
                .replace(/\b\d+\b/g, '<span class="number">$&</span>')
                // Highlight quoted strings
                .replace(/'([^']*)'/g, '<span class="string">\'$1\'</span>')
                .replace(/"([^"]*)"/g, '<span class="string">"$1"</span>');
            
            // Only update if we made changes
            if (highlighted !== text) {
                // Preserve the copy button
                const copyBtn = codeBlock.querySelector('.copy-btn');
                codeBlock.innerHTML = highlighted;
                if (copyBtn) {
                    codeBlock.appendChild(copyBtn);
                }
            }
        });
    }

    /**
     * Setup command execution form
     */
    setupCommandExecution() {
        const executeForm = document.querySelector('form[action*="execute"]');
        if (!executeForm) return;

        executeForm.addEventListener('submit', (e) => {
            const submitBtn = executeForm.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Wykonywanie...';
            submitBtn.disabled = true;
            
            // Re-enable after a delay (form will redirect anyway)
            setTimeout(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            }, 5000);
        });
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+K or Cmd+K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('commandSearch');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('commandSearch');
                if (searchInput && searchInput === document.activeElement) {
                    searchInput.value = '';
                    this.filterCommands('');
                    searchInput.blur();
                }
            }
        });
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.console-toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show console-toast`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-triangle' : 'info-circle';
        
        toast.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 150);
        }, 3000);
    }

    /**
     * Add command categories toggle
     */
    setupCategoryToggle() {
        document.querySelectorAll('.command-header').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const body = header.nextElementSibling;
                const isCollapsed = body.style.display === 'none';
                
                body.style.display = isCollapsed ? 'block' : 'none';
                
                const icon = header.querySelector('i');
                if (icon) {
                    icon.className = isCollapsed ? 
                        'fas fa-folder-open me-2' : 
                        'fas fa-folder me-2';
                }
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ConsoleCommands();
});

// Add keyboard shortcut hint
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('commandSearch');
    if (searchInput) {
        searchInput.placeholder = 'Szukaj komend... (Ctrl+K)';
    }
});