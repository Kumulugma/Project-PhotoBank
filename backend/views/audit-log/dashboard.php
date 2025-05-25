<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\search\AuditLogSearch;

\backend\assets\AppAsset::registerControllerCss($this, 'audit-log');
\backend\assets\AppAsset::registerComponentCss($this, 'modals');

$this->title = 'Dashboard Dziennika Zdarzeń';
$this->params['breadcrumbs'][] = ['label' => 'Dziennik Zdarzeń', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Dashboard';
?>

<div class="audit-log-dashboard">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Pełny dziennik', ['index'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Odśwież', ['dashboard'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <!-- Statystyki główne -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?= number_format($stats['total']) ?></h3>
                            <p class="mb-0">Wszystkich zdarzeń</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?= number_format($stats['today']) ?></h3>
                            <p class="mb-0">Dzisiaj</p>
                            <small>Wczoraj: <?= number_format($stats['yesterday']) ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?= number_format($errorStats['errors_today']) ?></h3>
                            <p class="mb-0">Błędów dzisiaj</p>
                            <small>Ostrzeżeń: <?= number_format($errorStats['warnings_today']) ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?= number_format($stats['week']) ?></h3>
                            <p class="mb-0">Ten tydzień</p>
                            <small>Miesiąc: <?= number_format($stats['month']) ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wykres aktywności -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktywność w ostatnich 7 dniach</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Najczęstsze akcje</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topActions)): ?>
                        <?php foreach (array_slice($topActions, 0, 8) as $action): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-truncate">
                                <?= AuditLogSearch::getActionOptions()[$action['action']] ?? $action['action'] ?>
                            </span>
                            <span class="badge bg-primary"><?= number_format($action['count']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Brak danych</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Najaktywniejsze użytkownicy i ostatnie błędy -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Najaktywniejsze użytkownicy (30 dni)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topUsers)): ?>
                        <?php foreach (array_slice($topUsers, 0, 10) as $userStat): ?>
                        <?php 
                            $user = \common\models\User::findOne($userStat['user_id']);
                            $username = $user ? $user->username : 'Nieznany użytkownik';
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-truncate"><?= Html::encode($username) ?></span>
                            <span class="badge bg-secondary"><?= number_format($userStat['count']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Brak danych</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ostatnie błędy i ostrzeżenia</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentErrors)): ?>
                        <?php foreach ($recentErrors as $error): ?>
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge bg-<?= $error->getSeverityClass() ?> me-2 mt-1">
                                <?= $error->getSeverityLabel() ?>
                            </span>
                            <div class="flex-grow-1">
                                <div class="small text-muted">
                                    <?= date('d.m.Y H:i', $error->created_at) ?>
                                    <?php if ($error->user): ?>
                                        • <?= Html::encode($error->user->username) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="text-truncate">
                                    <?= Html::encode($error->message ?: 'Brak wiadomości') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <?= Html::a('Zobacz wszystkie błędy', ['index', 'AuditLogSearch[severity]' => 'error'], 
                                ['class' => 'btn btn-sm btn-outline-danger']) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-success">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">Brak błędów!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Szybkie akcje -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Szybkie akcje</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?= Html::a('<i class="fas fa-list me-2"></i>Wszystkie zdarzenia', ['index'], 
                                ['class' => 'btn btn-outline-primary btn-block w-100 mb-2']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a('<i class="fas fa-exclamation-triangle me-2"></i>Tylko błędy', 
                                ['index', 'AuditLogSearch[severity]' => 'error'], 
                                ['class' => 'btn btn-outline-danger btn-block w-100 mb-2']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a('<i class="fas fa-sign-in-alt me-2"></i>Logowania', 
                                ['index', 'AuditLogSearch[action]' => 'login'], 
                                ['class' => 'btn btn-outline-success btn-block w-100 mb-2']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a('<i class="fas fa-download me-2"></i>Eksport CSV', '#', 
                                ['class' => 'btn btn-outline-info btn-block w-100 mb-2', 
                                 'data-bs-toggle' => 'modal', 'data-bs-target' => '#quickExportModal']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń błędy', '#', 
                                ['class' => 'btn btn-outline-danger btn-block w-100 mb-2', 
                                 'data-bs-toggle' => 'modal', 'data-bs-target' => '#deleteErrorsModal']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal szybkiego eksportu -->
<div class="modal fade" id="quickExportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['export'], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Szybki eksport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Eksportuj:</label>
                    <select name="quick_export" class="form-control">
                        <option value="today">Zdarzenia z dzisiaj</option>
                        <option value="week">Zdarzenia z tego tygodnia</option>
                        <option value="month">Zdarzenia z tego miesiąca</option>
                        <option value="errors">Wszystkie błędy</option>
                        <option value="logins">Wszystkie logowania</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format:</label>
                    <select name="format" class="form-control">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-info">Eksportuj</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- Modal usuwania błędów -->
<div class="modal fade" id="deleteErrorsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['delete-errors'], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Usuwanie błędów</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Usuń wpisy o błędach i ostrzeżeniach:</p>
                <div class="mb-3">
                    <label class="form-label">Usuń wpisy:</label>
                    <select name="error_type" class="form-control">
                        <option value="all_errors">Wszystkie błędy i ostrzeżenia</option>
                        <option value="errors_only">Tylko błędy</option>
                        <option value="warnings_only">Tylko ostrzeżenia</option>
                        <option value="today_errors">Błędy z dzisiaj</option>
                        <option value="week_errors">Błędy z tego tygodnia</option>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <strong>Uwaga:</strong> Ta operacja jest nieodwracalna!
                </div>
                <div class="alert alert-info">
                    <strong>Aktualne błędy dzisiaj:</strong> <?= number_format($errorStats['errors_today']) ?><br>
                    <strong>Aktualne ostrzeżenia dzisiaj:</strong> <?= number_format($errorStats['warnings_today']) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-danger">Usuń błędy</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyActivity, 'formatted_date')) ?>,
        datasets: [{
            label: 'Liczba zdarzeń',
            data: <?= json_encode(array_column($dailyActivity, 'count')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
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
            }
        }
    }
});
</script>