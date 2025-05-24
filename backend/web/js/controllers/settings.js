/**
 * Settings Controller JavaScript
 * Zasobnik B - Photo Management System
 */

class SettingsController {
    constructor() {
        this.init();
    }

    init() {
        if (this.isS3Page()) {
            this.initS3Page();
        }
        
        if (this.isWatermarkPage()) {
            this.initWatermarkPage();
        }
        
        if (this.isThumbnailSizePage()) {
            this.initThumbnailSizePage();
        }
        
        this.initCommonFeatures();
    }

    isS3Page() {
        return window.location.pathname.includes('/s3/');
    }

    isWatermarkPage() {
        return window.location.pathname.includes('/watermark/');
    }

    isThumbnailSizePage() {
        return window.location.pathname.includes('/thumbnail-size/');
    }

    initCommonFeatures() {
        this.initTooltips();
        this.initFormValidation();
    }

    initS3Page() {
        this.initS3TestButton();
    }

    initWatermarkPage() {
        this.initWatermarkTypeToggle();
        this.initOpacitySlider();
        this.initPreviewButton();
    }

    initThumbnailSizePage() {
        this.initDimensionPreview();
        this.initAspectRatioLock();
    }

    // S3 Settings functionality
    initS3TestButton() {
        const testBtn = document.getElementById('test-connection-btn');
        if (!testBtn) return;

        testBtn.addEventListener('click', async () => {
            const button = testBtn;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testowanie...';
            
            try {
                const response = await fetch('/s3/test', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': this.getCsrfToken()
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('Test połączenia zakończony sukcesem!', 'success');
                } else {
                    this.showToast('Test połączenia nieudany: ' + (data.message || 'Nieznany błąd'), 'error');
                }
            } catch (error) {
                this.showToast('Błąd podczas testowania połączenia', 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    }

    // Watermark Settings functionality
    initWatermarkTypeToggle() {
        const typeRadios = document.querySelectorAll('input[name="type"]');
        const textFields = document.querySelector('.watermark-text-fields');
        const imageFields = document.querySelector('.watermark-image-fields');
        
        if (!typeRadios.length || !textFields || !imageFields) return;
        
        const toggleFields = () => {
            const selectedType = document.querySelector('input[name="type"]:checked')?.value;
            
            if (selectedType === 'text') {
                textFields.style.display = 'block';
                imageFields.style.display = 'none';
            } else {
                textFields.style.display = 'none';
                imageFields.style.display = 'block';
            }
        };
        
        typeRadios.forEach(radio => {
            radio.addEventListener('change', toggleFields);
        });
        
        // Initial toggle
        toggleFields();
    }

    initOpacitySlider() {
        const opacityRange = document.getElementById('watermark-opacity');
        const opacityValue = document.getElementById('opacity-value');
        
        if (!opacityRange || !opacityValue) return;
        
        opacityRange.addEventListener('input', function() {
            opacityValue.textContent = Math.round(this.value * 100) + '%';
        });
    }

    initPreviewButton() {
        const previewBtn = document.getElementById('preview-watermark-btn');
        if (!previewBtn) return;

        previewBtn.addEventListener('click', async () => {
            const placeholder = document.getElementById('watermark-preview-placeholder');
            const loading = document.getElementById('watermark-preview-loading');
            const result = document.getElementById('watermark-preview-result');
            
            if (!placeholder || !loading || !result) return;
            
            // Show loading
            placeholder.style.display = 'none';
            result.style.display = 'none';
            loading.style.display = 'block';
            
            // Create form data
            const formData = new FormData();
            const typeInput = document.querySelector('input[name="type"]:checked');
            const textInput = document.querySelector('input[name="text"]');
            const positionSelect = document.getElementById('watermark-position');
            const opacityRange = document.getElementById('watermark-opacity');
            const imageUpload = document.getElementById('watermark-image-upload');
            
            if (typeInput) formData.append('type', typeInput.value);
            if (textInput) formData.append('text', textInput.value);
            if (positionSelect) formData.append('position', positionSelect.value);
            if (opacityRange) formData.append('opacity', opacityRange.value);
            
            // Add image if selected
            if (imageUpload && imageUpload.files[0]) {
                formData.append('image', imageUpload.files[0]);
            }
            
            try {
                const response = await fetch('/watermark/preview', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-Token': this.getCsrfToken()
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const previewImage = document.getElementById('watermark-preview-image');
                    if (previewImage) {
                        previewImage.src = data.preview;
                        result.style.display = 'block';
                    }
                } else {
                    this.showToast('Błąd podczas generowania podglądu: ' + (data.message || 'Nieznany błąd'), 'error');
                    placeholder.style.display = 'block';
                }
            } catch (error) {
                this.showToast('Błąd podczas generowania podglądu', 'error');
                placeholder.style.display = 'block';
            } finally {
                loading.style.display = 'none';
            }
        });
    }

    // Thumbnail Size Settings functionality
    initDimensionPreview() {
        const widthInput = document.getElementById('thumbnailsize-width');
        const heightInput = document.getElementById('thumbnailsize-height');
        const previewBox = document.getElementById('preview-box');
        const previewLabel = document.getElementById('preview-label');
        const nameInput = document.getElementById('thumbnailsize-name');
        
        if (!widthInput || !heightInput || !previewBox || !previewLabel) return;
        
        const updatePreview = () => {
            const width = parseInt(widthInput.value) || 0;
            const height = parseInt(heightInput.value) || 0;
            
            if (width > 0 && height > 0) {
                // Scale down the preview to fit in the container
                const maxPreviewSize = 150;
                const scale = Math.min(maxPreviewSize / width, maxPreviewSize / height);
                const previewWidth = width * scale;
                const previewHeight = height * scale;
                
                previewBox.style.width = previewWidth + 'px';
                previewBox.style.height = previewHeight + 'px';
                previewLabel.textContent = width + '×' + height + 'px';
                
                // Update name suggestion
                if (nameInput && !nameInput.value) {
                    if (width <= 200 && height <= 200) {
                        nameInput.placeholder = 'small';
                    } else if (width <= 400 && height <= 400) {
                        nameInput.placeholder = 'medium';
                    } else {
                        nameInput.placeholder = 'large';
                    }
                }
            } else {
                previewBox.style.width = '50px';
                previewBox.style.height = '50px';
                previewLabel.textContent = 'Wprowadź wymiary';
                if (nameInput) nameInput.placeholder = 'np. small, medium, large';
            }
        };
        
        // Update preview on input change
        widthInput.addEventListener('input', updatePreview);
        heightInput.addEventListener('input', updatePreview);
        
        // Initialize preview
        updatePreview();
    }

    initAspectRatioLock() {
        const widthInput = document.getElementById('thumbnailsize-width');
        const heightInput = document.getElementById('thumbnailsize-height');
        
        if (!widthInput || !heightInput) return;
        
        let aspectRatioLocked = false;
        let lastRatio = 1;
        
        // Add aspect ratio lock button
        const ratioButton = document.createElement('button');
        ratioButton.type = 'button';
        ratioButton.className = 'btn btn-sm btn-outline-info aspect-ratio-lock-btn';
        ratioButton.innerHTML = '<i class="fas fa-lock-open"></i>';
        ratioButton.title = 'Zablokuj proporcje';
        
        // Insert after height input
        heightInput.parentNode.appendChild(ratioButton);
        
        ratioButton.addEventListener('click', function() {
            aspectRatioLocked = !aspectRatioLocked;
            
            if (aspectRatioLocked) {
                const width = parseInt(widthInput.value) || 1;
                const height = parseInt(heightInput.value) || 1;
                lastRatio = width / height;
                ratioButton.innerHTML = '<i class="fas fa-lock"></i>';
                ratioButton.title = 'Odblokuj proporcje';
                ratioButton.classList.remove('btn-outline-info');
                ratioButton.classList.add('btn-info');
            } else {
                ratioButton.innerHTML = '<i class="fas fa-lock-open"></i>';
                ratioButton.title = 'Zablokuj proporcje';
                ratioButton.classList.remove('btn-info');
                ratioButton.classList.add('btn-outline-info');
            }
        });
        
        // Handle aspect ratio locking
        widthInput.addEventListener('input', function() {
            if (aspectRatioLocked && this.value) {
                const newHeight = Math.round(parseInt(this.value) / lastRatio);
                heightInput.value = newHeight;
                heightInput.dispatchEvent(new Event('input'));
            }
        });
        
        heightInput.addEventListener('input', function() {
            if (aspectRatioLocked && this.value) {
                const newWidth = Math.round(parseInt(this.value) * lastRatio);
                widthInput.value = newWidth;
                widthInput.dispatchEvent(new Event('input'));
            }
        });
    }

    initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                form.classList.add('was-validated');
            });
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
    new SettingsController();
});