<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use common\models\Settings;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

$this->title = 'Ustawienia AWS S3';
$this->params['breadcrumbs'][] = $this->title;

// Pobierz statystyki S3
$s3CurrentCount = (int) Settings::getSetting('s3.current_count', 0);
$s3MonthlyLimit = (int) Settings::getSetting('s3.monthly_limit', 10000);
$s3UsagePercent = $s3MonthlyLimit > 0 ? round(($s3CurrentCount / $s3MonthlyLimit) * 100, 1) : 0;
?>
<div class="s3-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <!-- Statystyki wykorzystania S3 -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0" style="color: rgb(73, 80, 87);">
                        <i class="fas fa-cloud me-2"></i>Wykorzystanie S3 w tym miesiącu
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar <?= $s3UsagePercent >= 90 ? 'bg-danger' : ($s3UsagePercent >= 70 ? 'bg-warning' : 'bg-info') ?>" 
                                     role="progressbar" style="width: <?= min($s3UsagePercent, 100) ?>%">
                                    <?= $s3UsagePercent ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= number_format($s3CurrentCount) ?> z <?= number_format($s3MonthlyLimit) ?> operacji
                            </small>
                        </div>
                        <div class="col-4 text-end">
                            <span class="h4 <?= $s3UsagePercent >= 90 ? 'text-danger' : 'text-info' ?>">
                                <?= number_format($s3MonthlyLimit - $s3CurrentCount) ?>
                            </span>
                            <br><small class="text-muted">pozostało</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($s3UsagePercent >= 90): ?>
        <div class="alert alert-danger mb-4">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Ostrzeżenie o limicie S3</h6>
            <p class="mb-0">Wykorzystano <?= $s3UsagePercent ?>% miesięcznego limitu operacji S3. Zwiększ limit w ustawieniach poniżej lub zresetuj licznik.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-aws me-2"></i>Konfiguracja S3
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 's3-settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label">Nazwa bucket</label>
                        <input type="text" class="form-control" name="bucket" 
                               value="<?= Html::encode($settings['bucket']) ?>" required>
                        <div class="form-text">Nazwa bucket S3 do przechowywania zdjęć</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <input type="text" class="form-control" name="region" 
                               value="<?= Html::encode($settings['region']) ?>" required
                               placeholder="np. us-east-1, eu-west-1">
                        <div class="form-text">Region AWS gdzie znajduje się bucket</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Klucz dostępu (Access Key)</label>
                        <input type="text" class="form-control" name="access_key" 
                               value="<?= Html::encode($settings['access_key']) ?>" 
                               placeholder="<?= empty($settings['access_key']) ? 'Wprowadź klucz dostępu' : 'Zachowaj obecny klucz' ?>">
                        <div class="form-text">Klucz dostępu AWS IAM</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Klucz sekretny (Secret Key)</label>
                        <input type="password" class="form-control" name="secret_key" 
                               value="<?= !empty($settings['secret_key']) ? '********' : '' ?>" 
                               placeholder="<?= empty($settings['secret_key']) ? 'Wprowadź klucz sekretny' : 'Zachowaj obecny klucz' ?>">
                        <div class="form-text">Klucz sekretny AWS IAM</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Katalog zdjęć</label>
                        <input type="text" class="form-control" name="directory" 
                               value="<?= Html::encode($settings['directory']) ?>" 
                               placeholder="photos">
                        <div class="form-text">Ścieżka w bucket dla zdjęć</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Katalog usuniętych zdjęć</label>
                        <input type="text" class="form-control" name="deleted_directory" 
                               value="<?= Html::encode($settings['deleted_directory']) ?>" 
                               placeholder="deleted">
                        <div class="form-text">Ścieżka w bucket dla usuniętych zdjęć</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Miesięczny limit operacji S3</label>
                        <input type="number" class="form-control" name="monthly_limit" 
                               value="<?= $s3MonthlyLimit ?>" min="0" max="1000000">
                        <div class="form-text">Maksymalna liczba operacji S3 w miesiącu</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Zapisz ustawienia
                        </button>
                        <button type="button" class="btn btn-info" id="test-connection-btn">
                            <i class="fas fa-plug me-2"></i>Test połączenia
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Zarządzanie licznikiem S3 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Zarządzanie licznikiem S3
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
                                <label class="form-label">Resetuj licznik S3</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reset_s3_counter" 
                                           value="1" id="reset-s3">
                                    <label class="form-check-label" for="reset-s3">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sync me-2"></i>Synchronizacja S3
                    </h5>
                </div>
                <div class="card-body">
                    <p>Użyj tej funkcji do synchronizacji zdjęć z magazynem S3. Proces obejmuje:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-upload text-primary me-2"></i>Przesłanie zatwierdzonych zdjęć do S3</li>
                        <li><i class="fas fa-database text-info me-2"></i>Aktualizacja ścieżek S3 w bazie danych</li>
                        <li><i class="fas fa-trash text-warning me-2"></i>Opcjonalne usunięcie lokalnych kopii</li>
                    </ul>

                    <?php $syncForm = ActiveForm::begin([
                        'id' => 's3-sync-form',
                        'action' => ['sync'],
                    ]); ?>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="delete_local" 
                                   value="1" id="delete-local-check">
                            <label class="form-check-label" for="delete-local-check">
                                Usuń lokalne kopie po przesłaniu
                            </label>
                        </div>
                        <div class="form-text">Uwaga: Ta opcja jest nieodwracalna!</div>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Przed synchronizacją</h6>
                        <ul class="mb-0">
                            <li>Upewnij się, że ustawienia S3 są poprawne</li>
                            <li>Przetestuj połączenie z S3</li>
                            <li>Zrób kopię zapasową bazy danych</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Rozpocznij synchronizację
                    </button>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Statystyki S3
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Reset licznika S3:</strong></td>
                                <td class="text-end"><?= Settings::getSetting('s3.reset_date', date('Y-m-01')) ?></td>
                            </tr>
                            <tr>
                                <td><strong>S3 dostępne:</strong></td>
                                <td class="text-end">
                                    <?php if ($s3UsagePercent < 100): ?>
                                        <span class="badge bg-success">Tak</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Limit wyczerpany</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Operacje w tym miesiącu:</strong></td>
                                <td class="text-end"><span class="badge bg-info"><?= number_format($s3CurrentCount) ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Pozostały limit:</strong></td>
                                <td class="text-end"><span class="badge bg-secondary"><?= number_format($s3MonthlyLimit - $s3CurrentCount) ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>O Amazon S3
                    </h5>
                </div>
                <div class="card-body">
                    <p>Amazon S3 (Simple Storage Service) to skalowalna usługa przechowywania obiektów w chmurze.</p>
                    
                    <h6 class="fw-bold">Korzyści z S3:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-shield-alt text-success me-2"></i>Trwałość i niezawodność</li>
                        <li><i class="fas fa-expand text-primary me-2"></i>Skalowalność</li>
                        <li><i class="fas fa-dollar-sign text-warning me-2"></i>Opłacalność dla dużych kolekcji</li>
                        <li><i class="fas fa-server text-info me-2"></i>Zmniejszenie obciążenia serwera</li>
                        <li><i class="fas fa-history text-secondary me-2"></i>Kopie zapasowe i wersjonowanie</li>
                    </ul>
                    
                    <div class="alert alert-info mb-0">
                        <strong>Wymagania:</strong> Aby korzystać z integracji S3, potrzebujesz konta AWS oraz bucket S3 z odpowiednimi uprawnieniami.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test connection button
    const testBtn = document.getElementById('test-connection-btn');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
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
                
                const container = document.querySelector('.s3-index');
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
                button.innerHTML = '<i class="fas fa-plug me-2"></i>Test połączenia';
            });
        });
    }
});
</script>