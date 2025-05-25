<?php

use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\bootstrap5\Breadcrumbs;
use common\widgets\Alert;
use backend\assets\AppAsset;
use common\models\AuditLog;

AppAsset::register($this);

// Register common component assets
AppAsset::registerComponentJs($this, 'modals');
AppAsset::registerComponentJs($this, 'forms');
AppAsset::registerComponentJs($this, 'tables');
AppAsset::registerComponentJs($this, 'alerts');

// Pobierz statystyki dla menu (tylko jeśli użytkownik jest zalogowany)
$todayErrors = 0;
$queuedJobs = 0;

if (!Yii::$app->user->isGuest) {
    try {
        $todayErrors = AuditLog::find()
                ->where(['severity' => [AuditLog::SEVERITY_ERROR, AuditLog::SEVERITY_WARNING]])
                ->andWhere(['>=', 'created_at', strtotime('today')])
                ->count();

        $queuedJobs = \common\models\QueuedJob::find()
                ->where(['status' => \common\models\QueuedJob::STATUS_PENDING])
                ->count();
    } catch (\Exception $e) {
        // Ignoruj błędy pobierania statystyk (np. gdy tabele jeszcze nie istnieją)
    }
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

        <header id="header">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandUrl' => Yii::$app->homeUrl,
                'options' => ['class' => 'navbar-expand-lg navbar-dark bg-dark fixed-top'],
            ]);

            $menuItems = [
                ['label' => 'Dashboard', 'url' => ['/site/index']],
                [
                    'label' => 'Zdjęcia',
                    'items' => [
                        ['label' => '<i class="fas fa-images me-2"></i>Wszystkie zdjęcia', 'url' => ['/photos/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-clock me-2"></i>Poczekalnia', 'url' => ['/photos/queue'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fas fa-upload me-2"></i>Przesyłanie', 'url' => ['/photos/upload'], 'encode' => false],
                        ['label' => '<i class="fas fa-file-import me-2"></i>Import', 'url' => ['/photos/import'], 'encode' => false],
                    ],
                ],
                [
                    'label' => 'Zarządzanie',
                    'items' => [
                        ['label' => '<i class="fas fa-folder me-2"></i>Kategorie', 'url' => ['/categories/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-tags me-2"></i>Tagi', 'url' => ['/tags/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-image me-2"></i>Rozmiary miniatur', 'url' => ['/thumbnail-size/index'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fas fa-users me-2"></i>Użytkownicy', 'url' => ['/users/index'], 'encode' => false],
                    ],
                ],
                [
                    'label' => 'System',
                    'items' => [
                        [
                            'label' => '<i class="fas fa-tasks me-2"></i>Kolejka zadań' . ($queuedJobs > 0 ? ' <span class="badge bg-warning ms-1">' . $queuedJobs . '</span>' : ''),
                            'url' => ['/queue/index'],
                            'encode' => false
                        ],
                        ['label' => '<i class="fas fa-terminal me-2"></i>Komendy consolowe', 'url' => ['/console/index'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fab fa-aws me-2"></i>Ustawienia S3', 'url' => ['/s3/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-tint me-2"></i>Znak wodny', 'url' => ['/watermark/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-robot me-2"></i>AI/Analiza', 'url' => ['/ai/index'], 'encode' => false],
                        ['label' => '<i class="fas fa-camera me-2"></i>EXIF', 'url' => ['/exif/index'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fas fa-cogs me-2"></i>Ustawienia', 'url' => ['/settings/index'], 'encode' => false],
                    ],
                ],
                [
                    'label' => '<i class="fas fa-clipboard-list me-2"></i>Dziennik zdarzeń' . ($todayErrors > 0 ? ' <span class="badge bg-danger ms-1">' . $todayErrors . '</span>' : ''),
                    'items' => [
                        ['label' => '<i class="fas fa-tachometer-alt me-2"></i>Dashboard', 'url' => ['/audit-log/dashboard'], 'encode' => false],
                        ['label' => '<i class="fas fa-list me-2"></i>Wszystkie zdarzenia', 'url' => ['/audit-log/index'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fas fa-exclamation-triangle me-2 text-danger"></i>Błędy i ostrzeżenia', 'url' => ['/audit-log/index', 'AuditLogSearch[severity]' => 'error'], 'encode' => false],
                        ['label' => '<i class="fas fa-sign-in-alt me-2 text-success"></i>Logowania', 'url' => ['/audit-log/index', 'AuditLogSearch[action]' => 'login'], 'encode' => false],
                        ['label' => '<i class="fas fa-cogs me-2 text-info"></i>Zmiany ustawień', 'url' => ['/audit-log/index', 'AuditLogSearch[action]' => 'settings'], 'encode' => false],
                        ['label' => '<i class="fas fa-upload me-2 text-primary"></i>Przesłane pliki', 'url' => ['/audit-log/index', 'AuditLogSearch[action]' => 'upload'], 'encode' => false],
                        '<div class="dropdown-divider"></div>',
                        ['label' => '<i class="fas fa-download me-2"></i>Eksport danych', 'url' => '#', 'linkOptions' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#auditExportModal'], 'encode' => false],
                        ['label' => '<i class="fas fa-broom me-2"></i>Czyszczenie starych wpisów', 'url' => '#', 'linkOptions' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#auditCleanupModal'], 'encode' => false],
                    ],
                    'encode' => false
                ],
            ];

            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => '<i class="fas fa-user me-2"></i>Logout (' . Html::encode(Yii::$app->user->identity->username) . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post'],
                    'encode' => false
                ];
            }

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav me-auto'],
                'items' => $menuItems,
            ]);

            NavBar::end();
            ?>
        </header>

        <main id="main" class="flex-shrink-0" role="main">
            <div class="container-fluid">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                <?php endif ?>

                <?= Alert::widget() ?>

<?= $content ?>
            </div>
        </main>

        <footer id="footer" class="mt-auto py-3 bg-light">
            <div class="container">
                <div class="row text-muted">
                    <div class="col-md-12 text-center">
                        <p>&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Globalne modale dla dziennika zdarzeń -->
<?php if (!Yii::$app->user->isGuest): ?>
            <!-- Modal eksportu dziennika zdarzeń (globalny) -->
            <div class="modal fade" id="auditExportModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
    <?= Html::beginForm(['/audit-log/export'], 'post') ?>
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-download me-2"></i>Eksport dziennika zdarzeń
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Format eksportu:</label>
                                <select name="format" class="form-control">
                                    <option value="csv">CSV (Excel)</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Zakres czasowy:</label>
                                <select name="quick_range" class="form-control" id="quickRangeSelect">
                                    <option value="">Wybierz zakres...</option>
                                    <option value="today">Dzisiaj</option>
                                    <option value="week">Ostatnie 7 dni</option>
                                    <option value="month">Ostatnie 30 dni</option>
                                    <option value="custom">Niestandardowy zakres</option>
                                </select>
                            </div>
                            <div class="row" id="customDateRange" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Data od:</label>
                                    <input type="date" name="date_from" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data do:</label>
                                    <input type="date" name="date_to" class="form-control">
                                </div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Plik zostanie automatycznie pobrany po wygenerowaniu.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Eksportuj
                            </button>
                        </div>
    <?= Html::endForm() ?>
                    </div>
                </div>
            </div>

            <!-- Modal czyszczenia dziennika zdarzeń (globalny) -->
            <div class="modal fade" id="auditCleanupModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
    <?= Html::beginForm(['/audit-log/cleanup'], 'post') ?>
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-broom me-2"></i>Czyszczenie dziennika zdarzeń
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Uwaga!</strong> Ta operacja jest nieodwracalna. Stare wpisy zostaną trwale usunięte.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Usuń wpisy starsze niż:</label>
                                <select name="days" class="form-control">
                                    <option value="30">30 dni</option>
                                    <option value="60">60 dni</option>
                                    <option value="90" selected>90 dni (zalecane)</option>
                                    <option value="180">180 dni</option>
                                    <option value="365">1 rok</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="confirmCleanup" required>
                                    <label class="form-check-label" for="confirmCleanup">
                                        Potwierdzam, że chcę usunąć stare wpisy dziennika
                                    </label>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Minimum 30 dni - wpisy młodsze nie mogą zostać usunięte.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-trash me-2"></i>Wyczyść dziennik
                            </button>
                        </div>
    <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
<?php endif; ?>

        <!-- Powiadomienia o błędach dziennika (opcjonalnie) -->
<?php if (!Yii::$app->user->isGuest && $todayErrors > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 1050; max-width: 350px;" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Uwaga!</strong> W dzienniku zdarzeń odnotowano <?= $todayErrors ?> błędów/ostrzeżeń dzisiaj.
                <?=
                Html::a('Zobacz szczegóły', ['/audit-log/index', 'AuditLogSearch[severity]' => 'error'],
                        ['class' => 'alert-link ms-2'])
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

<?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>