<?php

use yii\helpers\Html;
use yii\helpers\Url;
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');

/* @var $this yii\web\View */
/* @var $totalPhotos int */
/* @var $queuedPhotos int */
/* @var $publicPhotos int */
/* @var $privatePhotos int */
/* @var $aiPhotos int */
/* @var $aiPercentage float */
/* @var $totalCategories int */
/* @var $totalTags int */
/* @var $thumbnailsSize int */
/* @var $thumbnailsSizeFormatted string */
/* @var $importFilesCount int */
/* @var $dailyUploads array */
/* @var $auditStats array */
/* @var $awsCosts array|null */

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
                        <div class="stat-number text-dark"><?= number_format($totalPhotos) ?></div>
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
                        <div class="stat-number text-dark"><?= number_format($queuedPhotos) ?></div>
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
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= number_format($publicPhotos) ?></div>
                        <p class="stat-label">Publiczne</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['photos/index', 'PhotoSearch[is_public]' => 1]) ?>" class="btn btn-sm btn-outline-success">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card border-start border-secondary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon icon-secondary me-2">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= number_format($privatePhotos) ?></div>
                        <p class="stat-label">Prywatne</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['photos/index', 'PhotoSearch[is_public]' => 0]) ?>" class="btn btn-sm btn-outline-secondary">
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
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= number_format($aiPhotos) ?></div>
                        <p class="stat-label">Zdjęcia AI</p>
                        <?php if ($aiPercentage > 0): ?>
                            <div class="stat-action">
                                <small class="text-muted"><?= number_format($aiPercentage, 1) ?>% wszystkich</small>
                            </div>
                        <?php endif; ?>
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
                        <i class="fas fa-hdd"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number text-dark"><?= $thumbnailsSizeFormatted ?></div>
                        <p class="stat-label">Miniaturki</p>
                        <div class="stat-action">
                            <a href="<?= Url::to(['thumbnails/index']) ?>" class="btn btn-sm btn-outline-info">
                                Zobacz <i class="fas fa-arrow-right ms-1"></i>
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
    <div class="col-xl-4 col-lg-4 col-md-6">
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
    <div class="col-xl-4 col-lg-4 col-md-6">
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

    <!-- Last Month - tylko jeśli dostępne -->
    <?php if (isset($awsCosts['lastMonth']) && !isset($awsCosts['lastMonth']['error']) && $awsCosts['lastMonth']['total'] > 0): ?>
    <div class="col-xl-4 col-lg-4 col-md-6">
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
                <br><small class="text-muted">Przejdź do ustawień aby skonfigurować integrację</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Wykres dziennych wgrań -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Zdjęcia wgrane w ostatnim tygodniu
                </h5>
            </div>
            <div class="card-body">
                <canvas id="dailyUploadsChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>Status systemu
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-success me-2">
                                <i class="fas fa-database"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Baza danych</h6>
                                <small class="text-muted">MySQL aktywna</small>
                            </div>
                        </div>
                        <span class="badge bg-success">Online</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-primary me-2">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Kategorie</h6>
                                <small class="text-muted"><?= number_format($totalCategories) ?> kategorii</small>
                            </div>
                        </div>
                        <a href="<?= Url::to(['categories/index']) ?>" class="btn btn-sm btn-outline-primary">Zobacz</a>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-info me-2">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Tagi</h6>
                                <small class="text-muted"><?= number_format($totalTags) ?> tagów</small>
                            </div>
                        </div>
                        <a href="<?= Url::to(['tags/index']) ?>" class="btn btn-sm btn-outline-info">Zobacz</a>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="system-icon icon-danger me-2">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Pliki import</h6>
                                <small class="text-muted"><?= number_format($importFilesCount) ?> plików</small>
                            </div>
                        </div>
                        <a href="<?= Url::to(['photos/import']) ?>" class="btn btn-sm btn-outline-danger">Importuj</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Szybkie akcje -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card action-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Szybkie akcje
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3 col-6">
                        <a href="<?= Url::to(['photos/upload']) ?>" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-2"></i>Prześlij zdjęcia
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= Url::to(['photos/import']) ?>" class="btn btn-success w-100">
                            <i class="fas fa-file-import me-2"></i>Import z FTP
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= Url::to(['queue/index']) ?>" class="btn btn-info w-100">
                            <i class="fas fa-tasks me-2"></i>Kolejka zadań
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= Url::to(['settings/index']) ?>" class="btn btn-secondary w-100">
                            <i class="fas fa-cogs me-2"></i>Ustawienia
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dziennik zdarzeń -->
<?php if (!empty($auditStats)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Dziennik zdarzeń - ostatnie 7 dni
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-primary"><?= number_format($auditStats['total_events']) ?></h3>
                            <small class="text-muted">Wszystkie zdarzenia</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-success"><?= number_format($auditStats['today_events']) ?></h3>
                            <small class="text-muted">Dzisiaj</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-warning"><?= number_format($auditStats['warning_events']) ?></h3>
                            <small class="text-muted">Ostrzeżenia</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-danger"><?= number_format($auditStats['error_events']) ?></h3>
                            <small class="text-muted">Błędy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wykres dziennych wgrań
    const ctx = document.getElementById('dailyUploadsChart').getContext('2d');
    
    const dailyData = <?= json_encode($dailyUploads) ?>;
    const labels = dailyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('pl-PL', { weekday: 'short', day: 'numeric', month: 'short' });
    });
    const data = dailyData.map(item => item.count);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Liczba zdjęć',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: 'rgb(75, 192, 192)',
                pointHoverBackgroundColor: 'rgb(54, 162, 235)',
                pointHoverBorderColor: 'rgb(54, 162, 235)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'Dzień: ' + context[0].label;
                        },
                        label: function(context) {
                            return 'Wgrano: ' + context.parsed.y + ' zdjęć';
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>