<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerControllerAssets($this, 'ai');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $settings array */
/* @var $providers array */

$this->title = 'Ustawienia AI';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ai-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="ai-config-section">
                <h4 class="mb-3">
                    <i class="fas fa-robot me-2"></i>Konfiguracja AI
                </h4>
                <p class="mb-4">Skonfiguruj integrację z usługami AI do automatycznej analizy zdjęć</p>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'ai-settings-form',
                    'action' => ['update'],
                    'options' => ['class' => 'needs-validation'],
                ]); ?>

                <div class="mb-3">
                    <label class="form-label text-white">Dostawca AI</label>
                    <select name="provider" id="ai-provider" class="form-select" required>
                        <option value="">Wybierz dostawcę...</option>
                        <?php foreach ($providers as $key => $provider): ?>
                            <option value="<?= $key ?>" <?= $settings['provider'] === $key ? 'selected' : '' ?>>
                                <?= Html::encode($provider['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-white-50">Wybierz usługę AI do analizy zdjęć</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Klucz API</label>
                    <input type="password" class="form-control" name="api_key" 
                           value="<?= !empty($settings['api_key']) ? '********' : '' ?>"
                           placeholder="<?= empty($settings['api_key']) ? 'Wprowadź klucz API' : 'Zachowaj obecny klucz' ?>">
                    <div class="form-text text-white-50">Klucz dostępu do API wybranego dostawcy</div>
                </div>

                <!-- Provider-specific fields -->
                <div class="openai-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label text-white">Model OpenAI</label>
                        <select name="openai_model" class="form-select">
                            <option value="gpt-4-vision-preview" <?= ($settings['openai_model'] ?? '') === 'gpt-4-vision-preview' ? 'selected' : '' ?>>GPT-4 Vision Preview</option>
                            <option value="gpt-4o" <?= ($settings['openai_model'] ?? '') === 'gpt-4o' ? 'selected' : '' ?>>GPT-4 Omni</option>
                        </select>
                    </div>
                </div>

                <div class="anthropic-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label text-white">Model Claude</label>
                        <select name="anthropic_model" class="form-select">
                            <option value="claude-3-opus-20240229" <?= ($settings['anthropic_model'] ?? '') === 'claude-3-opus-20240229' ? 'selected' : '' ?>>Claude 3 Opus</option>
                            <option value="claude-3-sonnet-20240229" <?= ($settings['anthropic_model'] ?? '') === 'claude-3-sonnet-20240229' ? 'selected' : '' ?>>Claude 3 Sonnet</option>
                        </select>
                    </div>
                </div>

                <div class="google-field provider-field" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label text-white">Model Gemini</label>
                        <select name="google_model" class="form-select">
                            <option value="gemini-pro-vision" <?= ($settings['google_model'] ?? '') === 'gemini-pro-vision' ? 'selected' : '' ?>>Gemini Pro Vision</option>
                            <option value="gemini-1.5-pro-latest" <?= ($settings['google_model'] ?? '') === 'gemini-1.5-pro-latest' ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-save me-2"></i>Zapisz ustawienia
                    </button>
                    <button type="button" class="btn btn-outline-light" id="test-ai-btn">
    <i class="fas fa-vial me-2"></i>Testuj AI
</button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ai-form-section">
                <h5>
                    <i class="fas fa-info-circle me-2"></i>Informacje o dostawcach AI
                </h5>

                <!-- Provider info cards -->
                <div class="openai-info provider-info" style="display: none;">
                    <h6>OpenAI GPT-4 Vision</h6>
                    <ul>
                        <li><i class="fas fa-eye me-2"></i>Zaawansowana analiza obrazów</li>
                        <li><i class="fas fa-tags me-2"></i>Automatyczne tagowanie</li>
                        <li><i class="fas fa-comment me-2"></i>Generowanie opisów</li>
                        <li><i class="fas fa-search me-2"></i>Wykrywanie obiektów i scen</li>
                    </ul>
                </div>

                <div class="anthropic-info provider-info" style="display: none;">
                    <h6>Anthropic Claude 3</h6>
                    <ul>
                        <li><i class="fas fa-eye me-2"></i>Precyzyjna analiza wizualna</li>
                        <li><i class="fas fa-brain me-2"></i>Inteligentne rozumowanie</li>
                        <li><i class="fas fa-palette me-2"></i>Analiza kompozycji i kolorów</li>
                        <li><i class="fas fa-user-friends me-2"></i>Rozpoznawanie emocji</li>
                    </ul>
                </div>

                <div class="google-info provider-info" style="display: none;">
                    <h6>Google Gemini Vision</h6>
                    <ul>
                        <li><i class="fas fa-globe me-2"></i>Wielojęzyczne opisy</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>Rozpoznawanie miejsc</li>
                        <li><i class="fas fa-landmark me-2"></i>Identyfikacja zabytków</li>
                        <li><i class="fas fa-leaf me-2"></i>Klasyfikacja natury</li>
                    </ul>
                </div>
            </div>

            <div class="ai-form-section">
                <h5>
                    <i class="fas fa-cogs me-2"></i>Funkcje AI
                </h5>
                
                <div class="ai-capabilities-list">
                    <div class="ai-capability-item">
                        <i class="fas fa-tags text-primary"></i>
                        <div>
                            <strong>Auto-tagowanie</strong>
                            <small class="text-muted d-block">Automatyczne przypisywanie tagów</small>
                        </div>
                    </div>
                    
                    <div class="ai-capability-item">
                        <i class="fas fa-comment text-info"></i>
                        <div>
                            <strong>Opisy zdjęć</strong>
                            <small class="text-muted d-block">Generowanie opisów treści</small>
                        </div>
                    </div>
                    
                    <div class="ai-capability-item">
                        <i class="fas fa-search text-success"></i>
                        <div>
                            <strong>Wykrywanie obiektów</strong>
                            <small class="text-muted d-block">Identyfikacja elementów obrazu</small>
                        </div>
                    </div>
                    
                    <div class="ai-capability-item">
                        <i class="fas fa-palette text-warning"></i>
                        <div>
                            <strong>Analiza kolorów</strong>
                            <small class="text-muted d-block">Badanie palety barwnej</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ai-settings-warning">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne informacje</h6>
                <ul class="mb-0">
                    <li>Analiza AI wymaga połączenia internetowego</li>
                    <li>Korzystanie z API może generować koszty</li>
                    <li>Prywatność: zdjęcia są wysyłane do zewnętrznych usług</li>
                    <li>Wyniki analizy mogą być niedoskonałe</li>
                    <li>Zachowaj bezpiecznie klucze API</li>
                </ul>
            </div>
        </div>
    </div>
</div>