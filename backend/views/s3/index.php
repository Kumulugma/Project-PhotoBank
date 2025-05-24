<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'Ustawienia AWS S3';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="s3-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

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