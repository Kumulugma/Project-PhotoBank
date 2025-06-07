<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\QueuedJob;
use common\models\Photo;
use yii\helpers\Console;

/**
 * Debug controller for troubleshooting imports and system issues
 */
class DebugController extends Controller
{
    /**
     * Sprawdza ostatnie zadania importu i problemowe pliki
     */
    public function actionLastImports()
    {
        $this->stdout("=== ANALIZA OSTATNICH IMPORTÓW ===\n", Console::BOLD);
        
        // 1. Sprawdź ostatnie zadania importu
        $lastJobs = QueuedJob::find()
            ->where(['type' => 'import_photos'])
            ->orWhere(['type' => 'import_photos_batch'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();
            
        $this->stdout("\n1. OSTATNIE ZADANIA IMPORTU:\n", Console::FG_BLUE);
        
        if (empty($lastJobs)) {
            $this->stdout("  Brak zadań importu w bazie danych\n", Console::FG_YELLOW);
        } else {
            foreach ($lastJobs as $job) {
                $status = match($job->status) {
                    0 => 'PENDING',
                    1 => 'PROCESSING', 
                    2 => 'COMPLETED',
                    3 => 'FAILED'
                };
                
                $results = json_decode($job->results, true) ?? [];
                $imported = $results['imported'] ?? 0;
                $errors = $results['error_count'] ?? 0;
                
                $this->stdout("  ID: {$job->id} | Type: {$job->type} | Status: {$status} | Imported: {$imported} | Errors: {$errors} | " . date('Y-m-d H:i:s', $job->created_at) . "\n");
                
                // Sprawdź szczegóły błędów w ostatnich zadaniach
                if ($job->status == 2 && $imported == 0) { // COMPLETED ale 0 imported
                    $this->stdout("    ⚠️  PODEJRZANE: Zadanie zakończone ale 0 zaimportowanych!\n", Console::FG_RED);
                    $this->analyzeJob($job);
                }
            }
        }
        
        // 2. Sprawdź katalog importu
        $this->checkImportDirectory();
        
        // 3. Sprawdź zasoby systemowe
        $this->checkSystemResources();
        
        // 4. Sprawdź ostatnie zdjęcia w bazie
        $this->checkRecentPhotos();
        
        return ExitCode::OK;
    }
    
    private function analyzeJob($job)
    {
        $this->stdout("      Analiza zadania ID: {$job->id}\n", Console::FG_YELLOW);
        
        $data = json_decode($job->data, true) ?? [];
        $results = json_decode($job->results, true) ?? [];
        
        $directory = $data['directory'] ?? 'unknown';
        $this->stdout("      Katalog: {$directory}\n");
        
        if (isset($results['debug_info'])) {
            $debug = $results['debug_info'];
            $this->stdout("      Debug info:\n");
            foreach ($debug as $key => $value) {
                $this->stdout("        {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n");
            }
        }
        
        if (isset($results['errors']) && !empty($results['errors'])) {
            $this->stdout("      Błędy:\n", Console::FG_RED);
            foreach ($results['errors'] as $error) {
                $filename = $error['filename'] ?? 'unknown';
                $errorMsg = $error['error'] ?? 'unknown error';
                $this->stdout("        - {$filename}: {$errorMsg}\n");
            }
        }
        
        if (isset($results['processed']) && !empty($results['processed'])) {
            $this->stdout("      Przetworzone pliki:\n", Console::FG_GREEN);
            foreach ($results['processed'] as $processed) {
                $filename = $processed['filename'] ?? 'unknown';
                $photoId = $processed['photo_id'] ?? 'no ID';
                $this->stdout("        - {$filename} -> Photo ID: {$photoId}\n");
            }
        }
    }
    
    private function checkImportDirectory()
    {
        $this->stdout("\n2. SPRAWDZENIE KATALOGU IMPORTU:\n", Console::FG_BLUE);
        
        $importDir = Yii::getAlias('@webroot/uploads/import');
        if (!is_dir($importDir)) {
            $this->stdout("  ❌ Katalog nie istnieje: {$importDir}\n", Console::FG_RED);
            return;
        }
        
        $this->stdout("  📁 Katalog: {$importDir}\n");
        $this->stdout("  🔓 Odczyt: " . (is_readable($importDir) ? 'OK' : 'BŁĄD') . "\n");
        
        // Znajdź wszystkie pliki graficzne
        try {
            $files = \yii\helpers\FileHelper::findFiles($importDir, [
                'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.JPG', '*.JPEG', '*.PNG', '*.GIF'],
                'recursive' => true
            ]);
            
            $this->stdout("  📄 Łącznie plików: " . count($files) . "\n");
            
            if (count($files) > 0) {
                $this->stdout("  🔍 Analiza ostatnich 20 plików:\n");
                
                // Posortuj pliki po dacie modyfikacji
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                for ($i = 0; $i < min(20, count($files)); $i++) {
                    $file = $files[$i];
                    $filename = basename($file);
                    $size = filesize($file);
                    $readable = is_readable($file);
                    $mtime = date('Y-m-d H:i:s', filemtime($file));
                    
                    $status = $readable ? '✅' : '❌';
                    $sizeStr = $size ? $this->formatBytes($size) : 'ERROR';
                    
                    $this->stdout("    {$status} {$filename} | {$sizeStr} | {$mtime}");
                    
                    // Sprawdź czy to może być problematyczny plik
                    if (!$readable) {
                        $this->stdout(" | ❌ BRAK UPRAWNIEŃ");
                    } elseif ($size === false || $size === 0) {
                        $this->stdout(" | ❌ PLIK PUSTY");
                    } elseif ($size > 50 * 1024 * 1024) { // > 50MB
                        $this->stdout(" | ⚠️  BARDZO DUŻY");
                    }
                    
                    // Sprawdź czy już istnieje w bazie
                    $existingPhoto = Photo::find()
                        ->where(['like', 'file_name', basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)) . '%', false])
                        ->one();
                    
                    if ($existingPhoto) {
                        $this->stdout(" | ✅ JUŻ W BAZIE (ID: {$existingPhoto->id})");
                    }
                    
                    $this->stdout("\n");
                }
            }
            
        } catch (\Exception $e) {
            $this->stdout("  ❌ Błąd skanowania: " . $e->getMessage() . "\n", Console::FG_RED);
        }
    }
    
    private function checkSystemResources()
    {
        $this->stdout("\n3. ZASOBY SYSTEMOWE:\n", Console::FG_BLUE);
        
        // Pamięć PHP
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        $this->stdout("  🧠 Memory limit: {$memoryLimit}\n");
        $this->stdout("  📊 Memory usage: " . $this->formatBytes($memoryUsage) . "\n");
        $this->stdout("  📈 Memory peak: " . $this->formatBytes($memoryPeak) . "\n");
        
        // Czas wykonania
        $maxExecutionTime = ini_get('max_execution_time');
        $this->stdout("  ⏱️  Max execution time: {$maxExecutionTime}s\n");
        
        // Miejsce na dysku
        $uploadDir = Yii::getAlias('@webroot/uploads');
        if (is_dir($uploadDir)) {
            $freeSpace = disk_free_space($uploadDir);
            $totalSpace = disk_total_space($uploadDir);
            
            $this->stdout("  💾 Miejsce na dysku: " . $this->formatBytes($freeSpace) . " / " . $this->formatBytes($totalSpace) . "\n");
            
            if ($freeSpace < 1024 * 1024 * 1024) { // < 1GB
                $this->stdout("  ⚠️  MAŁO MIEJSCA NA DYSKU!\n", Console::FG_RED);
            }
        }
        
        // Uprawnienia katalogów
        $dirs = [
            'temp' => Yii::getAlias('@webroot/uploads/temp'),
            'thumbnails' => Yii::getAlias('@webroot/uploads/thumbnails'),
            'import' => Yii::getAlias('@webroot/uploads/import')
        ];
        
        $this->stdout("  📁 Uprawnienia katalogów:\n");
        foreach ($dirs as $name => $path) {
            $exists = is_dir($path);
            $readable = $exists && is_readable($path);
            $writable = $exists && is_writable($path);
            
            $status = $exists ? ($readable && $writable ? '✅' : '⚠️') : '❌';
            $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
            
            $this->stdout("    {$status} {$name}: {$path} | {$perms}");
            if (!$exists) $this->stdout(" | NIE ISTNIEJE");
            elseif (!$readable) $this->stdout(" | BRAK ODCZYTU");
            elseif (!$writable) $this->stdout(" | BRAK ZAPISU");
            $this->stdout("\n");
        }
    }
    
    private function checkRecentPhotos()
    {
        $this->stdout("\n4. OSTATNIE ZDJĘCIA W BAZIE:\n", Console::FG_BLUE);
        
        $recentPhotos = Photo::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->all();
            
        $this->stdout("  📸 Ostatnie 20 zdjęć:\n");
        foreach ($recentPhotos as $photo) {
            $status = match($photo->status) {
                0 => 'QUEUE',
                1 => 'ACTIVE', 
                2 => 'DELETED'
            };
            
            $created = date('Y-m-d H:i:s', $photo->created_at);
            $this->stdout("    ID: {$photo->id} | {$photo->file_name} | {$status} | {$created}\n");
        }
        
        // Statystyki
        $totalPhotos = Photo::find()->count();
        $queuePhotos = Photo::find()->where(['status' => 0])->count();
        $activePhotos = Photo::find()->where(['status' => 1])->count();
        
        $this->stdout("\n  📊 Statystyki:\n");
        $this->stdout("    Łącznie: {$totalPhotos}\n");
        $this->stdout("    W kolejce: {$queuePhotos}\n");
        $this->stdout("    Aktywne: {$activePhotos}\n");
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Sprawdza konkretne problematyczne pliki
     */
    public function actionCheckFiles($directory = 'uploads/import')
    {
        $this->stdout("=== SZCZEGÓŁOWA ANALIZA PLIKÓW ===\n", Console::BOLD);
        
        $importDir = Yii::getAlias('@webroot/' . $directory);
        if (!is_dir($importDir)) {
            $this->stdout("Katalog nie istnieje: {$importDir}\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }
        
        $files = \yii\helpers\FileHelper::findFiles($importDir, [
            'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.JPG', '*.JPEG', '*.PNG', '*.GIF'],
            'recursive' => true
        ]);
        
        $this->stdout("Znaleziono " . count($files) . " plików\n");
        
        foreach ($files as $index => $file) {
            $this->stdout("\n--- PLIK " . ($index + 1) . "/" . count($files) . " ---\n");
            $this->stdout("Plik: " . basename($file) . "\n");
            
            // Podstawowe informacje
            $size = filesize($file);
            $readable = is_readable($file);
            $this->stdout("Rozmiar: " . ($size ? $this->formatBytes($size) : 'ERROR') . "\n");
            $this->stdout("Odczytywalny: " . ($readable ? 'TAK' : 'NIE') . "\n");
            
            if (!$readable) {
                $this->stdout("❌ PLIK NIEOCZYTYWALNY!\n", Console::FG_RED);
                continue;
            }
            
            if ($size === false || $size === 0) {
                $this->stdout("❌ PLIK PUSTY!\n", Console::FG_RED);
                continue;
            }
            
            // Sprawdź MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file);
            finfo_close($finfo);
            $this->stdout("MIME type: {$mimeType}\n");
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes)) {
                $this->stdout("❌ NIEPRAWIDŁOWY TYP MIME!\n", Console::FG_RED);
                continue;
            }
            
            // Sprawdź czy można odczytać jako obraz
            $imageInfo = @getimagesize($file);
            if ($imageInfo === false) {
                $this->stdout("❌ NIE MOŻNA ODCZYTAĆ JAKO OBRAZ!\n", Console::FG_RED);
                continue;
            }
            
            $this->stdout("Wymiary: {$imageInfo[0]} x {$imageInfo[1]}\n");
            
            // Sprawdź czy już istnieje w bazie
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $existingPhoto = Photo::find()
                ->where(['like', 'file_name', $basename . '%', false])
                ->one();
                
            if ($existingPhoto) {
                $this->stdout("✅ JUŻ ISTNIEJE W BAZIE (ID: {$existingPhoto->id})\n", Console::FG_GREEN);
            } else {
                $this->stdout("🔄 NOWY PLIK\n", Console::FG_YELLOW);
            }
            
            // Test tworzenia miniatury
            try {
                $testImage = imagecreatefromjpeg($file);
                if ($testImage === false) {
                    $this->stdout("❌ NIE MOŻNA UTWORZYĆ ZASOBU OBRAZU!\n", Console::FG_RED);
                } else {
                    imagedestroy($testImage);
                    $this->stdout("✅ OBRAZ OK\n", Console::FG_GREEN);
                }
            } catch (\Exception $e) {
                $this->stdout("❌ BŁĄD TWORZENIA OBRAZU: " . $e->getMessage() . "\n", Console::FG_RED);
            }
        }
        
        return ExitCode::OK;
    }

    /**
     * Sprawdź status ostatnich zadań importu
     */
    public function actionImportJobs($limit = 5)
    {
        $this->stdout("=== OSTATNIE ZADANIA IMPORTU ===\n", Console::BOLD);
        
        $jobs = QueuedJob::find()
            ->where(['type' => 'import_photos'])
            ->orWhere(['type' => 'import_photos_batch'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        if (empty($jobs)) {
            $this->stdout("Brak zadań importu w bazie danych\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        foreach ($jobs as $job) {
            $this->stdout("\n--- ZADANIE ID: {$job->id} ---\n", Console::BOLD);
            $this->stdout("Typ: {$job->type}\n");
            $this->stdout("Status: " . $this->getStatusName($job->status) . "\n");
            $this->stdout("Utworzono: " . date('Y-m-d H:i:s', $job->created_at) . "\n");
            $this->stdout("Aktualizowano: " . date('Y-m-d H:i:s', $job->updated_at) . "\n");
            
            if ($job->started_at) {
                $this->stdout("Rozpoczęto: " . date('Y-m-d H:i:s', $job->started_at) . "\n");
            }
            
            if ($job->finished_at) {
                $this->stdout("Zakończono: " . date('Y-m-d H:i:s', $job->finished_at) . "\n");
            }

            // Pokaż parametry
            $params = json_decode($job->data, true);
            if ($params) {
                $this->stdout("Parametry:\n");
                $this->stdout("  Katalog: " . ($params['directory'] ?? 'nie określono') . "\n");
                $this->stdout("  Rekursywnie: " . (isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie') . "\n");
                $this->stdout("  Usuń oryginały: " . (isset($params['delete_originals']) && $params['delete_originals'] ? 'tak' : 'nie') . "\n");
            }

            // Pokaż wyniki
            if ($job->results) {
                $results = json_decode($job->results, true);
                if ($results) {
                    $this->stdout("Wyniki:\n");
                    $this->stdout("  Znalezionych plików: " . ($results['files_found'] ?? 0) . "\n");
                    $this->stdout("  Zaimportowanych: " . ($results['imported'] ?? 0) . "\n", Console::FG_GREEN);
                    $this->stdout("  Pominiętych: " . ($results['skipped_count'] ?? 0) . "\n", Console::FG_YELLOW);
                    $this->stdout("  Błędów: " . ($results['error_count'] ?? 0) . "\n", Console::FG_RED);
                    
                    if (!empty($results['error'])) {
                        $this->stdout("  Główny błąd: " . $results['error'] . "\n", Console::FG_RED);
                    }
                }
            }

            // Pokaż błędy
            if ($job->error_message) {
                $this->stdout("Błąd: " . $job->error_message . "\n", Console::FG_RED);
            }
        }

        return ExitCode::OK;
    }

    /**
     * Sprawdź zdjęcia w poczekalni
     */
    public function actionQueuePhotos($limit = 10)
    {
        $this->stdout("=== ZDJĘCIA W POCZEKALNI ===\n", Console::BOLD);
        
        $photos = Photo::find()
            ->where(['status' => Photo::STATUS_QUEUE])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        if (empty($photos)) {
            $this->stdout("Brak zdjęć w poczekalni\n", Console::FG_YELLOW);
        } else {
            $this->stdout("Znaleziono " . count($photos) . " zdjęć w poczekalni:\n\n");
            
            foreach ($photos as $photo) {
                $this->stdout("ID: {$photo->id} | ");
                $this->stdout("Kod: {$photo->search_code} | ");
                $this->stdout("Plik: {$photo->file_name} | ");
                $this->stdout("Utworzono: " . date('Y-m-d H:i:s', $photo->created_at) . "\n");
            }
        }

        return ExitCode::OK;
    }

    private function getStatusName($status)
    {
        $statusMap = [
            0 => 'Oczekujące',
            1 => 'Przetwarzane',
            2 => 'Zakończone',
            3 => 'Błąd',
        ];

        return $statusMap[$status] ?? 'Nieznany';
    }
}