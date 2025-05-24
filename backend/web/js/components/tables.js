// Check if TablesComponent already exists to prevent redeclaration
if (typeof window.TablesComponent !== 'undefined') {
    console.warn('TablesComponent already defined, skipping redefinition');
} else {
    /**
     * Tables Components JavaScript
     */
    class TablesComponent {
        constructor() {
            this.init();
        }

        init() {
            this.initSortableHeaders();
            this.initRowSelection();
            this.initResponsiveTables();
            this.initImagePreviews();
            this.initTableFilters();
            this.initBatchActions();
        }

        initSortableHeaders() {
            document.querySelectorAll('th[data-sort]').forEach(header => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    this.sortTable(header);
                });
            });

            // GridView sortable headers
            document.querySelectorAll('th a[data-sort]').forEach(link => {
                const header = link.closest('th');
                if (header) {
                    header.classList.add('sortable');
                    header.addEventListener('click', (e) => {
                        if (e.target === header) {
                            link.click();
                        }
                    });
                }
            });
        }

        sortTable(header) {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const columnIndex = Array.from(header.parentNode.children).indexOf(header);
            const sortOrder = header.dataset.sort === 'asc' ? 'desc' : 'asc';

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex]?.textContent.trim() || '';
                const bText = b.cells[columnIndex]?.textContent.trim() || '';
                
                const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
                const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return sortOrder === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                return sortOrder === 'asc' ? 
                    aText.localeCompare(bText) : 
                    bText.localeCompare(aText);
            });

            rows.forEach(row => tbody.appendChild(row));
            
            // Update sort indicators
            header.parentNode.querySelectorAll('th').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
                delete th.dataset.sort;
            });
            
            header.classList.add(`sort-${sortOrder}`);
            header.dataset.sort = sortOrder;
        }

        initRowSelection() {
            const selectAllCheckbox = document.querySelector('th input[type="checkbox"], input[name="selection_all"]');
            const rowCheckboxes = document.querySelectorAll('td input[type="checkbox"], input[name="selection[]"]');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', () => {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    this.updateBatchActions();
                    this.updateRowHighlight();
                });
            }
            
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    this.updateSelectAllState();
                    this.updateBatchActions();
                    this.updateRowHighlight();
                });
            });

            // Initial state
            this.updateSelectAllState();
            this.updateBatchActions();
        }

        updateSelectAllState() {
            const selectAllCheckbox = document.querySelector('th input[type="checkbox"], input[name="selection_all"]');
            const rowCheckboxes = document.querySelectorAll('td input[type="checkbox"], input[name="selection[]"]');
            
            if (selectAllCheckbox && rowCheckboxes.length > 0) {
                const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
                
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            }
        }

        updateBatchActions() {
            const checkedBoxes = document.querySelectorAll('td input[type="checkbox"]:checked, input[name="selection[]"]:checked');
            const batchActions = document.querySelectorAll('.batch-action-btn, .batch-actions');
            
            batchActions.forEach(action => {
                if (checkedBoxes.length > 0) {
                    action.style.display = 'inline-block';
                    action.classList.add('show');
                    
                    // Add animation
                    action.style.opacity = '0';
                    action.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        action.style.transition = 'all 0.3s ease';
                        action.style.opacity = '1';
                        action.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    action.style.transition = 'all 0.3s ease';
                    action.style.opacity = '0';
                    action.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        action.style.display = 'none';
                        action.classList.remove('show');
                    }, 300);
                }
            });
            
            // Update counters
            const counters = document.querySelectorAll('.selection-counter, .selected-count');
            counters.forEach(counter => {
                counter.textContent = checkedBoxes.length;
            });
        }

        updateRowHighlight() {
            const rowCheckboxes = document.querySelectorAll('td input[type="checkbox"], input[name="selection[]"]');
            
            rowCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    if (checkbox.checked) {
                        row.classList.add('table-warning');
                        row.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
                    } else {
                        row.classList.remove('table-warning');
                        row.style.backgroundColor = '';
                    }
                }
            });
        }

        initResponsiveTables() {
            const tables = document.querySelectorAll('.table-responsive');
            
            tables.forEach(tableWrapper => {
                const table = tableWrapper.querySelector('table');
                if (table) {
                    this.makeTableResponsive(table);
                }
            });

            // Handle window resize
            window.addEventListener('resize', () => {
                this.handleTableResize();
            });
        }

        makeTableResponsive(table) {
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            // Store header texts for mobile view
            headers.forEach((header, index) => {
                const headerText = header.textContent.trim();
                rows.forEach(row => {
                    const cell = row.cells[index];
                    if (cell) {
                        cell.setAttribute('data-label', headerText);
                    }
                });
            });

            // Add responsive class
            table.classList.add('table-responsive-stack');
        }

        handleTableResize() {
            const tables = document.querySelectorAll('.table-responsive table');
            
            tables.forEach(table => {
                if (window.innerWidth < 768) {
                    table.classList.add('table-mobile');
                } else {
                    table.classList.remove('table-mobile');
                }
            });
        }

        initImagePreviews() {
            const images = document.querySelectorAll('.table img, .img-thumbnail');
            
            images.forEach(img => {
                img.addEventListener('mouseenter', () => {
                    img.style.transform = 'scale(1.1)';
                    img.style.zIndex = '10';
                    img.style.position = 'relative';
                    img.style.transition = 'transform 0.2s ease';
                    img.style.cursor = 'pointer';
                });
                
                img.addEventListener('mouseleave', () => {
                    img.style.transform = 'scale(1)';
                    img.style.zIndex = 'auto';
                    img.style.position = '';
                });

                // Click to enlarge
                img.addEventListener('click', () => {
                    this.showImageModal(img);
                });
            });
        }

        showImageModal(img) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Podgląd obrazu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img src="${img.src}" alt="${img.alt}" class="img-fluid">
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            if (typeof bootstrap !== 'undefined') {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
            }
        }

        initTableFilters() {
            const filterInputs = document.querySelectorAll('.table-filter');
            let filterTimeout;

            filterInputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(() => {
                        this.applyTableFilters();
                    }, 300);
                });
            });
        }

        applyTableFilters() {
            const table = document.querySelector('.table tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            const filters = {};

            // Collect filter values
            document.querySelectorAll('.table-filter').forEach(input => {
                const column = input.dataset.column;
                const value = input.value.toLowerCase().trim();
                if (value) {
                    filters[column] = value;
                }
            });

            // Apply filters
            rows.forEach(row => {
                let visible = true;
                
                Object.keys(filters).forEach(column => {
                    const cell = row.querySelector(`[data-column="${column}"]`);
                    if (cell) {
                        const cellText = cell.textContent.toLowerCase();
                        if (!cellText.includes(filters[column])) {
                            visible = false;
                        }
                    }
                });

                row.style.display = visible ? '' : 'none';
            });
        }

        initBatchActions() {
            // Handle batch form submissions
            const batchForms = document.querySelectorAll('.batch-form');
            
            batchForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
                    
                    if (checkedBoxes.length === 0) {
                        e.preventDefault();
                        alert('Wybierz co najmniej jeden element');
                        return;
                    }

                    // Add selected IDs to form
                    const idsInput = form.querySelector('input[name="ids"]');
                    if (idsInput) {
                        const ids = Array.from(checkedBoxes).map(cb => cb.value);
                        idsInput.value = ids.join(',');
                    }
                });
            });
        }
    }

    // Store reference globally to prevent redefinition
    window.TablesComponent = TablesComponent;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.tablesComponentInstance) {
            window.tablesComponentInstance = new TablesComponent();
        }
    });
}