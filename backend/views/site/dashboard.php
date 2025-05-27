<?php

use yii\helpers\Html;
use yii\helpers\Url;
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $totalPhotos int */
/* @var $queuedPhotos int */
/* @var $totalCategories int */
/* @var $totalTags int */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-primary">
                            <i class="fas fa-images fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0"><?= $totalPhotos ?></h3>
                            <p class="text-muted mb-0">Aktywne zdjęcia</p>
                        </div>
                    </div>
                    <div>
                        <a href="<?= Url::to(['photos/index']) ?>" class="btn btn-sm btn-outline-primary">
                            Zobacz <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0"><?= $queuedPhotos ?></h3>
                            <p class="text-muted mb-0">W kolejce</p>
                        </div>
                    </div>
                    <div>
                        <a href="<?= Url::to(['photos/queue']) ?>" class="btn btn-sm btn-outline-warning">
                            Zobacz <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-success">
                            <i class="fas fa-folder fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0"><?= $totalCategories ?></h3>
                            <p class="text-muted mb-0">Kategorie</p>
                        </div>
                    </div>
                    <div>
                        <a href="<?= Url::to(['categories/index']) ?>" class="btn btn-sm btn-outline-success">
                            Zobacz <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-info">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0"><?= $totalTags ?></h3>
                            <p class="text-muted mb-0">Tagi</p>
                        </div>
                    </div>
                    <div>
                        <a href="<?= Url::to(['tags/index']) ?>" class="btn btn-sm btn-outline-info">
                            Zobacz <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($awsCosts && !isset($awsCosts['error'])): ?>
<!-- AWS Costs Section -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="text-primary mb-3">
            <i class="fab fa-aws me-2"></i>Koszty AWS - <?= date('F Y') ?>
        </h4>
    </div>
</div>

<div class="row mb-4">
    <!-- Current Month Costs -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-primary">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0">$<?= number_format($awsCosts['current']['total'], 2) ?></h3>
                            <p class="text-muted mb-0">Aktualny koszt</p>
                            <small class="text-muted">
                                <?= date('1') ?>-<?= date('j') ?> <?= date('M') ?>
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if (isset($awsCosts['lastMonth']) && !isset($awsCosts['lastMonth']['error'])): ?>
                            <?php 
                            $currentTotal = $awsCosts['current']['total'];
                            $lastMonthTotal = $awsCosts['lastMonth']['total'];
                            $percentChange = $lastMonthTotal > 0 ? (($currentTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
                            $isIncrease = $percentChange > 0;
                            ?>
                            <small class="<?= $isIncrease ? 'text-danger' : 'text-success' ?>">
                                <i class="fas fa-arrow-<?= $isIncrease ? 'up' : 'down' ?> me-1"></i>
                                <?= abs(round($percentChange, 1)) ?>%
                            </small>
                            <br>
                            <small class="text-muted">vs ubiegły miesiąc</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecasted Costs -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-warning">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0">$<?= number_format($awsCosts['forecast']['total'], 2) ?></h3>
                            <p class="text-muted mb-0">Prognoza na koniec</p>
                            <small class="text-muted">
                                <?= date('t') ?> <?= date('M') ?>
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php 
                        $confidence = $awsCosts['forecast']['confidence'] ?? 'MEDIUM';
                        $confidenceClass = [
                            'HIGH' => 'text-success',
                            'MEDIUM' => 'text-warning', 
                            'LOW' => 'text-danger'
                        ][$confidence] ?? 'text-muted';
                        ?>
                        <span class="badge bg-light <?= $confidenceClass ?>">
                            <?= $confidence ?>
                        </span>
                        <br>
                        <small class="text-muted">pewność</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- S3 Costs -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-info">
                            <i class="fas fa-cloud fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <h3 class="mb-0">$<?= number_format($awsCosts['s3']['total'], 2) ?></h3>
                            <p class="text-muted mb-0">Amazon S3</p>
                            <small class="text-muted">
                                Storage & Transfer
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php 
                        $s3Percentage = $awsCosts['current']['total'] > 0 ? 
                            ($awsCosts['s3']['total'] / $awsCosts['current']['total']) * 100 : 0;
                        ?>
                        <span class="badge bg-info">
                            <?= round($s3Percentage, 1) ?>%
                        </span>
                        <br>
                        <small class="text-muted">całkowitych kosztów</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last Month -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-start border-secondary border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary">
                            <i class="fas fa-history fa-2x"></i>
                        </div>
                        <div class="mt-3">
                            <?php if (isset($awsCosts['lastMonth']) && !isset($awsCosts['lastMonth']['error'])): ?>
                                <h3 class="mb-0">$<?= number_format($awsCosts['lastMonth']['total'], 2) ?></h3>
                                <p class="text-muted mb-0">Ubiegły miesiąc</p>
                                <small class="text-muted">
                                    <?= date('M Y', strtotime('-1 month')) ?>
                                </small>
                            <?php else: ?>
                                <h3 class="mb-0 text-muted">N/A</h3>
                                <p class="text-muted mb-0">Ubiegły miesiąc</p>
                                <small class="text-muted">Brak danych</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($awsCosts && isset($awsCosts['error'])): ?>
<!-- AWS Error State -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fab fa-aws me-3"></i>
            <div>
                <strong>Błąd pobierania kosztów AWS</strong><br>
                <?= Html::encode($awsCosts['message'] ?? 'Nieznany błąd') ?>
                <br><small class="text-muted">Sprawdź konfigurację AWS Cost Explorer w ustawieniach</small>
            </div>
        </div>
    </div>
</div>

<?php elseif (!$awsCosts): ?>
<!-- AWS Not Configured -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fab fa-aws me-3"></i>
            <div>
                <strong>Integracja AWS Cost Explorer</strong><br>
                Skonfiguruj AWS Cost Explorer aby wyświetlać koszty w dashboardzie.
                <br><a href="<?= Url::to(['settings/index']) ?>" class="alert-link">Przejdź do ustawień</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Szybkie akcje
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= Url::to(['photos/upload']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-upload text-primary me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Prześlij zdjęcia</div>
                            <small class="text-muted">Dodaj nowe zdjęcia do systemu</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['photos/queue']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-clock text-warning me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Poczekalnia zdjęć</div>
                            <small class="text-muted">Zatwierdź oczekujące zdjęcia</small>
                        </div>
                        <?php if ($queuedPhotos > 0): ?>
                            <span class="badge bg-warning"><?= $queuedPhotos ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-right text-muted ms-2"></i>
                    </a>
                    
                    <a href="<?= Url::to(['s3/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-cloud-upload text-info me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Synchronizacja S3</div>
                            <small class="text-muted">Prześlij zdjęcia do chmury</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['thumbnails/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-image text-success me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Zarządzaj miniaturami</div>
                            <small class="text-muted">Konfiguruj rozmiary miniatur</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['categories/create']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-plus text-secondary me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Dodaj kategorię</div>
                            <small class="text-muted">Utwórz nową kategorię zdjęć</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>Status systemu
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fab fa-php fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold">PHP</div>
                                <small class="text-muted"><?= PHP_VERSION ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-code fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold">Yii Framework</div>
                                <small class="text-muted"><?= Yii::getVersion() ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-memory fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold">Limit pamięci</div>
                                <small class="text-muted"><?= ini_get('memory_limit') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-upload fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold">Max upload</div>
                                <small class="text-muted"><?= ini_get('upload_max_filesize') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        System działa poprawnie
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($queuedPhotos > 0 || $totalPhotos === 0): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-3"></i>
            <div>
                <?php if ($queuedPhotos > 0): ?>
                    <strong>Uwaga!</strong> Masz <?= $queuedPhotos ?> zdjęć oczekujących na zatwierdzenie.
                    <a href="<?= Url::to(['photos/queue']) ?>" class="alert-link">Przejdź do poczekalni</a>
                <?php elseif ($totalPhotos === 0): ?>
                    <strong>Witaj!</strong> Wygląda na to, że nie masz jeszcze żadnych zdjęć.
                    <a href="<?= Url::to(['photos/upload']) ?>" class="alert-link">Prześlij swoje pierwsze zdjęcia</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

