<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model common\models\LoginForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Logowanie';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <div class="page-content">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h1 class="login-title"><?= Html::encode($this->title) ?></h1>
                    <p class="login-subtitle">Zaloguj się do swojego konta, aby uzyskać dostęp do galerii zdjęć</p>
                </div>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'login-form'],
                    'fieldConfig' => [
                        'template' => "
                            <div class=\"form-group\">
                                {label}
                                <div class=\"input-wrapper\">
                                    {input}
                                    <div class=\"input-icon\"></div>
                                </div>
                                {error}
                            </div>
                        ",
                        'labelOptions' => ['class' => 'form-label'],
                        'inputOptions' => ['class' => 'form-control'],
                        'errorOptions' => ['class' => 'form-error'],
                    ],
                ]); ?>
                
                <div class="form-fields">
                    <?= $form->field($model, 'username')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Wprowadź nazwę użytkownika',
                        'autocomplete' => 'username',
                        'id' => 'username-input'
                    ])->label('Nazwa użytkownika') ?>
                    
                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Wprowadź hasło',
                        'autocomplete' => 'current-password',
                        'id' => 'password-input'
                    ])->label('Hasło') ?>
                    
                    <?= $form->field($model, 'rememberMe')->checkbox([
                        'template' => "
                            <div class=\"form-check\">
                                {input}
                                <label class=\"form-check-label\" for=\"loginform-rememberme\">{label}</label>
                                {error}
                            </div>
                        ",
                        'labelOptions' => ['class' => 'form-check-label'],
                        'inputOptions' => ['class' => 'form-check-input'],
                    ])->label('Zapamiętaj mnie') ?>
                </div>
                
                <div class="form-actions">
                    <?= Html::submitButton('Zaloguj się', [
                        'class' => 'btn btn-primary btn-login',
                        'name' => 'login-button',
                        'id' => 'login-submit'
                    ]) ?>
                </div>
                
                <?php ActiveForm::end(); ?>
                
                <div class="login-footer">
                    <p class="help-text">
                        <i class="fas fa-info-circle"></i>
                        Nie masz konta? Skontaktuj się z administratorem.
                    </p>
                </div>
            </div>
            
            <!-- Background decoration -->
            <div class="login-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
            </div>
        </div>
    </div>
</div>

<style>
.login-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 2rem 0;
}

.login-card {
    background: var(--background);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: 3rem;
    width: 100%;
    max-width: 450px;
    border: 1px solid var(--border);
    position: relative;
    z-index: 1;
    animation: slideInUp 0.8s ease-out;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-icon {
    background: var(--gradient-primary);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: var(--shadow);
}

.login-icon i {
    font-size: 2.5rem;
    color: white;
}

.login-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.login-subtitle {
    color: var(--text-secondary);
    margin-bottom: 0;
    line-height: 1.5;
}

.login-form {
    position: relative;
}

.form-fields {
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.input-wrapper {
    position: relative;
}

.form-control {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    font-family: inherit;
    font-size: 1rem;
    transition: var(--animation);
    background: var(--background);
    color: var(--text-primary);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.form-control:hover {
    border-color: var(--primary-color);
    transform: translateY(-1px);
}

.input-wrapper::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background-size: contain;
    opacity: 0.5;
    transition: var(--animation);
}

.form-group:nth-child(1) .input-wrapper::before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%236b7280' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
}

.form-group:nth-child(2) .input-wrapper::before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%236b7280' viewBox='0 0 24 24'%3E%3Cpath d='M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z'/%3E%3C/svg%3E");
}

.form-control:focus + .input-icon,
.form-group.focused .input-wrapper::before {
    opacity: 1;
    color: var(--primary-color);
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
}

.form-check-input {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
    accent-color: var(--primary-color);
    border-radius: 4px;
}

.form-check-label {
    cursor: pointer;
    font-weight: 400;
    color: var(--text-secondary);
    font-size: 0.875rem;
    user-select: none;
}

.form-error {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.form-error::before {
    content: '⚠';
    font-size: 1rem;
}

.form-actions {
    margin-bottom: 2rem;
}

.btn-login {
    width: 100%;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: relative;
    overflow: hidden;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: var(--animation);
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 0.5rem;
    display: inline-block;
}

.login-footer {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}

.help-text {
    color: var(--text-light);
    font-size: 0.875rem;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.help-text i {
    color: var(--primary-color);
}

/* Background decoration */
.login-decoration {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
}

.circle-1 {
    width: 200px;
    height: 200px;
    top: 10%;
    left: -10%;
    animation-delay: 0s;
}

.circle-2 {
    width: 300px;
    height: 300px;
    bottom: 10%;
    right: -15%;
    animation-delay: 2s;
}

.circle-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    right: 10%;
    animation-delay: 4s;
}

/* Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-card {
        padding: 2rem 1.5rem;
        margin: 1rem;
        max-width: none;
    }
    
    .login-title {
        font-size: 1.75rem;
    }
    
    .login-icon {
        width: 60px;
        height: 60px;
    }
    
    .login-icon i {
        font-size: 2rem;
    }
    
    .circle-1, .circle-2, .circle-3 {
        display: none;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 1rem 0;
    }
    
    .login-card {
        padding: 1.5rem 1rem;
    }
    
    .form-control {
        padding: 0.875rem 0.875rem 0.875rem 2.5rem;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .login-card {
        border: 2px solid var(--text-primary);
    }
    
    .decoration-circle {
        display: none;
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .login-card {
        animation: none;
    }
    
    .decoration-circle {
        animation: none;
    }
    
    .form-control {
        transition: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const submitButton = document.getElementById('login-submit');
    const usernameInput = document.getElementById('username-input');
    const passwordInput = document.getElementById('password-input');
    
    // Focus state management
    function handleFocus(input) {
        input.closest('.form-group').classList.add('focused');
    }
    
    function handleBlur(input) {
        if (!input.value) {
            input.closest('.form-group').classList.remove('focused');
        }
    }
    
    // Add focus/blur handlers
    [usernameInput, passwordInput].forEach(input => {
        if (input) {
            input.addEventListener('focus', () => handleFocus(input));
            input.addEventListener('blur', () => handleBlur(input));
            
            // Check initial state
            if (input.value) {
                handleFocus(input);
            }
        }
    });
    
    // Form submission with loading state
    loginForm.addEventListener('submit', function() {
        submitButton.classList.add('loading');
        submitButton.disabled = true;
        
        // Re-enable after 5 seconds as failsafe
        setTimeout(() => {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
        }, 5000);
    });
    
    // Password visibility toggle
    const passwordField = passwordInput.closest('.form-group');
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'password-toggle';
    toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    toggleButton.style.cssText = `
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-light);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        transition: var(--animation);
    `;
    
    toggleButton.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        
        this.style.color = type === 'password' ? 'var(--text-light)' : 'var(--primary-color)';
    });
    
    toggleButton.addEventListener('mouseenter', function() {
        this.style.color = 'var(--primary-color)';
        this.style.backgroundColor = 'var(--surface)';
    });
    
    toggleButton.addEventListener('mouseleave', function() {
        if (passwordInput.getAttribute('type') === 'password') {
            this.style.color = 'var(--text-light)';
            this.style.backgroundColor = 'transparent';
        }
    });
    
    passwordField.querySelector('.input-wrapper').appendChild(toggleButton);
    
    // Auto-focus first empty field
    if (!usernameInput.value) {
        usernameInput.focus();
    } else if (!passwordInput.value) {
        passwordInput.focus();
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (e.target === usernameInput || e.target === passwordInput)) {
            loginForm.submit();
        }
    });
    
    // Real-time validation feedback
    function validateField(input, rules) {
        const value = input.value.trim();
        const group = input.closest('.form-group');
        let isValid = true;
        let message = '';
        
        // Remove existing error
        const existingError = group.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Apply validation rules
        rules.forEach(rule => {
            if (!isValid) return;
            
            if (rule.type === 'required' && !value) {
                isValid = false;
                message = rule.message || 'To pole jest wymagane';
            } else if (rule.type === 'minLength' && value.length < rule.value) {
                isValid = false;
                message = rule.message || `Minimum ${rule.value} znaków`;
            }
        });
        
        // Update UI
        input.classList.toggle('error', !isValid);
        group.classList.toggle('has-error', !isValid);
        
        if (!isValid && value.length > 0) {
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = message;
            group.appendChild(error);
        }
        
        return isValid;
    }
    
    // Validation on blur
    usernameInput.addEventListener('blur', function() {
        validateField(this, [
            { type: 'required', message: 'Nazwa użytkownika jest wymagana' },
            { type: 'minLength', value: 3, message: 'Minimum 3 znaki' }
        ]);
    });
    
    passwordInput.addEventListener('blur', function() {
        validateField(this, [
            { type: 'required', message: 'Hasło jest wymagane' },
            { type: 'minLength', value: 6, message: 'Minimum 6 znaków' }
        ]);
    });
    
    // Clear validation on input
    [usernameInput, passwordInput].forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            this.closest('.form-group').classList.remove('has-error');
            
            const error = this.closest('.form-group').querySelector('.form-error');
            if (error) {
                error.remove();
            }
        });
    });
});

// Additional styles for validation
const validationStyles = document.createElement('style');
validationStyles.textContent = `
    .form-control.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .form-group.has-error .form-label {
        color: #ef4444;
    }
    
    .password-toggle:hover {
        background-color: var(--surface) !important;
    }
`;
document.head.appendChild(validationStyles);
</script>