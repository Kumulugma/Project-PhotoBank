<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Settings;

/* @var $this yii\web\View */

$this->title = 'Import zdjęć';
$this->params['breadcrumbs'][] = ['label' => 'Zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Poczekalnia', 'url' => ['queue']];
$this->params['breadcrumbs'][] = $this->title;

// Pobierz domyślny katalog importu z ustawień
$importDirectory = Settings::findOne(['key' => 'upload.import_directory']);
$defaultDirectory = $importDirectory ? $importDirectory->value : 'uploads/import';

// Sprawdź, czy katalog importu istnieje i ma pliki
$fullPath = Yii::getAlias('@webroot/' . $defaultDirectory);
$directoryExists = is_dir($fullPath);
$directoryReadable = $directoryExists && is_readable($fullPath);
$fileCount = 0;
$imageCount = 0;

if ($directoryReadable) {
    $allFiles = new \FilesystemIterator($fullPath, \FilesystemIterator::SKIP_DOTS);
    $fileCount = iterator_count($allFiles);
    
    // Zlicz pliki graficzne
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imageCount = 0;
    $directory = new \DirectoryIterator($fullPath);
    foreach ($directory as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), $imageExtensions)) {
            $imageCount++;
        }
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
            <?= Html::a('<i class="fas fa-cogs me-2"></i>Ustawienia', ['settings/index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <!-- Formularz importu -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-import me-2"></i>Import zdjęć z FTP
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
                            <?php if ($directoryReadable): ?>
                                <span class="input-group-text bg-success text-white" title="Katalog jest dostępny i odczytywalny"><i class="fas fa-check"></i></span>
                            <?php else: ?>
                                <span class="input-group-text bg-danger text-white" title="Katalog nie istnieje lub brak uprawnień do odczytu"><i class="fas fa-times"></i></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">
                            <?php if ($directoryReadable): ?>
                                <span class="text-success">Katalog dostępny. Zawiera <?= $fileCount ?> plików, w tym <?= $imageCount ?> obrazów.</span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <?php if (!$directoryExists): ?>
                                        Katalog nie istnieje. Utwórz katalog <?= $fullPath ?>.
                                    <?php else: ?>
                                        Brak uprawnień do odczytu katalogu. Zmień uprawnienia: chmod 755 <?= $fullPath ?>
                                    <?php endif; ?>
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
                            <i class="fas fa-play-circle me-1"></i> Uruchom zadanie natychmiast
                        </label>
                        <div class="form-text">Importuj zdjęcia od razu, zamiast dodawać zadanie do kolejki</div>
                    </div>

                    <div class="mt-4">
                        <?= Html::submitButton('<i class="fas fa-file-import me-2"></i>Rozpocznij import', [
                            'class' => 'btn btn-primary',
                            'disabled' => !$directoryReadable || !$tempDirWritable || !$thumbsDirWritable || $imageCount === 0,
                            'data' => [
                                'confirm' => 'Czy na pewno chcesz rozpocząć import zdjęć z wybranego katalogu?',
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
                        <i class="fas fa-info-circle me-2"></i>Informacje o procesie importu
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Przebieg importu zdjęć</h6>
                    <ol class="mb-4">
                        <li>System wyszukuje pliki graficzne (.jpg, .jpeg, .png, .gif) w katalogu importu</li>
                        <li>Każdy plik jest kopiowany do katalogu tymczasowego <code>uploads/temp</code></li>
                        <li>Dla każdego pliku tworzone są miniatury we wszystkich skonfigurowanych rozmiarach</li>
                        <li>Zdjęcia są dodawane do poczekalni (status: oczekujące) - wymagają zatwierdzenia</li>
                        <li>Jeśli wybrano opcję usuwania oryginałów, pliki źródłowe są usuwane</li>
                    </ol>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Uwaga:</strong> Duże pliki mogą wymagać długiego czasu przetwarzania. Zalecamy używanie opcji dodania zadania do kolejki (nie zaznaczając opcji "Uruchom zadanie natychmiast") i pozostawienie przetwarzania mechanizmowi cron.
                    </div>
                    
                    <h6 class="mb-3">Dostępne formaty plików</h6>
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-file-image fa-2x text-primary mb-2"></i>
                                <div>JPEG (.jpg, .jpeg)</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-file-image fa-2x text-success mb-2"></i>
                                <div>PNG (.png)</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-file-image fa-2x text-warning mb-2"></i>
                                <div>GIF (.gif)</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <div>Inne (pominięte)</div>
                            </div>
                        </div>
                    </div>
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
                            <h6 class="mb-2">Uruchamianie importu</h6>
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