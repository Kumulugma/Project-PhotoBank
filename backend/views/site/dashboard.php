<?php

use yii\helpers\Html;
use yii\helpers\Url;
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $totalPhotos int */
/* @var $queuedPhotos int */
/* @var $totalCategories int */
/* @var $totalTags int */
/* @var $thumbnailsSize int */
/* @var $thumbnailsSizeFormatted string */
/* @var $importFilesCount int */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
/* Ultra kompaktowy dashboard */
.stats-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.stats-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.stats-card .card-body {
    padding: 0.875rem;
}

.stats-card .stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.stats-card .stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0.25rem 0 0.125rem 0;
    line-height: 1.2;
}

.stats-card .stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.2;
}

.stats-card .stat-action {
    margin-top: 0.5rem;
}

.stats-card .stat-action .btn {
    font-size: 0.75rem;
    padding: 0.15rem 0.5rem;
}

/* AWS Cards - ultra kompaktowe */
.aws-card {
    border-radius: 8px;
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.aws-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.aws-card .card-body {
    padding: 0.875rem;
}

.aws-card .aws-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.aws-card .aws-value {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0.25rem 0 0.125rem 0;
    line-height: 1.2;
}

.aws-card .aws-label {
    font-size: 0.8rem;
    margin: 0 0 0.125rem 0;
    line-height: 1.2;
}

.aws-card .aws-sublabel {
    font-size: 0.7rem;
    color: #6c757d;
    line-height: 1.2;
}

.aws-card .aws-meta {
    font-size: 0.7rem;
    margin-top: 0.25rem;
    line-height: 1.2;
}

/* Gradient backgrounds dla ikon */
.icon-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; }
.icon-warning { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; }
.icon-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; }
.icon-info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; }
.icon-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
.icon-danger { background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%); color: white; }

/* Akcje hover */
.action-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.list-group-item {
    border: none;
    padding: 1rem;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateX(4px);
}

.system-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

/* Responsive improvements */
@media (max-width: 576px) {
    .stats-card .stat-number { font-size: 1.25rem; }
    .aws-card .aws-value { font-size: 1.1rem; }
    .stats-card .card-body { padding: 0.75rem; }
    .aws-card .card-body { padding: 0.75rem; }
    .system-icon { width: 24px; height: 24px; font-size: 0.8rem; }
}
</style>

<div class="row g-2 mb-3">
    <!-- Statistics Cards - Ultra kompaktowe -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-primary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-primary me-2">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $totalPhotos ?></div>
                        <p class="stat-label">Aktywne zdjęcia</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['photos/index']) ?>" class="btn btn-sm btn-outline-primary">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-warning border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-warning me-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $queuedPhotos ?></div>
                        <p class="stat-label">W kolejce</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['photos/queue']) ?>" class="btn btn-sm btn-outline-warning">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-success border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-success me-2">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $totalCategories ?></div>
                        <p class="stat-label">Kategorie</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['categories/index']) ?>" class="btn btn-sm btn-outline-success">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-info border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-info me-2">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $totalTags ?></div>
                        <p class="stat-label">Tagi</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['tags/index']) ?>" class="btn btn-sm btn-outline-info">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thumbnails Size -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-secondary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-secondary me-2">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark" style="font-size: 1.25rem;"><?= $thumbnailsSizeFormatted ?></div>
                        <p class="stat-label">Miniaturki</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['thumbnails/index']) ?>" class="btn btn-sm btn-outline-secondary">
                                Zarządzaj <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Files Count -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-danger border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-danger me-2">
                        <i class="fas fa-file-import"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $importFilesCount ?></div>
                        <p class="stat-label">Pliki import</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['photos/import']) ?>" class="btn btn-sm btn-outline-danger">
                                Importuj <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($awsCosts && !isset($awsCosts['error'])): ?>
<!-- AWS Costs Section - Kompaktowa -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="text-primary mb-3">
            <i class="fab fa-aws me-2"></i>Koszty AWS - <?= date('F Y') ?>
        </h5>
    </div>
</div>

<div class="row g-2 mb-3">
    <!-- Current Month Costs -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card aws-card border-start border-primary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="aws-icon icon-primary me-2">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="aws-value text-dark">$<?= number_format($awsCosts['current']['total'], 2) ?></div>
                        <p class="aws-label fw-semibold">Aktualny koszt</p>
                        <small class="aws-sublabel">
                            <?= date('1') ?>-<?= date('j') ?> <?= date('M') ?>
                        </small>
                        <?php if (isset($awsCosts['lastMonth']) && !isset($awsCosts['lastMonth']['error'])): ?>
                            <div class="aws-meta">
                                <?php 
                                $currentTotal = $awsCosts['current']['total'];
                                $lastMonthTotal = $awsCosts['lastMonth']['total'];
                                $percentChange = $lastMonthTotal > 0 ? (($currentTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
                                $isIncrease = $percentChange > 0;
                                ?>
                                <span class="<?= $isIncrease ? 'text-danger' : 'text-success' ?>">
                                    <i class="fas fa-arrow-<?= $isIncrease ? 'up' : 'down' ?> me-1"></i>
                                    <?= abs(round($percentChange, 1)) ?>% vs ubiegły
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecasted Costs -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card aws-card border-start border-warning border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="aws-icon icon-warning me-2">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="aws-value text-dark">$<?= number_format($awsCosts['forecast']['total'], 2) ?></div>
                        <p class="aws-label fw-semibold">Prognoza</p>
                        <small class="aws-sublabel">
                            koniec <?= date('M') ?>
                        </small>
                        <div class="aws-meta">
                            <?php 
                            $confidence = $awsCosts['forecast']['confidence'] ?? 'MEDIUM';
                            $confidenceClass = [
                                'HIGH' => 'text-success',
                                'MEDIUM' => 'text-warning', 
                                'LOW' => 'text-danger'
                            ][$confidence] ?? 'text-muted';
                            ?>
                            <span class="<?= $confidenceClass ?>">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                <?= $confidence ?> pewność
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- S3 Costs -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card aws-card border-start border-info border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="aws-icon icon-info me-2">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="aws-value text-dark">$<?= number_format($awsCosts['s3']['total'], 2) ?></div>
                        <p class="aws-label fw-semibold">Amazon S3</p>
                        <small class="aws-sublabel">Storage & Transfer</small>
                        <div class="aws-meta">
                            <?php 
                            $s3Percentage = $awsCosts['current']['total'] > 0 ? 
                                ($awsCosts['s3']['total'] / $awsCosts['current']['total']) * 100 : 0;
                            ?>
                            <span class="text-info">
                                <i class="fas fa-percentage me-1"></i>
                                <?= round($s3Percentage, 1) ?>% całkowitych
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last Month - tylko jeśli dostępne -->
    <?php if (isset($awsCosts['lastMonth']) && !isset($awsCosts['lastMonth']['error']) && $awsCosts['lastMonth']['total'] > 0): ?>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card aws-card border-start border-secondary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="aws-icon icon-secondary me-2">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="aws-value text-dark">$<?= number_format($awsCosts['lastMonth']['total'], 2) ?></div>
                        <p class="aws-label fw-semibold">Ubiegły miesiąc</p>
                        <small class="aws-sublabel">
                            <?= date('M Y', strtotime('-1 month')) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($awsCosts && isset($awsCosts['error'])): ?>
<!-- AWS Error State -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fab fa-aws me-3 fa-2x"></i>
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
            <i class="fab fa-aws me-3 fa-2x"></i>
            <div>
                <strong>Integracja AWS Cost Explorer</strong><br>
                Skonfiguruj AWS Cost Explorer aby wyświetlać koszty w dashboardzie.
                <br><a href="<?= Url::to(['settings/index']) ?>" class="alert-link">Przejdź do ustawień</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="card action-card h-100">
            <div class="card-header border-0 bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2 text-primary"></i>Szybkie akcje
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="list-group list-group-flush">
                    <a href="<?= Url::to(['photos/upload']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <div class="system-icon icon-primary me-2">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Prześlij zdjęcia</div>
                            <small class="text-muted">Dodaj nowe zdjęcia do systemu</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['photos/queue']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <div class="system-icon icon-warning me-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Poczekalnia zdjęć</div>
                            <small class="text-muted">Zatwierdź oczekujące zdjęcia</small>
                        </div>
                        <?php if ($queuedPhotos > 0): ?>
                            <span class="badge bg-warning me-2"><?= $queuedPhotos ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['s3/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <div class="system-icon icon-info me-2">
                            <i class="fas fa-cloud-upload"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Synchronizacja S3</div>
                            <small class="text-muted">Prześlij zdjęcia do chmury</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['thumbnails/index']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <div class="system-icon icon-success me-2">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Zarządzaj miniaturami</div>
                            <small class="text-muted">Konfiguruj rozmiary miniatur</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="<?= Url::to(['categories/create']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <div class="system-icon icon-secondary me-2">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Dodaj kategorię</div>
                            <small class="text-muted">Utwórz nową kategorię zdjęć</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="col-lg-6">
        <div class="card action-card h-100">
            <div class="card-header border-0 mb-1 bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2 text-primary"></i>Status systemu
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-primary me-2">
                                <i class="fab fa-php"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">PHP</div>
                                <small class="text-muted"><?= PHP_VERSION ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-success me-2">
                                <i class="fas fa-code"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Yii Framework</div>
                                <small class="text-muted"><?= Yii::getVersion() ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-warning me-2">
                                <i class="fas fa-memory"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Limit pamięci</div>
                                <small class="text-muted"><?= ini_get('memory_limit') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-info me-2">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Max upload</div>
                                <small class="text-muted"><?= ini_get('upload_max_filesize') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1 mt-1 text-success"></i>
                        System działa poprawnie
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($queuedPhotos > 0 || $totalPhotos === 0): ?>
<div class="row mt-4">
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