<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Settings;
\backend\assets\AppAsset::registerControllerCss($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */

$this->title = 'Import zdjęć';
$this->params['breadcrumbs'][] = ['label' => 'Zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Poczekalnia', 'url' => ['queue']];
$this->params['breadcrumbs'][] = $this->title;

// Pobierz domyślny katalog importu z ustawień
$importDirectory = Settings::findOne(['key' => 'upload.import_directory']);
$defaultDirectory = $importDirectory ? $importDirectory->value : 'uploads/import';

// DEBUG: Sprawdź różne możliwe ścieżki
$possiblePaths = [
    Yii::getAlias('@webroot/' . $defaultDirectory),
    Yii::getAlias('@app/../' . $defaultDirectory),
    $defaultDirectory,
    Yii::getAlias('@webroot') . '/' . $defaultDirectory
];

$directoryExists = false;
$directoryReadable = false;
$actualPath = '';
$fileCount = 0;
$imageCount = 0;

foreach ($possiblePaths as $path) {
    if (is_dir($path)) {
        $directoryExists = true;
        $directoryReadable = is_readable($path);
        $actualPath = $path;
        
        if ($directoryReadable) {
            try {
                $allFiles = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
                $fileCount = iterator_count($allFiles);
                
                // Zlicz pliki graficzne (z uwzględnieniem wielkich liter)
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'JPG', 'JPEG', 'PNG', 'GIF'];
                $directory = new \DirectoryIterator($path);
                foreach ($directory as $file) {
                    if ($file->isFile() && in_array($file->getExtension(), $imageExtensions)) {
                        $imageCount++;
                    }
                }
            } catch (\Exception $e) {
                // Błąd podczas odczytu - prawdopodobnie brak uprawnień
                $directoryReadable = false;
            }
        }
        break;
    }
}

// Sprawdź czy katalog docelowy jest zapisywalny
$tempDirPath = Yii::getAlias('@webroot/uploads/temp');
$tempDirExists = is_dir($tempDirPath);
$tempDirWritable = $tempDirExists && is_writable($tempDirPath);

// Sprawdź czy katalog miniatur jest zapisywalny
$thumbsDirPath = Yii::getAlias('@webroot/uploads/thumbnails');
$thumbsDirExists = is_dir($thumbsDirPath);
$thumbsDirWritable = $thumbsDirExists && is_writable($thumbsDirPath);
?>

<div class="photo-import">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-images me-2"></i>Poczekalnia', ['queue'], [
                'class' => 'btn btn-outline-primary'
            ]) ?>
            <?= Html::a('<i class="fas fa-list me-2"></i>Kolejka zadań', ['queue/index'], [
                'class' => 'btn btn-outline-warning'
            ]) ?>
            <?= Html::a('<i class="fas fa-cogs me-2"></i>Ustawienia', ['settings/index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>
    <!-- Status katalogu importu -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-folder me-2"></i>Status katalogu importu
            </h5>
        </div>
        <div class="card-body">
            <?php if ($directoryExists && $directoryReadable && $imageCount > 0): ?>
                <div class="alert alert-success">
                    <div class="row">
                        <div class="col-md-8">
                            <h6><i class="fas fa-check-circle me-2"></i>Katalog gotowy do importu</h6>
                            <p class="mb-2">
                                <strong>Katalog:</strong> <code><?= Html::encode($actualPath) ?></code><br>
                                <strong>Znaleziono plików:</strong> <?= $imageCount ?> obrazów (<?= $fileCount ?> łącznie)
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="me-3">
                                    <i class="fas fa-images fa-3x text-success"></i>
                                </div>
                                <div>
                                    <div class="h2 mb-0 text-success"><?= $imageCount ?></div>
                                    <small class="text-muted">plików</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Problem z katalogiem importu</h6>
                    
                    <?php if (!$directoryExists): ?>
                        <p>Katalog nie istnieje. Sprawdzono ścieżki:</p>
                        <ul class="small mb-2">
                            <?php foreach ($possiblePaths as $path): ?>
                                <li><code><?= Html::encode($path) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="mb-0"><strong>Rozwiązanie:</strong> Utwórz katalog <code><?= Html::encode($possiblePaths[0]) ?></code></p>
                    <?php elseif (!$directoryReadable): ?>
                        <p><strong>Katalog istnieje ale brak uprawnień:</strong> <code><?= Html::encode($actualPath) ?></code></p>
                        <p class="mb-0"><strong>Rozwiązanie:</strong> <code>chmod 755 <?= Html::encode($actualPath) ?></code></p>
                    <?php elseif ($imageCount === 0): ?>
                        <p><strong>Katalog jest pusty</strong> (<?= $fileCount ?> plików, ale żaden nie jest obrazem)</p>
                        <p class="mb-0"><strong>Obsługiwane formaty:</strong> JPG, JPEG, PNG, GIF</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <!-- Formularz importu -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-import me-2"></i>Import zdjęć z FTP (w partiach)
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'action' => ['import-from-ftp'],
                        'method' => 'post',
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <div class="mb-3">
                        <label class="form-label">Katalog importu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-folder-open"></i></span>
                            <input type="text" class="form-control" value="<?= Html::encode($defaultDirectory) ?>" readonly>
                            <?php if ($directoryExists && $directoryReadable): ?>
                                <span class="input-group-text bg-success text-white" title="Katalog jest dostępny i odczytywalny"><i class="fas fa-check"></i></span>
                            <?php else: ?>
                                <span class="input-group-text bg-danger text-white" title="Katalog nie istnieje lub brak uprawnień do odczytu"><i class="fas fa-times"></i></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">
                            <?php if ($directoryExists && $directoryReadable): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> 
                                    Katalog dostępny: <code><?= Html::encode($actualPath) ?></code><br>
                                    Zawiera <?= $fileCount ?> plików, w tym <?= $imageCount ?> obrazów.
                                </span>
                            <?php elseif ($directoryExists): ?>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Katalog istnieje ale brak uprawnień: <code><?= Html::encode($actualPath) ?></code><br>
                                    Zmień uprawnienia: <code>chmod 755 <?= Html::encode($actualPath) ?></code>
                                </span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="fas fa-times-circle"></i>
                                    Katalog nie istnieje. Utwórz jeden z katalogów:<br>
                                    <?php foreach ($possiblePaths as $path): ?>
                                        <code><?= Html::encode($path) ?></code><br>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Katalogi docelowe</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-folder"></i></span>
                                    <input type="text" class="form-control" value="uploads/temp" readonly>
                                    <?php if ($tempDirWritable): ?>
                                        <span class="input-group-text bg-success text-white" title="Katalog jest dostępny i zapisywalny"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="input-group-text bg-danger text-white" title="Katalog nie istnieje lub brak uprawnień do zapisu"><i class="fas fa-times"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-folder"></i></span>
                                    <input type="text" class="form-control" value="uploads/thumbnails" readonly>
                                    <?php if ($thumbsDirWritable): ?>
                                        <span class="input-group-text bg-success text-white" title="Katalog jest dostępny i zapisywalny"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="input-group-text bg-danger text-white" title="Katalog nie istnieje lub brak uprawnień do zapisu"><i class="fas fa-times"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!$tempDirWritable || !$thumbsDirWritable): ?>
                            <div class="form-text text-danger">
                                <?php if (!$tempDirExists): ?>
                                    Katalog tymczasowy nie istnieje. Utwórz katalog <?= $tempDirPath ?>.<br>
                                <?php elseif (!$tempDirWritable): ?>
                                    Brak uprawnień do zapisu w katalogu tymczasowym. Zmień uprawnienia: chmod 775 <?= $tempDirPath ?><br>
                                <?php endif; ?>
                                
                                <?php if (!$thumbsDirExists): ?>
                                    Katalog miniatur nie istnieje. Utwórz katalog <?= $thumbsDirPath ?>.<br>
                                <?php elseif (!$thumbsDirWritable): ?>
                                    Brak uprawnień do zapisu w katalogu miniatur. Zmień uprawnienia: chmod 775 <?= $thumbsDirPath ?>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="form-text text-success">Katalogi docelowe są dostępne i zapisywalne.</div>
                        <?php endif; ?>
                    </div>
                    <hr class="my-4">

                    <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Opcje importu</h6>
                    
                    <!-- NOWA OPCJA: Rozmiar partii -->
                    <div class="mb-3">
                        <label class="form-label">Rozmiar partii</label>
                        <?= Html::dropDownList('batch_size', 10, [
                            5 => '5 plików na partię (bardzo bezpieczne)',
                            10 => '10 plików na partię (zalecane)',
                            15 => '15 plików na partię',
                            20 => '20 plików na partię',
                            25 => '25 plików na partię',
                            30 => '30 plików na partię (ryzykowne)'
                        ], ['class' => 'form-select', 'id' => 'batch-size-select']) ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Mniejsze partie = mniejsze ryzyko timeout, ale więcej zadań w kolejce.
                            <span id="batch-estimation" class="fw-bold text-primary"></span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="recursive" id="recursive" value="1" checked>
                                <label class="form-check-label" for="recursive">
                                    <i class="fas fa-sitemap me-1"></i> Importuj rekursywnie
                                </label>
                                <div class="form-text">Szukaj plików także w podkatalogach</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="delete_originals" id="delete-originals" value="1">
                                <label class="form-check-label" for="delete-originals">
                                    <i class="fas fa-trash-alt me-1"></i> Usuń oryginalne pliki
                                </label>
                                <div class="form-text">Po imporcie usuń pliki źródłowe</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="run_now" id="run-now" value="1">
                        <label class="form-check-label" for="run-now">
                            <i class="fas fa-play-circle me-1"></i> Uruchom pierwszą partię natychmiast
                        </label>
                        <div class="form-text">Pierwsze zadanie zostanie uruchomione od razu, pozostałe będą w kolejce</div>
                    </div>

                    <div class="mt-4">
                        <?= Html::submitButton('<i class="fas fa-file-import me-2"></i>Rozpocznij import w partiach', [
                            'class' => 'btn btn-primary',
                            'disabled' => !($directoryExists && $directoryReadable) || !$tempDirWritable || !$thumbsDirWritable || $imageCount === 0,
                            'data' => [
                                'confirm' => 'Czy na pewno chcesz rozpocząć import zdjęć w partiach z wybranego katalogu?',
                            ],
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <!-- Informacje o imporcie -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Import w partiach - jak działa?
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Korzyści importu w partiach:</h6>
                    <ul class="mb-4">
                        <li><strong>Brak timeout</strong> - każda partia jest przetwarzana osobno</li>
                        <li><strong>Równoległa praca</strong> - wiele partii może być przetwarzanych jednocześnie</li>
                        <li><strong>Odporność na błędy</strong> - błąd w jednej partii nie zatrzymuje całego importu</li>
                        <li><strong>Monitoring</strong> - szczegółowe logi dla każdej partii w kolejce zadań</li>
                        <li><strong>Elastyczność</strong> - możliwość dostosowania rozmiaru partii</li>
                    </ul>
                    
                    <h6 class="mb-3">Przebieg importu w partiach:</h6>
                    <ol class="mb-4">
                        <li>System skanuje katalog i znajduje wszystkie pliki graficzne</li>
                        <li>Pliki są dzielone na partie według wybranego rozmiaru</li>
                        <li>Dla każdej partii tworzone jest osobne zadanie w kolejce</li>
                        <li>Każda partia jest przetwarzana niezależnie</li>
                        <li>Zaimportowane zdjęcia trafiają do poczekalni</li>
                        <li>Po imporcie oryginalne pliki są usuwane (jeśli wybrano opcję)</li>
                    </ol>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-lightbulb me-2"></i>Zalecenia:</h6>
                        <ul class="mb-0">
                            <li><strong>10-15 plików na partię</strong> - optymalna wydajność</li>
                            <li><strong>Monitoruj postęp</strong> w sekcji "Kolejka zadań"</li>
                            <li><strong>Nie dodawaj nowych plików</strong> podczas importu</li>
                            <li><strong>Sprawdzaj logi</strong> w przypadku błędów</li>
                        </ul>
                    </div>
                    
                    <?php if ($imageCount > 0): ?>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-calculator me-2"></i>Szacowanie dla Twoich plików:</h6>
                            <div id="batch-calculation">
                                <!-- Dynamicznie aktualizowane przez JavaScript -->
                                <p class="mb-1">
                                    <strong>Plików do importu:</strong> <?= $imageCount ?><br>
                                    <strong>Partii przy 10 plikach:</strong> <?= ceil($imageCount / 10) ?><br>
                                    <strong>Szacowany czas:</strong> <?= ceil($imageCount / 10) * 1 ?> - <?= ceil($imageCount / 10) * 2 ?> minut
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Status systemu -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Status systemu
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Pobierz dane o zadaniach w kolejce
                    $pendingCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM queued_job WHERE status = 0")->queryScalar();
                    $processingCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM queued_job WHERE status = 1")->queryScalar();
                    $queuedPhotosCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM photo WHERE status = 0")->queryScalar();
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="mb-2">Kolejka zadań</h6>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="badge bg-warning"><?= $pendingCount ?></span>
                                    </div>
                                    <div>Zadania oczekujące</div>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="me-3">
                                        <span class="badge bg-primary"><?= $processingCount ?></span>
                                    </div>
                                    <div>Zadania przetwarzane</div>
                                </div>
                            </div>
                            
                            <div>
                                <h6 class="mb-2">Poczekalnia zdjęć</h6>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="badge bg-info"><?= $queuedPhotosCount ?></span>
                                    </div>
                                    <div>Zdjęcia oczekujące na zatwierdzenie</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-2">Automatyczne przetwarzanie</h6>
                            <p class="small">
                                Aby automatycznie przetwarzać zadania, skonfiguruj cron:
                            </p>
                            <pre class="bg-light p-2 small mb-0"><code>*/5 * * * * php <?= Yii::getAlias('@app') ?>/yii queue/run</code></pre>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <?= Html::a('<i class="fas fa-list me-2"></i>Zobacz kolejkę zadań', ['queue/index'], [
                            'class' => 'btn btn-sm btn-outline-secondary'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const batchSizeSelect = document.getElementById('batch-size-select');
    const batchEstimation = document.getElementById('batch-estimation');
    const totalFiles = <?= $imageCount ?>;
    
    function updateBatchEstimation() {
        if (!batchSizeSelect || !batchEstimation || totalFiles === 0) return;
        
        const batchSize = parseInt(batchSizeSelect.value);
        const totalBatches = Math.ceil(totalFiles / batchSize);
        const estimatedTimeMin = totalBatches * 1; // 1 minuta na partię minimum
        const estimatedTimeMax = totalBatches * 2; // 2 minuty na partię maksimum
        
        batchEstimation.innerHTML = `Utworzy ${totalBatches} zadań (czas: ${estimatedTimeMin}-${estimatedTimeMax} min)`;
        
        // Aktualizuj także szczegółowe obliczenia jeśli istnieją
        const batchCalculation = document.getElementById('batch-calculation');
        if (batchCalculation) {
            batchCalculation.innerHTML = `
                <p class="mb-1">
                    <strong>Plików do importu:</strong> ${totalFiles}<br>
                    <strong>Partii przy ${batchSize} plikach:</strong> ${totalBatches}<br>
                    <strong>Szacowany czas:</strong> ${estimatedTimeMin} - ${estimatedTimeMax} minut
                </p>
            `;
        }
    }
    
    // Inicjalna aktualizacja
    updateBatchEstimation();
    
    // Aktualizacja przy zmianie rozmiaru partii
    if (batchSizeSelect) {
        batchSizeSelect.addEventListener('change', updateBatchEstimation);
    }
    
    // Potwierdzenie przed wysłaniem formularza
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (totalFiles === 0) {
                e.preventDefault();
                alert('Brak plików do importu w katalogu.');
                return false;
            }
            
            const batchSize = parseInt(batchSizeSelect.value);
            const totalBatches = Math.ceil(totalFiles / batchSize);
            
            if (totalBatches > 20) {
                if (!confirm(`Zostanie utworzonych ${totalBatches} zadań. To może być dużo zadań w kolejce. Czy kontynuować?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>