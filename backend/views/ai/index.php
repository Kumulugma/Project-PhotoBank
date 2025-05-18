<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'Integracja AI';
$this->params['breadcrumbs'][] = $this->title;

// Provider options
$providerOptions = [
    'aws' => 'AWS Rekognition',
    'google' => 'Google Vision',
    'openai' => 'OpenAI GPT-4 Vision',
];
?>
<div class="ai-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>Konfiguracja AI
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'ai-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label">Dostawca AI</label>
                        <?= Html::dropDownList('provider', $settings['provider'], $providerOptions, [
                            'class' => 'form-select',
                            'id' => 'ai-provider',
                            'prompt' => '- Wybierz dostawcę -',
                            'required' => true,
                        ]) ?>
                        <div class="form-text">Wybierz dostawcę usług AI do analizy zdjęć</div>
                    </div>

                    <div class="mb-3 provider-field aws-field google-field openai-field" style="display: none;">
                        <label class="form-label">Klucz API</label>
                        <input type="text" class="form-control" name="api_key" 
                               value="<?= Html::encode($settings['api_key']) ?>" 
                               placeholder="<?= empty($settings['api_key']) ? 'Wprowadź klucz API' : 'Zachowaj obecny klucz' ?>">
                        <div class="form-text aws-field" style="display: none;">Klucz dostępu AWS</div>
                        <div class="form-text google-field" style="display: none;">Ścieżka do pliku klucza Google Service Account</div>
                        <div class="form-text openai-field" style="display: none;">Klucz API OpenAI</div>
                    </div>

                    <div class="mb-3 aws-field" style="display: none;">
                        <label class="form-label">Region AWS</label>
                        <input type="text" class="form-control" name="region" 
                               value="<?= Html::encode($settings['region']) ?>"
                               placeholder="np. us-east-1">
                        <div class="form-text">Region AWS dla usługi Rekognition</div>
                    </div>

                    <div class="mb-3 openai-field" style="display: none;">
                        <label class="form-label">Model OpenAI</label>
                        <input type="text" class="form-control" name="model" 
                               value="<?= Html::encode($settings['model']) ?>"
                               placeholder="gpt-4-vision-preview">
                        <div class="form-text">Nazwa modelu OpenAI do analizy obrazów</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enabled" 
                                   value="1" <?= $settings['enabled'] ? 'checked' : '' ?> id="ai-enabled">
                            <label class="form-check-label" for="ai-enabled">
                                Włącz integrację AI
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Zapisz ustawienia
                        </button>
                        <button type="button" class="btn btn-info" id="test-ai-btn">
                            <i class="fas fa-vial me-2"></i>Test AI
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-magic me-2"></i>Funkcje AI
                    </h5>
                </div>
                <div class="card-body">
                    <p>Integracja AI umożliwia automatyczną analizę zdjęć i może dostarczyć następujące funkcje:</p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6 col-md-3">
                            <i class="fas fa-tags fa-2x text-primary mb-2"></i>
                            <div class="small">Automatyczne<br>tagowanie</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                            <div class="small">Opisy<br>zawartości</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <i class="fas fa-shield-check fa-2x text-success mb-2"></i>
                            <div class="small">Filtrowanie<br>zawartości</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <i class="fas fa-search fa-2x text-warning mb-2"></i>
                            <div class="small">Rozpoznawanie<br>obiektów</div>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold">Porównanie dostawców:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Funkcja</th>
                                    <th class="text-center">AWS</th>
                                    <th class="text-center">Google</th>
                                    <th class="text-center">OpenAI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Automatyczne tagowanie</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Opisy zawartości</td>
                                    <td class="text-center"><i class="fas fa-times text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Filtrowanie zawartości</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Rozpoznawanie twarzy</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-muted"></i></td>
                                </tr>
                                <tr>
                                    <td>Rozpoznawanie tekstu</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Konfiguracja dostawców
                    </h5>
                </div>
                <div class="card-body">
                    <div class="provider-info aws-info" style="display: none;">
                        <h6 class="text-primary">AWS Rekognition</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-key me-2"></i>Wymagany klucz dostępu AWS IAM</li>
                            <li><i class="fas fa-globe me-2"></i>Określ region (np. us-east-1)</li>
                            <li><i class="fas fa-dollar-sign me-2"></i>Płatność za analizę</li>
                        </ul>
                    </div>
                    
                    <div class="provider-info google-info" style="display: none;">
                        <h6 class="text-success">Google Vision</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-file me-2"></i>Wymagany plik klucza Service Account</li>
                            <li><i class="fas fa-cloud me-2"></i>Google Cloud Project z włączonym API</li>
                            <li><i class="fas fa-chart-line me-2"></i>Darmowe limity miesięczne</li>
                        </ul>
                    </div>
                    
                    <div class="provider-info openai-info" style="display: none;">
                        <h6 class="text-info">OpenAI GPT-4 Vision</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-brain me-2"></i>Najnowocześniejsza analiza obrazów</li>
                            <li><i class="fas fa-language me-2"></i>Naturalne opisy w języku polskim</li>
                            <li><i class="fas fa-coins me-2"></i>Płatność za token/żądanie</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne</h6>
                        <ul class="mb-0">
                            <li>AI analizuje tylko zatwierdzone zdjęcia</li>
                            <li>Wyniki analizy wymagają ręcznego sprawdzenia</li>
                            <li>Koszty analizy zależą od dostawcy</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('ai-provider');
    const testBtn = document.getElementById('test-ai-btn');
    
    // Toggle provider-specific fields
    function toggleProviderFields() {
        const provider = providerSelect.value;
        
        // Hide all provider fields
        document.querySelectorAll('.provider-field, .provider-info').forEach(el => {
            el.style.display = 'none';
        });
        
        if (provider) {
            // Show relevant fields
            document.querySelectorAll('.' + provider + '-field, .' + provider + '-info').forEach(el => {
                el.style.display = 'block';
            });
        }
    }
    
    // Initialize fields visibility
    toggleProviderFields();
    
    // Handle provider change
    providerSelect.addEventListener('change', toggleProviderFields);
    
    // Test AI service
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            const button = this;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testowanie...';
            
            fetch('<?= Url::to(['test']) ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Test AI zakończony sukcesem!', 'success');
                } else {
                    showToast('Test AI nieudany: ' + (data.message || 'Nieznany błąd'), 'error');
                }
            })
            .catch(error => {
                showToast('Błąd podczas testowania AI', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });
    }
});
</script>