<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Settings;
\backend\assets\AppAsset::registerControllerAssets($this, 'ai');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

$this->title = 'Ustawienia AI';
$this->params['breadcrumbs'][] = $this->title;

// Pobierz statystyki AI
$aiCurrentCount = (int) Settings::getSetting('ai.current_count', 0);
$aiMonthlyLimit = (int) Settings::getSetting('ai.monthly_limit', 1000);
$aiUsagePercent = $aiMonthlyLimit > 0 ? round(($aiCurrentCount / $aiMonthlyLimit) * 100, 1) : 0;
?>
<div class="ai-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <!-- Statystyki wykorzystania AI -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0" style="color: black;">
                        <i class="fas fa-robot me-2"></i>Wykorzystanie AI w tym miesiącu
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar <?= $aiUsagePercent >= 90 ? 'bg-danger' : ($aiUsagePercent >= 70 ? 'bg-warning' : 'bg-success') ?>" 
                                     role="progressbar" style="width: <?= min($aiUsagePercent, 100) ?>%">
                                    <?= $aiUsagePercent ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= number_format($aiCurrentCount) ?> z <?= number_format($aiMonthlyLimit) ?> zapytań
                            </small>
                        </div>
                        <div class="col-4 text-end">
                            <span class="h4 <?= $aiUsagePercent >= 90 ? 'text-danger' : 'text-primary' ?>">
                                <?= number_format($aiMonthlyLimit - $aiCurrentCount) ?>
                            </span>
                            <br><small class="text-muted">pozostało</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($aiUsagePercent >= 90): ?>
        <div class="alert alert-danger mb-4">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Ostrzeżenie o limicie AI</h6>
            <p class="mb-0">Wykorzystano <?= $aiUsagePercent ?>% miesięcznego limitu zapytań AI. Zwiększ limit w ustawieniach poniżej lub zresetuj licznik.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="ai-config-section">
                <h4 class="mb-3">
                    <i class="fas fa-robot me-2"></i>Konfiguracja AI
                </h4>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'ai-settings-form',
                    'action' => ['update'],
                    'options' => ['class' => 'needs-validation'],
                ]); ?>

                <div class="mb-3">
                    <label class="form-label">Dostawca AI</label>
                    <select name="provider" id="ai-provider" class="form-select" required>
                        <option value="">Wybierz dostawcę...</option>
                        <?php foreach ($providers as $key => $provider): ?>
                            <option value="<?= $key ?>" <?= $settings['provider'] === $key ? 'selected' : '' ?>>
                                <?= Html::encode($provider['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Klucz API</label>
                    <input type="password" class="form-control" name="api_key" 
                           value="<?= !empty($settings['api_key']) ? '********' : '' ?>"
                           placeholder="<?= empty($settings['api_key']) ? 'Wprowadź klucz API' : 'Zachowaj obecny klucz' ?>">
                </div>

                <!-- Provider-specific fields -->
                <div class="openai-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Model OpenAI</label>
                        <select name="openai_model" class="form-select">
                            <option value="gpt-4-vision-preview" <?= ($settings['openai_model'] ?? '') === 'gpt-4-vision-preview' ? 'selected' : '' ?>>GPT-4 Vision Preview</option>
                            <option value="gpt-4o" <?= ($settings['openai_model'] ?? '') === 'gpt-4o' ? 'selected' : '' ?>>GPT-4 Omni</option>
                        </select>
                    </div>
                </div>

                <div class="anthropic-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Model Claude</label>
                        <select name="anthropic_model" class="form-select">
                            <option value="claude-3-opus-20240229" <?= ($settings['anthropic_model'] ?? '') === 'claude-3-opus-20240229' ? 'selected' : '' ?>>Claude 3 Opus</option>
                            <option value="claude-3-sonnet-20240229" <?= ($settings['anthropic_model'] ?? '') === 'claude-3-sonnet-20240229' ? 'selected' : '' ?>>Claude 3 Sonnet</option>
                        </select>
                    </div>
                </div>

                <div class="google-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Model Gemini</label>
                        <select name="google_model" class="form-select">
                            <option value="gemini-pro-vision" <?= ($settings['google_model'] ?? '') === 'gemini-pro-vision' ? 'selected' : '' ?>>Gemini Pro Vision</option>
                            <option value="gemini-1.5-pro-latest" <?= ($settings['google_model'] ?? '') === 'gemini-1.5-pro-latest' ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
                        </select>
                    </div>
                </div>

                <!-- Limity AI -->
                <div class="mb-3">
                    <label class="form-label">Miesięczny limit zapytań AI</label>
                    <input type="number" class="form-control" name="ai_monthly_limit" 
                           value="<?= $aiMonthlyLimit ?>" min="0" max="100000">
                    <div class="form-text">Maksymalna liczba zapytań AI w miesiącu</div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generate_english_descriptions" 
                               value="1" id="generate-english" 
                               <?= Settings::getSetting('ai.generate_english_descriptions', '1') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="generate-english">
                            Generuj opisy w języku angielskim
                        </label>
                        <div class="form-text">AI będzie tworzyć opisy zarówno po polsku jak i po angielsku</div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Zapisz ustawienia
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="test-ai-btn">
                        <i class="fas fa-vial me-2"></i>Testuj AI
                    </button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>

            <!-- Zarządzanie licznikiem AI -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Zarządzanie licznikiem AI
                    </h5>
                </div>
                <div class="card-body">
                    <?php $countersForm = ActiveForm::begin([
                        'id' => 'counters-form',
                        'action' => ['update-counters'],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Resetuj licznik AI</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reset_ai_counter" 
                                           value="1" id="reset-ai">
                                    <label class="form-check-label" for="reset-ai">
                                        Wyzeruj do 0
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-sync me-2"></i>Zresetuj licznik
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ai-form-section">
                <h5>
                    <i class="fas fa-info-circle me-2"></i>Informacje o dostawcach AI
                </h5>

                <!-- Provider info cards -->
                <div class="openai-info provider-info" style="display: none;">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6>OpenAI GPT-4 Vision</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-eye me-2 text-primary"></i>Zaawansowana analiza obrazów</li>
                                <li><i class="fas fa-tags me-2 text-primary"></i>Automatyczne tagowanie</li>
                                <li><i class="fas fa-comment me-2 text-primary"></i>Generowanie opisów PL/EN</li>
                                <li><i class="fas fa-search me-2 text-primary"></i>Wykrywanie obiektów i scen</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="anthropic-info provider-info" style="display: none;">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6>Anthropic Claude 3</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-eye me-2 text-primary"></i>Precyzyjna analiza wizualna</li>
                                <li><i class="fas fa-brain me-2 text-primary"></i>Inteligentne rozumowanie</li>
                                <li><i class="fas fa-palette me-2 text-primary"></i>Analiza kompozycji i kolorów</li>
                                <li><i class="fas fa-globe me-2 text-primary"></i>Opisy dwujęzyczne</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="google-info provider-info" style="display: none;">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6>Google Gemini Vision</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-globe me-2 text-primary"></i>Wielojęzyczne opisy</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-primary"></i>Rozpoznawanie miejsc</li>
                                <li><i class="fas fa-landmark me-2 text-primary"></i>Identyfikacja zabytków</li>
                                <li><i class="fas fa-leaf me-2 text-primary"></i>Klasyfikacja natury</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ai-form-section mt-4">
                <h5>
                    <i class="fas fa-chart-line me-2"></i>Statystyki AI
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Reset licznika AI:</strong></td>
                            <td class="text-end"><?= Settings::getSetting('ai.reset_date', date('Y-m-01')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>AI dostępne:</strong></td>
                            <td class="text-end">
                                <?php if ($aiUsagePercent < 100): ?>
                                    <span class="badge bg-success">Tak</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Limit wyczerpany</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Zapytania w tym miesiącu:</strong></td>
                            <td class="text-end"><span class="badge bg-info"><?= number_format($aiCurrentCount) ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Pozostały limit:</strong></td>
                            <td class="text-end"><span class="badge bg-secondary"><?= number_format($aiMonthlyLimit - $aiCurrentCount) ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide provider-specific fields
    const providerSelect = document.getElementById('ai-provider');
    const providerFields = document.querySelectorAll('.provider-field');
    const providerInfos = document.querySelectorAll('.provider-info');
    
    function toggleProviderFields() {
        const selectedProvider = providerSelect.value;
        
        // Hide all fields and info
        providerFields.forEach(field => field.style.display = 'none');
        providerInfos.forEach(info => info.style.display = 'none');
        
        // Show selected provider fields and info
        if (selectedProvider) {
            const selectedField = document.querySelector('.' + selectedProvider + '-field');
            const selectedInfo = document.querySelector('.' + selectedProvider + '-info');
            
            if (selectedField) selectedField.style.display = 'block';
            if (selectedInfo) selectedInfo.style.display = 'block';
        }
    }
    
    if (providerSelect) {
        providerSelect.addEventListener('change', toggleProviderFields);
        toggleProviderFields(); // Initialize on page load
    }
    
    // Test AI button
    const testAiBtn = document.getElementById('test-ai-btn');
    if (testAiBtn) {
        testAiBtn.addEventListener('click', function() {
            const button = this;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testowanie...';
            
            fetch('<?= \yii\helpers\Url::to(['test']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                const alertClass = data.success ? 'alert-success' : 'alert-danger';
                const iconClass = data.success ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                const alert = document.createElement('div');
                alert.className = `alert ${alertClass} alert-dismissible fade show`;
                alert.innerHTML = `
                    <i class="fas ${iconClass} me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                const container = document.querySelector('.ai-index');
                container.insertBefore(alert, container.firstChild.nextSibling);
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-vial me-2"></i>Testuj AI';
            });
        });
    }
});
</script>