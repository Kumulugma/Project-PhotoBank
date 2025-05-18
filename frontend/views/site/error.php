<?php
/* @var $this yii\web\View */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = 'Wystąpił błąd';

$statusCode = $exception->statusCode ?? 500;
$name = $exception->getName() ?? 'Błąd';
$message = $exception->getMessage() ?? 'Wystąpił nieoczekiwany błąd.';

// Custom error messages for common status codes
$errorMessages = [
    400 => 'Nieprawidłowe żądanie.',
    401 => 'Brak autoryzacji. Zaloguj się, aby uzyskać dostęp.',
    403 => 'Dostęp zabroniony. Nie masz uprawnień do tej strony.',
    404 => 'Strona nie została znaleziona.',
    405 => 'Metoda nie dozwolona.',
    408 => 'Timeout żądania.',
    429 => 'Zbyt wiele żądań. Spróbuj ponownie później.',
    500 => 'Wewnętrzny błąd serwera.',
    502 => 'Błąd bramy.',
    503 => 'Usługa niedostępna.',
    504 => 'Timeout bramy.'
];

$errorMessage = $errorMessages[$statusCode] ?? $message;

// Error icons for different status codes
$errorIcons = [
    400 => 'fa-exclamation-triangle',
    401 => 'fa-lock',
    403 => 'fa-ban',
    404 => 'fa-question-circle',
    405 => 'fa-times-circle',
    500 => 'fa-server',
    502 => 'fa-plug',
    503 => 'fa-tools'
];

$errorIcon = $errorIcons[$statusCode] ?? 'fa-exclamation-triangle';
?>

<div class="container">
    <div class="error-container">
        <!-- Error Icon -->
        <div class="error-icon">
            <i class="fas <?= $errorIcon ?>" aria-hidden="true"></i>
        </div>
        
        <!-- Error Code -->
        <div class="error-code" aria-label="Kod błędu <?= $statusCode ?>">
            <?= $statusCode ?>
        </div>
        
        <!-- Error Title -->
        <h1 class="error-title">
            <?= Html::encode($name) ?>
        </h1>
        
        <!-- Error Message -->
        <p class="error-message">
            <?= Html::encode($errorMessage) ?>
        </p>
        
        <!-- Additional Information (only in debug mode) -->
        <?php if (YII_DEBUG && !empty($message) && $message !== $errorMessage): ?>
            <details class="error-details">
                <summary class="error-details-summary">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    Szczegóły techniczne
                </summary>
                <div class="error-details-content">
                    <pre><?= Html::encode($message) ?></pre>
                    <?php if (isset($exception) && method_exists($exception, 'getFile')): ?>
                        <p class="error-file">
                            <strong>Plik:</strong> <?= Html::encode($exception->getFile()) ?>:<?= $exception->getLine() ?>
                        </p>
                    <?php endif; ?>
                </div>
            </details>
        <?php endif; ?>
        
        <!-- Error Actions -->
        <div class="error-actions">
            <?php if ($statusCode == 401): ?>
                <?= Html::a(
                    '<i class="fas fa-sign-in-alt" aria-hidden="true"></i> Zaloguj się',
                    ['/site/login'],
                    ['class' => 'btn btn-primary']
                ) ?>
            <?php endif; ?>
            
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="history.back()"
                    id="goBackBtn">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Wróć
            </button>
            
            <?= Html::a(
                '<i class="fas fa-home" aria-hidden="true"></i> Strona główna',
                ['/site/index'],
                ['class' => 'btn btn-outline-primary']
            ) ?>
            
            <?php if (!Yii::$app->user->isGuest): ?>
                <?= Html::a(
                    '<i class="fas fa-images" aria-hidden="true"></i> Galeria',
                    ['/gallery/index'],
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
            <?php endif; ?>
        </div>
        
        <!-- Help Section -->
        <section class="error-help">
            <h2 class="help-title">Co możesz zrobić?</h2>
            <div class="help-suggestions">
                <?php if ($statusCode == 404): ?>
                    <div class="help-item">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <div>
                            <h3>Sprawdź adres URL</h3>
                            <p>Upewnij się, że wprowadzony adres jest poprawny.</p>
                        </div>
                    </div>
                    <div class="help-item">
                        <i class="fas fa-sitemap" aria-hidden="true"></i>
                        <div>
                            <h3>Użyj nawigacji</h3>
                            <p>Skorzystaj z menu głównego, aby znaleźć to czego szukasz.</p>
                        </div>
                    </div>
                <?php elseif ($statusCode == 403): ?>
                    <div class="help-item">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                        <div>
                            <h3>Sprawdź uprawnienia</h3>
                            <p>Może potrzebujesz specjalnych uprawnień do tej strony.</p>
                        </div>
                    </div>
                <?php elseif ($statusCode >= 500): ?>
                    <div class="help-item">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        <div>
                            <h3>Spróbuj później</h3>
                            <p>To może być tymczasowy problem. Spróbuj ponownie za chwilę.</p>
                        </div>
                    </div>
                    <div class="help-item">
                        <i class="fas fa-refresh" aria-hidden="true"></i>
                        <div>
                            <h3>Odśwież stronę</h3>
                            <p>Czasami pomaga po prostu odświeżyć stronę.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="help-item">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    <div>
                        <h3>Skontaktuj się z nami</h3>
                        <p>Jeśli problem się powtarza, skontaktuj się z administratorem.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
/* Error page specific styles */
.error-container {
    text-align: center;
    padding: var(--spacing-3xl) var(--spacing-xl);
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    max-width: 800px;
    margin: 0 auto;
}

.error-icon {
    font-size: 6rem;
    color: var(--primary-color);
    margin-bottom: var(--spacing-lg);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
}

.error-code {
    font-size: 8rem;
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
    line-height: 1;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 4px 8px rgba(99, 102, 241, 0.1);
}

.error-title {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
    font-weight: var(--font-weight-semibold);
}

.error-message {
    font-size: var(--font-size-xl);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2xl);
    max-width: 600px;
    line-height: var(--line-height-relaxed);
}

/* Error details (debug mode) */
.error-details {
    margin: var(--spacing-xl) 0;
    text-align: left;
    background: var(--surface);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    max-width: 100%;
}

.error-details-summary {
    padding: var(--spacing-md);
    cursor: pointer;
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--background);
    border-radius: var(--radius) var(--radius) 0 0;
    border-bottom: 1px solid var(--border);
    transition: var(--animation);
}

.error-details-summary:hover {
    background: var(--surface);
}

.error-details-summary::marker {
    display: none;
}

.error-details-content {
    padding: var(--spacing-md);
}

.error-details pre {
    background: var(--background);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: var(--spacing-md);
    overflow-x: auto;
    font-size: var(--font-size-sm);
    line-height: var(--line-height-normal);
    color: var(--text-primary);
    white-space: pre-wrap;
    word-wrap: break-word;
}

.error-file {
    margin-top: var(--spacing-md);
    padding: var(--spacing-sm);
    background: var(--background);
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

/* Error actions */
.error-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: var(--spacing-2xl);
}

/* Help section */
.error-help {
    text-align: left;
    background: var(--surface);
    border-radius: var(--radius);
    padding: var(--spacing-xl);
    border: 1px solid var(--border);
    margin-top: var(--spacing-xl);
    width: 100%;
    max-width: 600px;
}

.help-title {
    text-align: center;
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
    font-size: var(--font-size-xl);
}

.help-suggestions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.help-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--background);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    transition: var(--animation);
}

.help-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.help-item i {
    color: var(--primary-color);
    font-size: var(--font-size-xl);
    margin-top: var(--spacing-xs);
    flex-shrink: 0;
}

.help-item h3 {
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);
}

.help-item p {
    color: var(--text-secondary);
    margin: 0;
    line-height: var(--line-height-relaxed);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .error-container {
        padding: var(--spacing-xl) var(--spacing-md);
    }
    
    .error-code {
        font-size: 6rem;
    }
    
    .error-title {
        font-size: var(--font-size-2xl);
    }
    
    .error-message {
        font-size: var(--font-size-lg);
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .error-actions .btn {
        width: 100%;
        max-width: 280px;
    }
    
    .error-help {
        padding: var(--spacing-md);
    }
    
    .help-item {
        flex-direction: column;
        text-align: center;
    }
    
    .help-item i {
        margin-top: 0;
        margin-bottom: var(--spacing-sm);
    }
}

@media (max-width: 480px) {
    .error-icon {
        font-size: 4rem;
    }
    
    .error-code {
        font-size: 4rem;
    }
    
    .error-title {
        font-size: var(--font-size-xl);
    }
    
    .error-message {
        font-size: var(--font-size-base);
    }
}

/* Animation for error appearance */
.error-container > * {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
    transform: translateY(30px);
}

.error-container > *:nth-child(1) { animation-delay: 0.1s; }
.error-container > *:nth-child(2) { animation-delay: 0.2s; }
.error-container > *:nth-child(3) { animation-delay: 0.3s; }
.error-container > *:nth-child(4) { animation-delay: 0.4s; }
.error-container > *:nth-child(5) { animation-delay: 0.5s; }
.error-container > *:nth-child(6) { animation-delay: 0.6s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dark mode adjustments (if implemented) */
@media (prefers-color-scheme: dark) {
    .error-code {
        text-shadow: 0 4px 8px rgba(163, 180, 252, 0.1);
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .error-container {
        border: 2px solid var(--text-primary);
        border-radius: var(--radius);
    }
    
    .error-help,
    .help-item,
    .error-details {
        border: 2px solid var(--border);
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .error-icon {
        animation: none;
    }
    
    .error-container > * {
        animation: none;
        opacity: 1;
        transform: none;
    }
    
    .help-item:hover {
        transform: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh for server errors after 30 seconds
    <?php if ($statusCode >= 500): ?>
        let countdown = 30;
        let refreshTimer;
        let countdownDisplay;
        
        function createRefreshCountdown() {
            const actionsContainer = document.querySelector('.error-actions');
            if (!actionsContainer) return;
            
            const refreshContainer = document.createElement('div');
            refreshContainer.className = 'refresh-countdown';
            refreshContainer.innerHTML = `
                <p style="margin-top: 1rem; color: var(--text-secondary); font-size: var(--font-size-sm);">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    Automatyczne odświeżenie za: <span id="countdown">${countdown}</span>s
                    <button type="button" id="cancelRefresh" class="btn btn-sm btn-outline-secondary" style="margin-left: 1rem;">
                        Anuluj
                    </button>
                </p>
            `;
            
            actionsContainer.insertAdjacentElement('afterend', refreshContainer);
            countdownDisplay = document.getElementById('countdown');
            
            // Cancel refresh button
            document.getElementById('cancelRefresh').addEventListener('click', function() {
                clearInterval(refreshTimer);
                refreshContainer.remove();
            });
            
            // Start countdown
            refreshTimer = setInterval(function() {
                countdown--;
                countdownDisplay.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(refreshTimer);
                    window.location.reload();
                }
            }, 1000);
        }
        
        // Start countdown after 5 seconds
        setTimeout(createRefreshCountdown, 5000);
    <?php endif; ?>
    
    // Enhanced back button functionality
    const goBackBtn = document.getElementById('goBackBtn');
    if (goBackBtn) {
        // Check if there's history to go back to
        if (window.history.length <= 1) {
            goBackBtn.textContent = 'Strona główna';
            goBackBtn.onclick = function() {
                window.location.href = '/';
            };
        }
        
        // Add keyboard shortcut
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || (e.altKey && e.key === 'ArrowLeft')) {
                goBackBtn.click();
            }
        });
    }
    
    // Error reporting (if needed)
    <?php if (YII_DEBUG): ?>
        // Add error reporting button in debug mode
        const reportError = document.createElement('button');
        reportError.className = 'btn btn-sm btn-outline-secondary';
        reportError.innerHTML = '<i class="fas fa-bug" aria-hidden="true"></i> Zgłoś błąd';
        reportError.onclick = function() {
            const errorInfo = {
                statusCode: <?= $statusCode ?>,
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            };
            
            console.log('Error Report:', errorInfo);
            
            // You could send this to your error tracking service
            navigator.clipboard.writeText(JSON.stringify(errorInfo, null, 2)).then(() => {
                showNotification('Informacje o błędzie skopiowane do schowka', 'info');
            });
        };
        
        document.querySelector('.error-actions').appendChild(reportError);
    <?php endif; ?>
    
    // Accessibility improvements
    setTimeout(function() {
        const errorCode = document.querySelector('.error-code');
        if (errorCode && window.speechSynthesis) {
            const announcement = `Błąd ${errorCode.textContent}. ${document.querySelector('.error-title').textContent}`;
            
            // Only announce for screen readers, not audibly
            const ariaLive = document.createElement('div');
            ariaLive.setAttribute('aria-live', 'polite');
            ariaLive.setAttribute('aria-atomic', 'true');
            ariaLive.className = 'sr-only';
            ariaLive.textContent = announcement;
            document.body.appendChild(ariaLive);
            
            setTimeout(() => ariaLive.remove(), 3000);
        }
    }, 1000);
    
    // Add focus management
    const firstActionButton = document.querySelector('.error-actions .btn');
    if (firstActionButton) {
        firstActionButton.focus();
    }
});

// Global notification function fallback
if (typeof showNotification === 'undefined') {
    window.showNotification = function(message, type) {
        alert(message);
    };
}
</script>