// Check if FormsComponent already exists to prevent redeclaration
if (typeof window.FormsComponent !== 'undefined') {
    console.warn('FormsComponent already defined, skipping redefinition');
} else {
    /**
     * Forms Components JavaScript
     */
    class FormsComponent {
        constructor() {
            this.init();
        }

        init() {
            this.initValidation();
            this.initAutoResize();
            this.initFileInputs();
            this.initFormSubmission();
            this.initCharacterCounters();
            this.initDependentFields();
            this.initFormWizard();
        }

        initValidation() {
            const forms = document.querySelectorAll('.needs-validation');
            
            forms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                            
                            // Show custom error message
                            this.showFieldError(firstInvalid);
                        }
                    }
                    form.classList.add('was-validated');
                });

                // Real-time validation
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('blur', () => {
                        this.validateField(input);
                    });

                    input.addEventListener('input', () => {
                        if (input.classList.contains('is-invalid')) {
                            this.validateField(input);
                        }
                    });
                });
            });
        }

        validateField(field) {
            const isValid = field.checkValidity();
            
            field.classList.remove('is-valid', 'is-invalid');
            field.classList.add(isValid ? 'is-valid' : 'is-invalid');

            // Update feedback
            const feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
            if (feedback) {
                feedback.style.display = isValid ? 'none' : 'block';
            }

            return isValid;
        }

        showFieldError(field) {
            const message = field.validationMessage || 'To pole jest wymagane';
            
            // Create or update error tooltip
            let errorTooltip = field.parentNode.querySelector('.field-error-tooltip');
            if (!errorTooltip) {
                errorTooltip = document.createElement('div');
                errorTooltip.className = 'field-error-tooltip alert alert-danger alert-sm mt-1';
                field.parentNode.appendChild(errorTooltip);
            }
            
            errorTooltip.textContent = message;
            errorTooltip.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                errorTooltip.style.display = 'none';
            }, 5000);
        }

        initAutoResize() {
            const textareas = document.querySelectorAll('textarea[data-auto-resize], textarea:not([data-no-resize])');
            
            textareas.forEach(textarea => {
                const resize = () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = Math.max(textarea.scrollHeight, 60) + 'px';
                };
                
                textarea.addEventListener('input', resize);
                textarea.addEventListener('focus', resize);
                
                // Initial resize
                setTimeout(resize, 100);
            });
        }

        initFileInputs() {
            const fileInputs = document.querySelectorAll('input[type="file"]:not(.file-processed)');
            
            fileInputs.forEach(input => {
                input.classList.add('file-processed');
                
                // Skip if already wrapped
                if (input.closest('.file-input-wrapper')) return;
                
                const wrapper = document.createElement('div');
                wrapper.className = 'file-input-wrapper';
                
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-secondary';
                button.innerHTML = '<i class="fas fa-file me-2"></i>Wybierz plik';
                
                const label = document.createElement('span');
                label.className = 'file-input-label ms-2 text-muted';
                label.textContent = 'Nie wybrano pliku';
                
                // Wrap the input
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(button);
                wrapper.appendChild(label);
                wrapper.appendChild(input);
                
                // Hide original input
                input.style.display = 'none';
                
                button.addEventListener('click', () => input.click());
                
                input.addEventListener('change', () => {
                    if (input.files.length > 0) {
                        const fileName = input.files[0].name;
                        const fileSize = this.formatFileSize(input.files[0].size);
                        label.innerHTML = `<i class="fas fa-check text-success me-1"></i>${fileName} <small>(${fileSize})</small>`;
                        label.className = 'file-input-label ms-2 text-success';
                    } else {
                        label.textContent = 'Nie wybrano pliku';
                        label.className = 'file-input-label ms-2 text-muted';
                    }
                });

                // Drag and drop support
                this.initDragAndDrop(wrapper, input, label);
            });
        }

        initDragAndDrop(wrapper, input, label) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                wrapper.addEventListener(eventName, this.preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                wrapper.addEventListener(eventName, () => {
                    wrapper.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                wrapper.addEventListener(eventName, () => {
                    wrapper.classList.remove('drag-over');
                }, false);
            });

            wrapper.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    input.dispatchEvent(new Event('change'));
                }
            }, false);
        }

        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        initFormSubmission() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn && !submitBtn.disabled && !submitBtn.classList.contains('no-loading')) {
                        this.showSubmitLoading(submitBtn);
                    }
                });
            });
        }

        showSubmitLoading(button) {
            const originalContent = button.innerHTML || button.value;
            
            button.disabled = true;
            button.dataset.originalContent = originalContent;
            
            if (button.tagName === 'BUTTON') {
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Przetwarzanie...';
            } else {
                button.value = 'Przetwarzanie...';
            }
            
            // Re-enable after delay (in case of validation errors or redirects)  
            setTimeout(() => {
                this.hideSubmitLoading(button);
            }, 10000);
        }

        hideSubmitLoading(button) {
            if (button.dataset.originalContent) {
                button.disabled = false;
                
                if (button.tagName === 'BUTTON') {
                    button.innerHTML = button.dataset.originalContent;
                } else {
                    button.value = button.dataset.originalContent;
                }
                
                delete button.dataset.originalContent;
            }
        }

        initCharacterCounters() {
            const fieldsWithCounters = document.querySelectorAll('[data-max-length]');
            
            fieldsWithCounters.forEach(field => {
                const maxLength = parseInt(field.dataset.maxLength);
                
                const counter = document.createElement('div');
                counter.className = 'character-counter text-muted small mt-1';
                field.parentNode.appendChild(counter);
                
                const updateCounter = () => {
                    const currentLength = field.value.length;
                    const remaining = maxLength - currentLength;
                    
                    counter.textContent = `${currentLength}/${maxLength} znaków`;
                    
                    if (remaining < 10) {
                        counter.className = 'character-counter text-danger small mt-1';
                    } else if (remaining < 50) {
                        counter.className = 'character-counter text-warning small mt-1';
                    } else {
                        counter.className = 'character-counter text-muted small mt-1';
                    }
                };
                
                field.addEventListener('input', updateCounter);
                updateCounter(); // Initial count
            });
        }

        initDependentFields() {
            const dependentFields = document.querySelectorAll('[data-depends-on]');
            
            dependentFields.forEach(field => {
                const dependsOn = field.dataset.dependsOn;
                const dependsValue = field.dataset.dependsValue;
                const parentField = document.querySelector(`[name="${dependsOn}"]`);
                
                if (parentField) {
                    const toggleField = () => {
                        const currentValue = parentField.value;
                        const shouldShow = !dependsValue || currentValue === dependsValue;
                        
                        const container = field.closest('.form-group, .mb-3, .field-container') || field.parentNode;
                        
                        if (shouldShow) {
                            container.style.display = 'block';
                            container.style.opacity = '0';
                            container.style.transform = 'translateY(-10px)';
                            
                            setTimeout(() => {
                                container.style.transition = 'all 0.3s ease';
                                container.style.opacity = '1';
                                container.style.transform = 'translateY(0)';
                            }, 10);
                        } else {
                            container.style.transition = 'all 0.3s ease';
                            container.style.opacity = '0';
                            container.style.transform = 'translateY(-10px)';
                            
                            setTimeout(() => {
                                container.style.display = 'none';
                                field.value = ''; // Clear hidden field
                            }, 300);
                        }
                    };
                    
                    parentField.addEventListener('change', toggleField);
                    toggleField(); // Initial state
                }
            });
        }

        initFormWizard() {
            const wizards = document.querySelectorAll('.form-wizard');
            
            wizards.forEach(wizard => {
                const steps = wizard.querySelectorAll('.wizard-step');
                const nextBtns = wizard.querySelectorAll('.wizard-next');
                const prevBtns = wizard.querySelectorAll('.wizard-prev');
                const progress = wizard.querySelector('.wizard-progress');
                
                let currentStep = 0;
                
                const showStep = (stepIndex) => {
                    steps.forEach((step, index) => {
                        step.style.display = index === stepIndex ? 'block' : 'none';
                    });
                    
                    if (progress) {
                        const progressPercent = ((stepIndex + 1) / steps.length) * 100;
                        progress.style.width = progressPercent + '%';
                    }
                    
                    // Update navigation buttons
                    wizard.querySelectorAll('.wizard-prev').forEach(btn => {
                        btn.style.display = stepIndex > 0 ? 'inline-block' : 'none';
                    });
                    
                    wizard.querySelectorAll('.wizard-next').forEach(btn => {
                        btn.textContent = stepIndex === steps.length - 1 ? 'Zakończ' : 'Dalej';
                    });
                };
                
                nextBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const currentStepElement = steps[currentStep];
                        const inputs = currentStepElement.querySelectorAll('input, textarea, select');
                        let isValid = true;
                        
                        inputs.forEach(input => {
                            if (!this.validateField(input)) {
                                isValid = false;
                            }
                        });
                        
                        if (isValid && currentStep < steps.length - 1) {
                            currentStep++;
                            showStep(currentStep);
                        }
                    });
                });
                
                prevBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (currentStep > 0) {
                            currentStep--;
                            showStep(currentStep);
                        }
                    });
                });
                
                showStep(0); // Initial step
            });
        }
    }

    // Store reference globally to prevent redefinition
    window.FormsComponent = FormsComponent;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.formsComponentInstance) {
            window.formsComponentInstance = new FormsComponent();
        }
    });
}