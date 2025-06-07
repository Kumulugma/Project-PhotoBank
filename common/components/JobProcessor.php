<?php

namespace common\components;

use Yii;
use common\models\QueuedJob;
use common\models\Photo;
use common\models\PhotoTag;
use common\models\Tag;
use common\models\ThumbnailSize;
use common\models\Settings;
use common\helpers\PathHelper;
use yii\helpers\FileHelper;
use Intervention\Image\ImageManagerStatic as Image;

class JobProcessor {

    /**
     * Dodaj do metody processJob() nowy case dla 'import_photos_batch'
     */
    public function processJob($job) {
        if (!$job) {
            return false;
        }

        $params = json_decode($job->data, true) ?: [];

        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'job_type' => $job->type,
            'job_id' => $job->id
        ];

        try {
            switch ($job->type) {
                case 's3_sync':
                    return $this->syncWithS3($params, $job);

                case 'regenerate_thumbnails':
                    return $this->regenerateThumbnails($params, $job);

                case 'analyze_photo':
                    return $this->analyzePhoto($params, $job);

                case 'analyze_batch':
                    return $this->analyzeBatch($params, $job);

                case 'import_photos':
                    return $this->importPhotos($params, $job);

                case 'import_photos_batch': // NOWY TYP ZADANIA
                    return $this->importPhotosBatch($params, $job);

                default:
                    $error = "Nieznany typ zadania: {$job->type}";
                    Yii::error($error);
                    $results['error'] = $error;
                    $job->results = json_encode($results, JSON_PRETTY_PRINT);
                    throw new \Exception($error);
            }
        } catch (\Exception $e) {
            $errorMsg = "Błąd przetwarzania zadania ID {$job->id}: " . $e->getMessage();
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;
            $results['completed_at'] = date('Y-m-d H:i:s');
            $job->results = json_encode($results, JSON_PRETTY_PRINT);
            return false;
        }
    }

    /**
     * Import photos batch - przetwarza tylko określoną partię plików
     */
    protected function importPhotosBatch($params, $job = null) {
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'directory' => $params['directory'] ?? 'nie określono',
            'batch_number' => $params['batch_number'] ?? 1,
            'total_batches' => $params['total_batches'] ?? 1,
            'files_in_batch' => count($params['files'] ?? []),
            'processed' => [],
            'skipped' => [],
            'errors' => [],
            'imported' => 0,
            'skipped_count' => 0,
            'error_count' => 0
        ];

        Yii::info("=== ROZPOCZYNAM IMPORT PARTII {$results['batch_number']}/{$results['total_batches']} ===");
        Yii::info("Pliki w partii: " . $results['files_in_batch']);

        if (empty($params['directory'])) {
            $errorMsg = "Nie podano katalogu do importu";
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;
            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }
            throw new \Exception($errorMsg);
        }

        if (empty($params['files']) || !is_array($params['files'])) {
            $errorMsg = "Nie podano plików do importu w partii";
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;
            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }
            throw new \Exception($errorMsg);
        }

        // Znajdź katalog - sprawdź różne możliwe ścieżki
        $directory = null;
        $possiblePaths = [
            Yii::getAlias('@webroot/' . $params['directory']),
            Yii::getAlias('@app/../' . $params['directory']),
            $params['directory'],
            Yii::getAlias('@webroot') . '/' . $params['directory']
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path) && is_readable($path)) {
                $directory = $path;
                Yii::info("✓ Użyję katalogu: $directory");
                break;
            }
        }

        if (!$directory) {
            $errorMsg = "Katalog nie istnieje lub nie jest dostępny. Sprawdzono: " . implode(', ', $possiblePaths);
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;
            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }
            throw new \Exception($errorMsg);
        }

        // Upewnij się, że katalogi docelowe istnieją
        if (!PathHelper::ensureDirectoryExists('temp')) {
            throw new \Exception('Nie można utworzyć katalogu temp');
        }
        if (!PathHelper::ensureDirectoryExists('thumbnails')) {
            throw new \Exception('Nie można utworzyć katalogu thumbnails');
        }

        // Przetwórz tylko pliki z tej partii
        foreach ($params['files'] as $index => $fileName) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

            $fileInfo = [
                'batch_index' => $index + 1,
                'filename' => $fileName,
                'source' => $filePath,
                'time' => date('Y-m-d H:i:s')
            ];

            Yii::info("=== PRZETWARZAM PLIK {$fileInfo['batch_index']}/{$results['files_in_batch']}: {$fileName} ===");

            try {
                // 1. Sprawdź czy plik istnieje
                if (!file_exists($filePath)) {
                    throw new \Exception("Plik nie istnieje");
                }

                if (!is_readable($filePath)) {
                    throw new \Exception("Brak uprawnień do odczytu pliku");
                }

                $fileSize = filesize($filePath);
                if ($fileSize === false || $fileSize === 0) {
                    throw new \Exception("Nie można odczytać rozmiaru pliku lub plik jest pusty");
                }

                // 2. Sprawdź typ MIME
                $mimeType = FileHelper::getMimeType($filePath);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileInfo['mime_type'] = $mimeType;
                $fileInfo['file_size'] = $fileSize;

                if (!in_array($mimeType, $allowedTypes)) {
                    Yii::warning("Pominięto plik - nieprawidłowy typ MIME: {$mimeType}");
                    $fileInfo['reason'] = "Nieprawidłowy typ MIME: {$mimeType}";
                    $results['skipped'][] = $fileInfo;
                    $results['skipped_count']++;
                    continue;
                }

                Yii::info("✓ Plik prawidłowy - MIME: {$mimeType}, rozmiar: " . Yii::$app->formatter->asShortSize($fileSize));

                /*
                // 3. Sprawdź czy już nie istnieje w bazie (po nazwie pliku)
                $existingPhoto = Photo::find()
                        ->where(['like', 'file_name', pathinfo($fileName, PATHINFO_FILENAME) . '%', false])
                        ->one();

                if ($existingPhoto) {
                    Yii::warning("Pominięto plik - już istnieje w bazie: {$fileName}");
                    $fileInfo['reason'] = "Plik już istnieje w bazie danych";
                    $results['skipped'][] = $fileInfo;
                    $results['skipped_count']++;
                    continue;
                }
                 */

                // 4. Generuj nazwę pliku docelowego
                $preserveNames = $this->getSettingValue('upload.preserve_original_names', '1');

                if ($preserveNames == '1') {
                    $originalName = pathinfo($fileName, PATHINFO_FILENAME);
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $hash = substr(Yii::$app->security->generateRandomString(12), 0, 8);
                    $newFileName = $originalName . '_' . $hash . '.' . $extension;
                } else {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $newFileName = Yii::$app->security->generateRandomString(16) . '.' . $extension;
                }

                $destPath = PathHelper::getPhotoPath($newFileName, 'temp');
                $fileInfo['new_filename'] = $newFileName;
                $fileInfo['destination'] = $destPath;

                Yii::info("✓ Nazwa docelowa: {$newFileName}");

                // 5. Skopiuj plik
                if (!copy($filePath, $destPath)) {
                    throw new \Exception("Nie można skopiować pliku do {$destPath}");
                }

                // Sprawdź czy kopia jest prawidłowa
                if (!file_exists($destPath)) {
                    throw new \Exception("Skopiowany plik nie istnieje");
                }

                $copiedSize = filesize($destPath);
                if ($copiedSize !== $fileSize) {
                    unlink($destPath);
                    throw new \Exception("Rozmiar skopiowanego pliku ({$copiedSize}) różni się od oryginału ({$fileSize})");
                }

                Yii::info("✓ Plik skopiowany pomyślnie");

                // 6. Odczytaj wymiary obrazu
                try {
                    $image = Image::make($destPath);
                    $width = $image->width();
                    $height = $image->height();
                    $fileInfo['width'] = $width;
                    $fileInfo['height'] = $height;
                    Yii::info("✓ Wymiary: {$width}x{$height}px");
                } catch (\Exception $e) {
                    unlink($destPath);
                    throw new \Exception("Nie można odczytać wymiarów obrazu: " . $e->getMessage());
                }

                // 7. Utwórz rekord w bazie danych
                $photo = new Photo();
                $photo->title = pathinfo($fileName, PATHINFO_FILENAME);
                $photo->file_name = $newFileName;
                $photo->file_size = $fileSize;
                $photo->mime_type = $mimeType;
                $photo->width = $width;
                $photo->height = $height;
                $photo->status = Photo::STATUS_QUEUE; // WAŻNE!
                $photo->is_public = 0;
                $photo->created_at = time();
                $photo->updated_at = time();
                $photo->created_by = isset($params['created_by']) ? $params['created_by'] : 1;

                if (!$photo->save()) {
                    unlink($destPath);
                    throw new \Exception("Błąd zapisywania do bazy: " . json_encode($photo->errors));
                }

                $fileInfo['photo_id'] = $photo->id;
                $fileInfo['search_code'] = $photo->search_code;
                Yii::info("✓ Utworzono rekord zdjęcia ID: {$photo->id}, kod: {$photo->search_code}");

                // 8. Wyodrębnij dane EXIF (opcjonalnie)
                try {
                    $photo->extractAndSaveExif();
                    Yii::info("✓ Wyodrębniono dane EXIF");
                } catch (\Exception $e) {
                    Yii::warning("Nie udało się wyodrębnić EXIF: " . $e->getMessage());
                }

                // 9. Wygeneruj miniatury
                $thumbnailsGenerated = 0;
                $thumbnailSizes = ThumbnailSize::find()->all();
                $fileInfo['thumbnails'] = [];

                foreach ($thumbnailSizes as $size) {
                    try {
                        $thumbnailPath = PathHelper::getThumbnailPath($size->name, $newFileName);
                        $thumbnailImage = Image::make($destPath);

                        if ($size->crop) {
                            $thumbnailImage->fit($size->width, $size->height);
                        } else {
                            $thumbnailImage->resize($size->width, $size->height, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                        }

                        if ($size->watermark) {
                            $thumbnailImage = $this->addWatermark($thumbnailImage);
                        }

                        $thumbnailImage->save($thumbnailPath);
                        $fileInfo['thumbnails'][] = [
                            'size' => $size->name,
                            'path' => $thumbnailPath,
                            'width' => $size->width,
                            'height' => $size->height
                        ];
                        $thumbnailsGenerated++;
                    } catch (\Exception $e) {
                        Yii::warning("Błąd generowania miniatury {$size->name}: " . $e->getMessage());
                        $fileInfo['thumbnails'][] = [
                            'size' => $size->name,
                            'error' => $e->getMessage()
                        ];
                    }
                }

                Yii::info("✓ Wygenerowano {$thumbnailsGenerated} miniatur");

                // 10. Usuń plik źródłowy jeśli wymagane
                if (isset($params['delete_originals']) && $params['delete_originals']) {
                    if (unlink($filePath)) {
                        Yii::info("✓ Usunięto plik źródłowy");
                        $fileInfo['original_deleted'] = true;
                    } else {
                        Yii::warning("Nie udało się usunąć pliku źródłowego");
                        $fileInfo['original_deleted'] = false;
                        $fileInfo['delete_error'] = "Nie udało się usunąć pliku";
                    }
                }

                // 11. Oznacz jako pomyślnie zaimportowany
                $results['imported']++;
                $results['processed'][] = $fileInfo;

                Yii::info("✓ Plik {$fileName} zaimportowany pomyślnie jako ID {$photo->id}");
            } catch (\Exception $e) {
                $errorMsg = "Błąd importu pliku {$fileName}: " . $e->getMessage();
                Yii::error($errorMsg);
                $fileInfo['error'] = $e->getMessage();
                $results['errors'][] = $fileInfo;
                $results['error_count']++;

                // Wyczyść pliki w przypadku błędu
                if (isset($destPath) && file_exists($destPath)) {
                    unlink($destPath);
                }
            }
        }

        // Finalizuj wyniki partii
        $results['completed_at'] = date('Y-m-d H:i:s');
        $results['summary'] = "Partia {$results['batch_number']}/{$results['total_batches']}: zaimportowano: {$results['imported']}, pominięto: {$results['skipped_count']}, błędy: {$results['error_count']}";

        Yii::info("=== PARTIA ZAKOŃCZONA ===");
        Yii::info($results['summary']);

        if ($job) {
            $job->results = json_encode($results, JSON_PRETTY_PRINT);
            $job->save();
        }

        return true;
    }

    protected function syncWithS3($params) {
        // Sprawdź limity S3
        if (!$this->checkS3Limits()) {
            throw new \Exception("Osiągnięto miesięczny limit operacji S3");
        }

        if (!Yii::$app->has('s3')) {
            throw new \Exception("Komponent S3 nie jest skonfigurowany");
        }

        /** @var \common\components\S3Component $s3 */
        $s3 = Yii::$app->get('s3');
        $s3Settings = $s3->getSettings();

        if (empty($s3Settings['bucket']) || empty($s3Settings['region']) ||
                empty($s3Settings['access_key']) || empty($s3Settings['secret_key'])) {
            throw new \Exception("S3 nie jest poprawnie skonfigurowane");
        }

        $photos = Photo::find()
                ->where(['status' => Photo::STATUS_ACTIVE])
                ->andWhere(['s3_path' => null])
                ->all();

        if (empty($photos)) {
            Yii::info("Brak zdjęć do synchronizacji z S3");
            return true;
        }

        $syncedCount = 0;
        $errorCount = 0;

        foreach ($photos as $photo) {
            try {
                $filePath = PathHelper::getPhotoPath($photo->file_name, 'temp');

                if (!file_exists($filePath)) {
                    Yii::warning("Plik {$filePath} nie istnieje, pomijam synchronizację zdjęcia ID {$photo->id}");
                    continue;
                }

                $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d', $photo->created_at) . '/' . $photo->file_name;

                $s3->putObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $s3Key,
                    'SourceFile' => $filePath,
                    'ContentType' => $photo->mime_type
                ]);

                // Zwiększ licznik S3
                $this->incrementS3Counter();

                $photo->s3_path = $s3Key;
                $photo->updated_at = time();

                if (!$photo->save()) {
                    throw new \Exception("Błąd aktualizacji modelu: " . json_encode($photo->errors));
                }

                if (isset($params['delete_local']) && $params['delete_local']) {
                    @unlink($filePath);
                }

                $syncedCount++;
            } catch (\Exception $e) {
                Yii::error("Błąd synchronizacji zdjęcia ID {$photo->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        Yii::info("Synchronizacja S3 zakończona. Zsynchronizowano: {$syncedCount}, błędy: {$errorCount}");

        return true;
    }

    protected function regenerateThumbnails($params) {
        $query = Photo::find()->where(['!=', 'status', Photo::STATUS_DELETED]);

        if (isset($params['photo_id']) && $params['photo_id']) {
            $query->andWhere(['id' => $params['photo_id']]);
        }

        $thumbnailSizes = [];
        if (isset($params['size_id']) && $params['size_id']) {
            $size = ThumbnailSize::findOne($params['size_id']);
            if ($size) {
                $thumbnailSizes[] = $size;
            } else {
                throw new \Exception("Nieprawidłowy rozmiar miniatury ID {$params['size_id']}");
            }
        } else {
            $thumbnailSizes = ThumbnailSize::find()->all();
        }

        if (empty($thumbnailSizes)) {
            throw new \Exception("Nie znaleziono żadnych rozmiarów miniatur");
        }

        $photos = $query->all();

        if (empty($photos)) {
            Yii::info("Brak zdjęć do regeneracji miniatur");
            return true;
        }

        $regeneratedCount = 0;
        $errorCount = 0;

        foreach ($photos as $photo) {
            try {
                $filePath = PathHelper::getPhotoPath($photo->file_name, 'temp');

                if (!file_exists($filePath) && !empty($photo->s3_path)) {
                    if (Yii::$app->has('s3')) {
                        /** @var \common\components\S3Component $s3 */
                        $s3 = Yii::$app->get('s3');
                        $s3Settings = $s3->getSettings();

                        try {
                            PathHelper::ensureDirectoryExists('temp');

                            $s3->getObject([
                                'Bucket' => $s3Settings['bucket'],
                                'Key' => $photo->s3_path,
                                'SaveAs' => $filePath
                            ]);
                        } catch (\Exception $e) {
                            Yii::error("Błąd pobierania pliku z S3: " . $e->getMessage());
                            continue;
                        }
                    } else {
                        Yii::warning("Brak pliku lokalnie i komponent S3 nie jest skonfigurowany");
                        continue;
                    }
                }

                if (!file_exists($filePath)) {
                    Yii::warning("Plik {$filePath} nie istnieje, pomijam regenerację miniatur dla zdjęcia ID {$photo->id}");
                    continue;
                }

                PathHelper::ensureDirectoryExists('thumbnails');

                foreach ($thumbnailSizes as $size) {
                    $thumbnailPath = PathHelper::getThumbnailPath($size->name, $photo->file_name);

                    if (isset($params['partial']) && $params['partial'] && file_exists($thumbnailPath)) {
                        continue;
                    }

                    $thumbnailImage = Image::make($filePath);

                    if ($size->crop) {
                        $thumbnailImage->fit($size->width, $size->height);
                    } else {
                        $thumbnailImage->resize($size->width, $size->height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    if ($size->watermark) {
                        $thumbnailImage = $this->addWatermark($thumbnailImage);
                    }

                    $thumbnailImage->save($thumbnailPath);
                    $regeneratedCount++;
                }

                if (!empty($photo->s3_path) && isset($params['delete_temp']) && $params['delete_temp']) {
                    @unlink($filePath);
                }
            } catch (\Exception $e) {
                Yii::error("Błąd regeneracji miniatur dla zdjęcia ID {$photo->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        Yii::info("Regeneracja miniatur zakończona. Zregenerowano: {$regeneratedCount}, błędy: {$errorCount}");

        return true;
    }

    protected function analyzePhoto($params) {
        // Sprawdź limity AI
        if (!$this->checkAiLimits()) {
            throw new \Exception("Osiągnięto miesięczny limit zapytań AI");
        }

        if (empty($params['photo_id'])) {
            throw new \Exception("Nie podano ID zdjęcia do analizy");
        }

        $photoId = $params['photo_id'];
        $analyzeTags = isset($params['analyze_tags']) ? (bool) $params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool) $params['analyze_description'] : true;
        $analyzeEnglishDescription = isset($params['analyze_english_description']) ? (bool) $params['analyze_english_description'] : true;

        $photo = Photo::findOne($photoId);
        if (!$photo) {
            throw new \Exception("Nie znaleziono zdjęcia o ID {$photoId}");
        }

        $aiEnabled = (bool) $this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            throw new \Exception("Integracja AI jest wyłączona");
        }

        $aiProvider = $this->getSettingValue('ai.provider', '');
        $apiKey = $this->getSettingValue('ai.api_key', '');

        if (empty($aiProvider) || empty($apiKey)) {
            throw new \Exception("Brak konfiguracji dostawcy AI lub klucza API");
        }

        $filePath = PathHelper::getPhotoPath($photo->file_name, 'temp');

        if (!file_exists($filePath) && !empty($photo->s3_path)) {
            if (Yii::$app->has('s3')) {
                /** @var \common\components\S3Component $s3 */
                $s3 = Yii::$app->get('s3');
                $s3Settings = $s3->getSettings();

                try {
                    PathHelper::ensureDirectoryExists('temp');

                    $s3->getObject([
                        'Bucket' => $s3Settings['bucket'],
                        'Key' => $photo->s3_path,
                        'SaveAs' => $filePath
                    ]);
                } catch (\Exception $e) {
                    throw new \Exception("Błąd pobierania pliku z S3: " . $e->getMessage());
                }
            } else {
                throw new \Exception("Brak pliku lokalnie i komponent S3 nie jest skonfigurowany");
            }
        }

        if (!file_exists($filePath)) {
            throw new \Exception("Plik {$filePath} nie istnieje");
        }

        $results = [];

        switch ($aiProvider) {
            case 'openai':
                $results = $this->analyzeWithOpenAI($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription);
                break;

            case 'anthropic':
                $results = $this->analyzeWithAnthropic($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription);
                break;

            case 'google':
                $results = $this->analyzeWithGoogle($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription);
                break;

            default:
                // Zastępcza implementacja do testów
                $results = [
                    'tags' => ['nature', 'landscape', 'sky', 'outdoor'],
                    'description' => 'Piękny krajobraz na świeżym powietrzu z błękitnym niebem.',
                    'english_description' => 'A beautiful outdoor landscape with a clear blue sky.'
                ];
        }

        // Zwiększ licznik AI
        $this->incrementAiCounter();

        if ($analyzeTags && !empty($results['tags'])) {
            $this->applyTags($photo, $results['tags']);
        }

        if ($analyzeDescription && !empty($results['description'])) {
            $photo->description = $results['description'];
        }

        if ($analyzeEnglishDescription && !empty($results['english_description'])) {
            $photo->english_description = $results['english_description'];
        }

        if (($analyzeDescription && !empty($results['description'])) ||
                ($analyzeEnglishDescription && !empty($results['english_description']))) {
            $photo->updated_at = time();
            $photo->save();
        }

        if (!empty($photo->s3_path) && !file_exists(PathHelper::getPhotoPath($photo->file_name, 'temp'))) {
            @unlink($filePath);
        }

        return true;
    }

    protected function analyzeBatch($params) {
        if (empty($params['photo_ids']) || !is_array($params['photo_ids'])) {
            throw new \Exception("Nie podano ID zdjęć do analizy");
        }

        $photoIds = $params['photo_ids'];
        $analyzeTags = isset($params['analyze_tags']) ? (bool) $params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool) $params['analyze_description'] : true;

        $successCount = 0;
        $errorCount = 0;

        foreach ($photoIds as $photoId) {
            try {
                $result = $this->analyzePhoto([
                    'photo_id' => $photoId,
                    'analyze_tags' => $analyzeTags,
                    'analyze_description' => $analyzeDescription
                ]);

                if ($result) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                Yii::error("Błąd analizy zdjęcia ID {$photoId}: " . $e->getMessage());
                $errorCount++;
            }
        }

        Yii::info("Analiza wsadowa zakończona. Przeanalizowano: {$successCount}, błędy: {$errorCount}");

        return true;
    }

    protected function importPhotos($params, $job = null) {
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'directory' => $params['directory'] ?? 'nie określono',
            'recursive' => isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie',
            'processed' => [],
            'skipped' => [],
            'errors' => [],
            'files_found' => 0,
            'imported' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'debug_info' => []  // DODAJ TO
        ];

        // DODAJ DEBUG NA POCZĄTKU
        Yii::info("=== DEBUG IMPORT START ===");
        Yii::info("PHP Memory: " . memory_get_usage(true) . " / " . memory_get_peak_usage(true));
        Yii::info("PathHelper class exists: " . (class_exists('\common\helpers\PathHelper') ? 'YES' : 'NO'));

        // Sprawdź dostępność klas
        $results['debug_info']['pathhelper_exists'] = class_exists('\common\helpers\PathHelper');
        $results['debug_info']['photo_class_exists'] = class_exists('\common\models\Photo');
        $results['debug_info']['thumbnailsize_class_exists'] = class_exists('\common\models\ThumbnailSize');

        // Test ścieżek
        try {
            $testFileName = 'test.jpg';
            $testPath = PathHelper::getPhotoPath($testFileName, 'temp');
            $results['debug_info']['test_temp_path'] = $testPath;
            $results['debug_info']['temp_dir_writable'] = is_writable(dirname($testPath));
            Yii::info("Test temp path: $testPath");
        } catch (\Exception $e) {
            $results['debug_info']['pathhelper_error'] = $e->getMessage();
            Yii::error("PathHelper error: " . $e->getMessage());
        }

        // Reszta kodu...
        // W pętli foreach ($files as $index => $filePath) DODAJ:
        foreach ($files as $index => $filePath) {
            $fileInfo = [
                'index' => $index + 1,
                'source' => $filePath,
                'filename' => basename($filePath),
                'time' => date('Y-m-d H:i:s'),
                'debug' => []  // DODAJ TO
            ];

            Yii::info("=== PRZETWARZAM PLIK {$fileInfo['index']}/{$results['files_found']}: {$fileInfo['filename']} ===");

            try {
                // DODAJ WIĘCEJ DEBUGOWANIA
                $fileInfo['debug']['file_exists'] = file_exists($filePath);
                $fileInfo['debug']['file_readable'] = is_readable($filePath);
                $fileInfo['debug']['file_size'] = filesize($filePath);

                if (!file_exists($filePath)) {
                    throw new \Exception("Plik nie istnieje");
                }

                // ... reszta logiki ...
                // PRZED UTWORZENIEM MODELU Photo DODAJ:
                Yii::info("Tworzę model Photo...");
                $photo = new Photo();

                // DODAJ DEBUGOWANIE ZAPISU
                $fileInfo['debug']['before_photo_save'] = [
                    'title' => $photo->title,
                    'file_name' => $photo->file_name,
                    'status' => $photo->status
                ];

                if (!$photo->save()) {
                    $fileInfo['debug']['photo_save_errors'] = $photo->errors;
                    unlink($destPath);
                    throw new \Exception("Błąd zapisywania do bazy: " . json_encode($photo->errors));
                }

                $fileInfo['debug']['photo_saved_successfully'] = true;
                $fileInfo['debug']['photo_id'] = $photo->id;

                // SPRAWDŹ CZY REKORD RZECZYWIŚCIE ISTNIEJE
                $savedPhoto = Photo::findOne($photo->id);
                $fileInfo['debug']['photo_exists_in_db'] = $savedPhoto !== null;
                if ($savedPhoto) {
                    $fileInfo['debug']['saved_photo_status'] = $savedPhoto->status;
                }
            } catch (\Exception $e) {
                $fileInfo['debug']['exception'] = $e->getMessage();
                $fileInfo['debug']['exception_trace'] = $e->getTraceAsString();
                // ... reszta obsługi błędów
            }

            // DODAJ fileInfo do results zawsze
            if (isset($photo) && $photo->id) {
                $results['processed'][] = $fileInfo;
                $results['imported']++;
            } else {
                $results['errors'][] = $fileInfo;
                $results['error_count']++;
            }
        }

        // DODAJ NA KOŃCU
        $results['debug_info']['final_memory'] = memory_get_usage(true);
        $results['debug_info']['db_photos_count'] = Photo::find()->count();
        $results['debug_info']['db_queue_photos_count'] = Photo::find()->where(['status' => Photo::STATUS_QUEUE])->count();

        Yii::info("=== DEBUG IMPORT END ===");
        Yii::info("Final summary: " . json_encode($results['debug_info']));

        return true;
    }

    /**
     * Dodaje znak wodny do obrazu
     * 
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    protected function addWatermark($image) {
        $watermarkType = $this->getSettingValue('watermark.type', 'text');
        $watermarkPosition = $this->getSettingValue('watermark.position', 'bottom-right');
        $watermarkOpacity = (float) $this->getSettingValue('watermark.opacity', 0.5);

        // Position mapping
        $positionMap = [
            'top-left' => 'top-left',
            'top-right' => 'top-right',
            'bottom-left' => 'bottom-left',
            'bottom-right' => 'bottom-right',
            'center' => 'center'
        ];

        $position = $positionMap[$watermarkPosition] ?? 'bottom-right';

        if ($watermarkType === 'text') {
            // Text watermark
            $watermarkText = $this->getSettingValue('watermark.text', '');

            if (!empty($watermarkText)) {
                $fontSize = min($image->width(), $image->height()) / 20; // Scale font size

                $image->text($watermarkText, $image->width() - 20, $image->height() - 20, function ($font) use ($fontSize, $watermarkOpacity) {
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity * 255]);
                    $font->align('right');
                    $font->valign('bottom');
                });
            }
        } elseif ($watermarkType === 'image') {
            // Image watermark
            $watermarkImage = $this->getSettingValue('watermark.image', '');

            if (!empty($watermarkImage)) {
                $watermarkPath = PathHelper::getUploadPath('watermark') . '/' . $watermarkImage;

                if (file_exists($watermarkPath)) {
                    $watermark = Image::make($watermarkPath);

                    // Scale watermark
                    $maxWidth = $image->width() / 4; // Max 25% of image width
                    $maxHeight = $image->height() / 4; // Max 25% of image height

                    if ($watermark->width() > $maxWidth || $watermark->height() > $maxHeight) {
                        $watermark->resize($maxWidth, $maxHeight, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    // Add opacity
                    $watermark->opacity($watermarkOpacity * 100);

                    // Insert watermark
                    $image->insert($watermark, $position);
                }
            }
        }

        return $image;
    }

    protected function applyTags($photo, $tagNames) {
        if (empty($tagNames) || empty($photo)) {
            return;
        }

        foreach ($tagNames as $tagName) {
            $tag = Tag::findOne(['name' => $tagName]);

            if (!$tag) {
                $tag = new Tag();
                $tag->name = $tagName;
                $tag->frequency = 0;
                $tag->created_at = time();
                $tag->updated_at = time();

                if (!$tag->save()) {
                    Yii::warning("Nie można utworzyć tagu '{$tagName}': " . json_encode($tag->errors));
                    continue;
                }
            }

            $existingRelation = PhotoTag::findOne(['photo_id' => $photo->id, 'tag_id' => $tag->id]);

            if (!$existingRelation) {
                $photoTag = new PhotoTag();
                $photoTag->photo_id = $photo->id;
                $photoTag->tag_id = $tag->id;

                if ($photoTag->save()) {
                    $tag->frequency += 1;
                    $tag->updated_at = time();
                    $tag->save();
                } else {
                    Yii::warning("Nie można przypisać tagu '{$tagName}' do zdjęcia ID {$photo->id}: " . json_encode($photoTag->errors));
                }
            }
        }
    }

    protected function getSettingValue($key, $default = null) {
        $setting = Settings::findOne(['key' => $key]);
        return $setting ? $setting->value : $default;
    }

    protected function analyzeWithOpenAI($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription) {
        // Implementacja OpenAI - przykładowa
        $results = [
            'tags' => $analyzeTags ? ['nature', 'landscape', 'mountains', 'sky', 'outdoor', 'scenic'] : [],
        ];

        if ($analyzeDescription) {
            $results['description'] = 'Spokojny krajobraz przedstawiający góry na tle błękitnego nieba, z naturalnymi elementami tworzącymi spokojną scenę na świeżym powietrzu.';
        }

        if ($analyzeEnglishDescription) {
            $results['english_description'] = 'A serene landscape showing mountains against a blue sky, with natural elements creating a peaceful outdoor scene.';
        }

        return $results;
    }

    protected function analyzeWithAnthropic($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription) {
        // Implementacja Anthropic - przykładowa
        $results = [
            'tags' => $analyzeTags ? ['landscape', 'sky', 'nature', 'outdoor', 'cloud'] : [],
        ];

        if ($analyzeDescription) {
            $results['description'] = 'Krajobraz na świeżym powietrzu z błękitnym niebem i chmurami.';
        }

        if ($analyzeEnglishDescription) {
            $results['english_description'] = 'Outdoor landscape with blue sky and clouds.';
        }

        return $results;
    }

    protected function analyzeWithGoogle($filePath, $analyzeTags, $analyzeDescription, $analyzeEnglishDescription) {
        // Implementacja Google - przykładowa
        $results = [
            'tags' => $analyzeTags ? ['nature', 'landscape', 'sky', 'outdoor'] : [],
        ];

        if ($analyzeDescription) {
            $results['description'] = 'Piękny krajobraz na świeżym powietrzu z czyste niebem.';
        }

        if ($analyzeEnglishDescription) {
            $results['english_description'] = 'A beautiful outdoor landscape with clear sky.';
        }

        return $results;
    }

    /**
     * Sprawdza czy nie przekroczono limitów AI
     */
    protected function checkAiLimits() {
        $this->resetCountersIfNeeded();

        $currentCount = (int) $this->getSettingValue('ai.current_count', 0);
        $monthlyLimit = (int) $this->getSettingValue('ai.monthly_limit', 1000);

        return $currentCount < $monthlyLimit;
    }

    /**
     * Sprawdza czy nie przekroczono limitów S3
     */
    protected function checkS3Limits() {
        $this->resetCountersIfNeeded();

        $currentCount = (int) $this->getSettingValue('s3.current_count', 0);
        $monthlyLimit = (int) $this->getSettingValue('s3.monthly_limit', 10000);

        return $currentCount < $monthlyLimit;
    }

    /**
     * Zwiększa licznik AI
     */
    protected function incrementAiCounter() {
        $currentCount = (int) $this->getSettingValue('ai.current_count', 0);
        Settings::setSetting('ai.current_count', $currentCount + 1);
    }

    /**
     * Zwiększa licznik S3
     */
    protected function incrementS3Counter() {
        $currentCount = (int) $this->getSettingValue('s3.current_count', 0);
        Settings::setSetting('s3.current_count', $currentCount + 1);
    }

    /**
     * Resetuje liczniki jeśli minął miesiąc
     */
    protected function resetCountersIfNeeded() {
        $currentMonth = date('Y-m-01');

        // Reset liczników AI
        $aiResetDate = $this->getSettingValue('ai.reset_date', $currentMonth);
        if ($aiResetDate !== $currentMonth) {
            Settings::setSetting('ai.current_count', '0');
            Settings::setSetting('ai.reset_date', $currentMonth);
        }

        // Reset liczników S3
        $s3ResetDate = $this->getSettingValue('s3.reset_date', $currentMonth);
        if ($s3ResetDate !== $currentMonth) {
            Settings::setSetting('s3.current_count', '0');
            Settings::setSetting('s3.reset_date', $currentMonth);
        }
    }

}
