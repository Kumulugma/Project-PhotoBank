<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\QueuedJob;
use common\models\Photo;

/**
 * Debugowanie importu zdjęć
 */
class DebugController extends Controller
{
    /**
     * Sprawdź status ostatnich zadań importu
     */
    public function actionImportJobs($limit = 5)
    {
        $this->stdout("=== OSTATNIE ZADANIA IMPORTU ===\n", \yii\helpers\Console::BOLD);
        
        $jobs = QueuedJob::find()
            ->where(['type' => 'import_photos'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        if (empty($jobs)) {
            $this->stdout("Brak zadań importu w bazie danych\n", \yii\helpers\Console::FG_YELLOW);
            return ExitCode::OK;
        }

        foreach ($jobs as $job) {
            $this->stdout("\n--- ZADANIE ID: {$job->id} ---\n", \yii\helpers\Console::BOLD);
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
                    $this->stdout("  Zaimportowanych: " . ($results['imported'] ?? 0) . "\n", \yii\helpers\Console::FG_GREEN);
                    $this->stdout("  Pominiętych: " . ($results['skipped_count'] ?? 0) . "\n", \yii\helpers\Console::FG_YELLOW);
                    $this->stdout("  Błędów: " . ($results['error_count'] ?? 0) . "\n", \yii\helpers\Console::FG_RED);
                    
                    if (!empty($results['error'])) {
                        $this->stdout("  Główny błąd: " . $results['error'] . "\n", \yii\helpers\Console::FG_RED);
                    }
                    
                    // Pokaż szczegóły błędów
                    if (!empty($results['errors'])) {
                        $this->stdout("  Szczegóły błędów:\n", \yii\helpers\Console::FG_RED);
                        foreach ($results['errors'] as $index => $error) {
                            $this->stdout("    " . ($index + 1) . ". " . ($error['filename'] ?? 'nieznany') . ": " . ($error['error'] ?? 'brak opisu') . "\n");
                        }
                    }
                    
                    // Pokaż szczegóły pominiętych
                    if (!empty($results['skipped'])) {
                        $this->stdout("  Szczegóły pominiętych:\n", \yii\helpers\Console::FG_YELLOW);
                        foreach ($results['skipped'] as $index => $skipped) {
                            $this->stdout("    " . ($index + 1) . ". " . ($skipped['filename'] ?? 'nieznany') . ": " . ($skipped['reason'] ?? 'brak powodu') . "\n");
                        }
                    }
                }
            }

            // Pokaż błędy
            if ($job->error_message) {
                $this->stdout("Błąd: " . $job->error_message . "\n", \yii\helpers\Console::FG_RED);
            }
        }

        return ExitCode::OK;
    }

    /**
     * Sprawdź zdjęcia w poczekalni
     */
    public function actionQueuePhotos($limit = 10)
    {
        $this->stdout("=== ZDJĘCIA W POCZEKALNI ===\n", \yii\helpers\Console::BOLD);
        
        $photos = Photo::find()
            ->where(['status' => Photo::STATUS_QUEUE])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        if (empty($photos)) {
            $this->stdout("Brak zdjęć w poczekalni\n", \yii\helpers\Console::FG_YELLOW);
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

    /**
     * Sprawdź pliki w katalogach
     */
    public function actionCheckFiles($directory = 'uploads/import')
    {
        $this->stdout("=== SPRAWDZENIE PLIKÓW ===\n", \yii\helpers\Console::BOLD);
        
        $possiblePaths = [
            Yii::getAlias('@webroot/' . $directory),
            Yii::getAlias('@app/../' . $directory),
            $directory,
            Yii::getAlias('@webroot') . '/' . $directory
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            $this->stdout("Sprawdzam: $path\n");
            if (is_dir($path) && is_readable($path)) {
                $foundPath = $path;
                $this->stdout("✓ Katalog dostępny\n", \yii\helpers\Console::FG_GREEN);
                break;
            } else {
                $this->stdout("✗ Katalog niedostępny\n", \yii\helpers\Console::FG_RED);
            }
        }

        if (!$foundPath) {
            $this->stdout("Nie znaleziono dostępnego katalogu!\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        // Sprawdź pliki
        try {
            $options = [
                'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.JPG', '*.JPEG', '*.PNG', '*.GIF'],
                'recursive' => true,
            ];

            $files = \yii\helpers\FileHelper::findFiles($foundPath, $options);
            $this->stdout("\nZnaleziono " . count($files) . " plików graficznych:\n", \yii\helpers\Console::BOLD);
            
            foreach ($files as $index => $file) {
                $size = filesize($file);
                $this->stdout(($index + 1) . ". " . basename($file) . " (" . Yii::$app->formatter->asShortSize($size) . ")\n");
                
                if ($index >= 9) { // Pokaż tylko pierwsze 10
                    $remaining = count($files) - 10;
                    if ($remaining > 0) {
                        $this->stdout("... i jeszcze $remaining plików\n");
                    }
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->stdout("Błąd podczas wyszukiwania plików: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        return ExitCode::OK;
    }

    /**
     * Sprawdź logi aplikacji
     */
    public function actionLogs($lines = 50)
    {
        $this->stdout("=== OSTATNIE LOGI (import, queue, photo) ===\n", \yii\helpers\Console::BOLD);
        
        $logFile = Yii::getAlias('@app/runtime/logs/app.log');
        
        if (!file_exists($logFile)) {
            $this->stdout("Plik logów nie istnieje: $logFile\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        // Odczytaj ostatnie linie i przefiltruj
        $command = "tail -n $lines '$logFile' | grep -i -E '(import|queue|photo|job)'";
        $output = shell_exec($command);
        
        if ($output) {
            $this->stdout($output);
        } else {
            $this->stdout("Brak istotnych logów w ostatnich $lines liniach\n", \yii\helpers\Console::FG_YELLOW);
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