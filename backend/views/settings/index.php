<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $settings array */

$this->title = 'Ustawienia systemu';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-cogs me-2"></i>Konfiguracja systemu
            </h5>
        </div>
        <div class="card-body">
            <?php
            $form = ActiveForm::begin([
                        'id' => 'settings-form',
                        'action' => ['update'],
                        'options' => ['class' => 'needs-validation'],
            ]);
            ?>

            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-cog me-2"></i>Ustawienia ogólne
                    </h5>

<?php if (isset($settings['general']) && !empty($settings['general'])): ?>
                            <?php foreach ($settings['general'] as $key => $setting): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
        <?php if (is_bool($setting['value']) || $setting['value'] === '0' || $setting['value'] === '1'): ?>
                                    <select name="Settings[<?= $setting['key'] ?>]" class="form-select">
                                        <option value="1" <?= $setting['value'] == 1 ? 'selected' : '' ?>>Włączone</option>
                                        <option value="0" <?= $setting['value'] == 0 ? 'selected' : '' ?>>Wyłączone</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="Settings[<?= $setting['key'] ?>]" value="<?= Html::encode($setting['value']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($setting['description'])): ?>
                                    <div class="form-text"><?= Html::encode($setting['description']) ?></div>
                            <?php endif; ?>
                            </div>
    <?php endforeach; ?>
<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Brak skonfigurowanych ustawień ogólnych
                        </div>
<?php endif; ?>

                    <h5 class="text-info mb-3 mt-4">
                        <i class="fas fa-envelope me-2"></i>Ustawienia email
                    </h5>

<?php if (isset($settings['email']) && !empty($settings['email'])): ?>
                            <?php foreach ($settings['email'] as $key => $setting): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
        <?php if ($key === 'password' || $key === 'smtp_password'): ?>
                                    <input type="password" class="form-control" name="Settings[<?= $setting['key'] ?>]" 
                                           value="<?= !empty($setting['value']) ? '********' : '' ?>" 
                                           placeholder="<?= empty($setting['value']) ? 'Wprowadź hasło' : 'Zachowaj obecne hasło' ?>">
        <?php elseif (is_bool($setting['value']) || $setting['value'] === '0' || $setting['value'] === '1'): ?>
                                    <select name="Settings[<?= $setting['key'] ?>]" class="form-select">
                                        <option value="1" <?= $setting['value'] == 1 ? 'selected' : '' ?>>Włączone</option>
                                        <option value="0" <?= $setting['value'] == 0 ? 'selected' : '' ?>>Wyłączone</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="Settings[<?= $setting['key'] ?>]" value="<?= Html::encode($setting['value']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($setting['description'])): ?>
                                    <div class="form-text"><?= Html::encode($setting['description']) ?></div>
                            <?php endif; ?>
                            </div>
    <?php endforeach; ?>
<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Brak skonfigurowanych ustawień email
                        </div>
<?php endif; ?>
                </div>

                <div class="col-lg-6">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-upload me-2"></i>Ustawienia przesyłania
                    </h5>

<?php if (isset($settings['upload']) && !empty($settings['upload'])): ?>
                            <?php foreach ($settings['upload'] as $key => $setting): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
        <?php if (is_bool($setting['value']) || $setting['value'] === '0' || $setting['value'] === '1'): ?>
                                    <select name="Settings[<?= $setting['key'] ?>]" class="form-select">
                                        <option value="1" <?= $setting['value'] == 1 ? 'selected' : '' ?>>Włączone</option>
                                        <option value="0" <?= $setting['value'] == 0 ? 'selected' : '' ?>>Wyłączone</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="Settings[<?= $setting['key'] ?>]" value="<?= Html::encode($setting['value']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($setting['description'])): ?>
                                    <div class="form-text"><?= Html::encode($setting['description']) ?></div>
                            <?php endif; ?>
                            </div>
    <?php endforeach; ?>
<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Brak skonfigurowanych ustawień przesyłania
                        </div>
<?php endif; ?>

                    <h5 class="text-warning mb-3 mt-4">
                        <i class="fas fa-images me-2"></i>Ustawienia galerii
                    </h5>

<?php if (isset($settings['gallery']) && !empty($settings['gallery'])): ?>
                            <?php foreach ($settings['gallery'] as $key => $setting): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
        <?php if (is_bool($setting['value']) || $setting['value'] === '0' || $setting['value'] === '1'): ?>
                                    <select name="Settings[<?= $setting['key'] ?>]" class="form-select">
                                        <option value="1" <?= $setting['value'] == 1 ? 'selected' : '' ?>>Włączone</option>
                                        <option value="0" <?= $setting['value'] == 0 ? 'selected' : '' ?>>Wyłączone</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="Settings[<?= $setting['key'] ?>]" value="<?= Html::encode($setting['value']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($setting['description'])): ?>
                                    <div class="form-text"><?= Html::encode($setting['description']) ?></div>
                            <?php endif; ?>
                            </div>
    <?php endforeach; ?>
<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Brak skonfigurowanych ustawień galerii
                        </div>
<?php endif; ?>

                    <h5 class="text-secondary mb-3 mt-4">
                        <i class="fas fa-camera me-2"></i>Ustawienia EXIF
                    </h5>

                    <?php if (isset($settings['gallery']) && !empty($settings['gallery'])): ?>
                        <?php
                        $exifSettings = [];
                        foreach ($settings['gallery'] as $key => $setting) {
                            if (strpos($setting['key'], 'exif_show_') !== false) {
                                $exifSettings[$key] = $setting;
                            }
                        }
                        ?>

                                <?php if (!empty($exifSettings)): ?>
                            <div class="row">
                                <div class="col-md-6">
        <?php foreach (array_slice($exifSettings, 0, ceil(count($exifSettings) / 2), true) as $key => $setting): ?>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="hidden" name="Settings[<?= $setting['key'] ?>]" value="0">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="Settings[<?= $setting['key'] ?>]" 
                                                       value="1" 
                                                       id="<?= $setting['key'] ?>"
                                                    <?= $setting['value'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="<?= $setting['key'] ?>">
            <?= Html::encode($setting['description']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-md-6">
        <?php foreach (array_slice($exifSettings, ceil(count($exifSettings) / 2), null, true) as $key => $setting): ?>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="hidden" name="Settings[<?= $setting['key'] ?>]" value="0">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="Settings[<?= $setting['key'] ?>]" 
                                                       value="1" 
                                                       id="<?= $setting['key'] ?>"
                                                    <?= $setting['value'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="<?= $setting['key'] ?>">
            <?= Html::encode($setting['description']) ?>
                                                </label>
                                            </div>
                                        </div>
                            <?php endforeach; ?>
                                </div>
                            </div>
    <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Brak skonfigurowanych ustawień EXIF
                            </div>
    <?php endif; ?>
<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Uruchom migrację aby dodać ustawienia EXIF
                        </div>
<?php endif; ?>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <div></div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Zapisz ustawienia
                </button>
            </div>

<?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>Informacje o ustawieniach
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p>Na tej stronie możesz zarządzać globalnymi ustawieniami systemu. Ustawienia są pogrupowane według kategorii dla łatwiejszego zarządzania.</p>

                    <h6 class="fw-bold">Kategorie ustawień:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-cog text-primary me-2"></i><strong>Ogólne:</strong> Podstawowe opcje konfiguracji systemu</li>
                        <li><i class="fas fa-envelope text-info me-2"></i><strong>Email:</strong> Konfiguracja SMTP do wysyłania wiadomości</li>
                        <li><i class="fas fa-upload text-success me-2"></i><strong>Przesyłanie:</strong> Ograniczenia i restrakcje przesyłania plików</li>
                        <li><i class="fas fa-images text-warning me-2"></i><strong>Galeria:</strong> Preferencje wyświetlania galerii frontend</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne informacje</h6>
                        <ul class="mb-0">
                            <li>Niektóre ustawienia mogą wymagać restartu systemu aby wejść w życie</li>
                            <li>Ustawienia specjalistyczne (S3, AI, Watermark) są dostępne w odpowiednich sekcjach</li>
                            <li>Dla zaawansowanej konfiguracji można modyfikować pliki konfiguracyjne bezpośrednio</li>
                            <li>Regularne tworzenie kopii zapasowych konfiguracji jest zalecane</li>
                        </ul>
                    </div>

                    <div class="alert alert-info mb-0">
                        <h6><i class="fas fa-link me-2"></i>Powiązane sekcje</h6>
                        <ul class="mb-0">
                            <li><?= Html::a('Ustawienia S3', ['/s3/index'], ['class' => 'alert-link']) ?></li>
                            <li><?= Html::a('Integracja AI', ['/ai/index'], ['class' => 'alert-link']) ?></li>
                            <li><?= Html::a('Ustawienia znaku wodnego', ['/watermark/index'], ['class' => 'alert-link']) ?></li>
                            <li><?= Html::a('Rozmiary miniatur', ['/thumbnails/index'], ['class' => 'alert-link']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>