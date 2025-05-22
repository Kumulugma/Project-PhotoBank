<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\QueuedJob;
use common\components\JobProcessor;

/**
 * Kontroler do testowania importu zdjęć z linii komend
 */
class ImportController extends Controller
{
    /**
     * Testuje import zdjęć z podanego katalogu
     * 
     * @param string $directory Katalog do importu
     * @param bool $recursive Czy szukać rekursywnie
     * @param bool $deleteOriginals Czy usuwać oryginalne pliki
     * @return int Exit code
     */
    public function actionTest($directory = 'uploads/import', $recursive = true, $deleteOriginals = false)
    {
        $this->stdout("=== TEST IMPORTU ZDJĘĆ ===\n", \yii\helpers\Console::BOLD);
        $this->stdout("Katalog: $directory\n");
        $this->stdout("Rekursywnie: " . ($recursive ? 'tak' : 'nie') . "\n");
        $this->stdout("Usuń oryginały: " . ($deleteOriginals ? 'tak' : 'nie') . "\n\n");

        // Sprawdź możliwe ścieżki
        $possiblePaths = [
            Yii::getAlias('@webroot/' . $directory),
            Yii::getAlias('@app/../' . $directory),
            $directory,
            Yii::getAlias('@webroot') . '/' . $directory
        ];

        $this->stdout("Sprawdzam ścieżki:\n", \yii\helpers\Console::BOLD);
        $foundPath = null;
        foreach ($possiblePaths as $path) {
            $exists = is_dir($path);
            $this->stdout("  $path: ");
            if ($exists) {
                $this->stdout("ISTNIEJE\n", \yii\helpers\Console::FG_GREEN);
                if (!$foundPath) {
                    $foundPath = $path;
                }
            } else {
                $this->stdout("NIE ISTNIEJE\n", \yii\helpers\Console::FG_RED);
            }
        }

        if (!$foundPath) {
            $this->stdout("\nBŁĄD: Nie znaleziono katalogu!\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $this->stdout("\nUżywana ścieżka: $foundPath\n", \yii\helpers\Console::FG_GREEN);

        // Sprawdź pliki
        $this->stdout("\nSprawdzam pliki w katalogu...\n", \yii\helpers\Console::BOLD);
        
        try {
            $options = [
                'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.JPG', '*.JPEG', '*.PNG', '*.GIF'],
                'recursive' => (bool)$recursive,
            ];

            $files = \yii\helpers\FileHelper::findFiles($foundPath, $options);
            $this->stdout("Znaleziono " . count($files) . " plików do importu\n");

            if (count($files) > 0) {
                $this->stdout("\nPierwsze 5 plików:\n");
                for ($i = 0; $i < min(5, count($files)); $i++) {
                    $this->stdout("  " . ($i+1) . ". " . basename($files[$i]) . "\n");
                }
            }

        } catch (\Exception $e) {
            $this->stdout("BŁĄD podczas wyszukiwania plików: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        if (empty($files)) {
            $this->stdout("\nNie znaleziono plików do importu!\n", \yii\helpers\Console::FG_YELLOW);
            return ExitCode::OK;
        }

        // Zapytaj o kontynuację
        if (!$this->confirm("\nCzy kontynuować import?")) {
            $this->stdout("Import anulowany\n");
            return ExitCode::OK;
        }

        // Utwórz zadanie
        $job = new QueuedJob();
        $job->type = 'import_photos';
        $job->data = json_encode([
            'directory' => $directory,
            'recursive' => (bool)$recursive,
            'delete_originals' => (bool)$deleteOriginals,
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $job->status = QueuedJob::STATUS_PENDING;
        $job->created_at = time();
        $job->updated_at = time();

        if (!$job->save()) {
            $this->stdout("BŁĄD: Nie udało się utworzyć zadania: " . json_encode($job->errors) . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        $this->stdout("Utworzono zadanie ID: " . $job->id . "\n", \yii\helpers\Console::FG_GREEN);

        // Uruchom zadanie
        $this->stdout("\nUruchamiam zadanie...\n", \yii\helpers\Console::BOLD);
        
        try {
            $jobProcessor = new JobProcessor();
            $job->markAsStarted();

            if ($jobProcessor->processJob($job)) {
                $job->markAsFinished();
                
                $results = json_decode($job->results, true);
                $this->stdout("\n=== WYNIKI IMPORTU ===\n", \yii\helpers\Console::BOLD);
                $this->stdout("Zaimportowano: " . ($results['imported'] ?? 0) . "\n", \yii\helpers\Console::FG_GREEN);
                $this->stdout("Pominięto: " . ($results['skipped_count'] ?? 0) . "\n", \yii\helpers\Console::FG_YELLOW);
                $this->stdout("Błędy: " . ($results['error_count'] ?? 0) . "\n", \yii\helpers\Console::FG_RED);
                
                if (!empty($results['errors'])) {
                    $this->stdout("\nBłędy:\n", \yii\helpers\Console::FG_RED);
                    foreach ($results['errors'] as $error) {
                        $this->stdout("  - " . $error['filename'] . ": " . $error['error'] . "\n");
                    }
                }
                
                $this->stdout("\nImport zakończony pomyślnie!\n", \yii\helpers\Console::FG_GREEN);
            } else {
                $job->markAsFailed('Błąd podczas przetwarzania');
                $this->stdout("BŁĄD: Import nie powiódł się\n", \yii\helpers\Console::FG_RED);
                return ExitCode::SOFTWARE;
            }

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            $this->stdout("BŁĄD podczas importu: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        return ExitCode::OK;
    }

    /**
     * Wyświetla informacje o systemie plików
     */
    public function actionInfo()
    {
        $this->stdout("=== INFORMACJE O SYSTEMIE PLIKÓW ===\n", \yii\helpers\Console::BOLD);
        
        $paths = [
            'webroot' => Yii::getAlias('@webroot'),
            'app' => Yii::getAlias('@app'),
            'uploads/temp' => Yii::getAlias('@webroot/uploads/temp'),
            'uploads/thumbnails' => Yii::getAlias('@webroot/uploads/thumbnails'),
            'uploads/import' => Yii::getAlias('@webroot/uploads/import'),
        ];

        foreach ($paths as $name => $path) {
            $this->stdout("\n$name:\n");
            $this->stdout("  Ścieżka: $path\n");
            $this->stdout("  Istnieje: " . (is_dir($path) ? 'TAK' : 'NIE') . "\n");
            if (is_dir($path)) {
                $this->stdout("  Odczyt: " . (is_readable($path) ? 'TAK' : 'NIE') . "\n");
                $this->stdout("  Zapis: " . (is_writable($path) ? 'TAK' : 'NIE') . "\n");
                
                try {
                    $files = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
                    $count = iterator_count($files);
                    $this->stdout("  Plików: $count\n");
                } catch (\Exception $e) {
                    $this->stdout("  Plików: BŁĄD - " . $e->getMessage() . "\n");
                }
            }
        }
    }
}