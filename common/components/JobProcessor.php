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

    protected function syncWithS3($params) {
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
        if (empty($params['photo_id'])) {
            throw new \Exception("Nie podano ID zdjęcia do analizy");
        }

        $photoId = $params['photo_id'];
        $analyzeTags = isset($params['analyze_tags']) ? (bool) $params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool) $params['analyze_description'] : true;

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
            case 'aws':
                $results = [
                    'tags' => ['nature', 'landscape', 'sky', 'outdoor'],
                    'description' => 'A beautiful outdoor landscape with a clear blue sky.'
                ];
                break;

            case 'google':
                $results = [
                    'tags' => ['landscape', 'sky', 'nature', 'outdoor', 'cloud'],
                    'description' => 'Outdoor landscape with blue sky and clouds.'
                ];
                break;

            case 'openai':
                $results = [
                    'tags' => ['nature', 'landscape', 'mountains', 'sky', 'outdoor', 'scenic'],
                    'description' => 'A serene landscape showing mountains against a blue sky, with natural elements creating a peaceful outdoor scene.'
                ];
                break;

            default:
                throw new \Exception("Nieobsługiwany dostawca AI: {$aiProvider}");
        }

        if ($analyzeTags && !empty($results['tags'])) {
            $this->applyTags($photo, $results['tags']);
        }

        if ($analyzeDescription && !empty($results['description'])) {
            $photo->description = $results['description'];
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
            'error_count' => 0
        ];

        Yii::info("=== ROZPOCZYNAM IMPORT ZDJĘĆ ===");
        Yii::info("Katalog: {$params['directory']}");
        Yii::info("Rekursywnie: " . (isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie'));
        Yii::info("Usuń oryginały: " . (isset($params['delete_originals']) && $params['delete_originals'] ? 'tak' : 'nie'));

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

        // Znajdź katalog - sprawdź różne możliwe ścieżki
        $directory = null;
        $possiblePaths = [
            Yii::getAlias('@webroot/' . $params['directory']),
            Yii::getAlias('@app/../' . $params['directory']),
            $params['directory'], // bezpośrednia ścieżka
            Yii::getAlias('@webroot') . '/' . $params['directory']
        ];

        foreach ($possiblePaths as $path) {
            Yii::info("Sprawdzam ścieżkę: $path");
            if (is_dir($path) && is_readable($path)) {
                $directory = $path;
                Yii::info("✓ Użyję katalogu: $directory");
                break;
            } else {
                Yii::info("✗ Katalog niedostępny: $path");
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

        $recursive = isset($params['recursive']) ? (bool) $params['recursive'] : false;

        // Upewnij się, że katalogi docelowe istnieją
        if (!PathHelper::ensureDirectoryExists('temp')) {
            throw new \Exception('Nie można utworzyć katalogu temp');
        }
        if (!PathHelper::ensureDirectoryExists('thumbnails')) {
            throw new \Exception('Nie można utworzyć katalogu thumbnails');
        }

        // Znajdź wszystkie pliki graficzne
        $options = [
            'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.JPG', '*.JPEG', '*.PNG', '*.GIF'],
            'recursive' => $recursive,
        ];

        try {
            $files = FileHelper::findFiles($directory, $options);
            $results['files_found'] = count($files);
            Yii::info("Znaleziono " . count($files) . " plików do importu");

            // Debug: pokaż pierwsze 3 pliki
            for ($i = 0; $i < min(3, count($files)); $i++) {
                Yii::info("Plik {$i}: {$files[$i]}");
            }
        } catch (\Exception $e) {
            $errorMsg = "Błąd podczas wyszukiwania plików: " . $e->getMessage();
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;
            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }
            throw new \Exception($errorMsg);
        }

        if (empty($files)) {
            $warnMsg = "Nie znaleziono plików graficznych w katalogu {$directory}";
            Yii::warning($warnMsg);
            $results['warning'] = $warnMsg;
            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }
            return true; // nie jest to błąd krytyczny
        }

        // Przetwórz każdy plik
        foreach ($files as $index => $filePath) {
            $fileInfo = [
                'index' => $index + 1,
                'source' => $filePath,
                'filename' => basename($filePath),
                'time' => date('Y-m-d H:i:s')
            ];

            Yii::info("=== PRZETWARZAM PLIK {$fileInfo['index']}/{$results['files_found']}: {$fileInfo['filename']} ===");

            try {
                // 1. Sprawdź czy plik istnieje i jest dostępny
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

                // 3. Generuj nazwę pliku docelowego
                $preserveNames = $this->getSettingValue('upload.preserve_original_names', '1');

                if ($preserveNames == '1') {
                    $originalName = pathinfo($filePath, PATHINFO_FILENAME);
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $hash = substr(Yii::$app->security->generateRandomString(12), 0, 8);
                    $fileName = $originalName . '_' . $hash . '.' . $extension;
                } else {
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileName = Yii::$app->security->generateRandomString(16) . '.' . $extension;
                }

                $destPath = PathHelper::getPhotoPath($fileName, 'temp');
                $fileInfo['new_filename'] = $fileName;
                $fileInfo['destination'] = $destPath;

                Yii::info("✓ Nazwa docelowa: {$fileName}");

                // 4. Skopiuj plik
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

                // 5. Odczytaj wymiary obrazu
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

                // 6. Utwórz rekord w bazie danych
                $photo = new Photo();
                $photo->title = pathinfo($filePath, PATHINFO_FILENAME);
                $photo->file_name = $fileName;
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

                // 7. Wyodrębnij dane EXIF (opcjonalnie)
                try {
                    $photo->extractAndSaveExif();
                    Yii::info("✓ Wyodrębniono dane EXIF");
                } catch (\Exception $e) {
                    Yii::warning("Nie udało się wyodrębnić EXIF: " . $e->getMessage());
                }

                // 8. Wygeneruj miniatury
                $thumbnailsGenerated = 0;
                $thumbnailSizes = ThumbnailSize::find()->all();
                $fileInfo['thumbnails'] = [];

                foreach ($thumbnailSizes as $size) {
                    try {
                        $thumbnailPath = PathHelper::getThumbnailPath($size->name, $fileName);
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

                // 9. Usuń plik źródłowy jeśli wymagane
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

                // 10. Oznacz jako pomyślnie zaimportowany
                $results['imported']++;
                $results['processed'][] = $fileInfo;

                Yii::info("✓ Plik {$fileInfo['filename']} zaimportowany pomyślnie jako ID {$photo->id}");

                // Aktualizuj wyniki w bazie co 5 plików
                if ($job && ($results['imported'] % 5 === 0)) {
                    $job->results = json_encode($results, JSON_PRETTY_PRINT);
                    $job->save();
                    Yii::info("Zapisano postęp: {$results['imported']} z {$results['files_found']}");
                }
            } catch (\Exception $e) {
                $errorMsg = "Błąd importu pliku {$fileInfo['filename']}: " . $e->getMessage();
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

        // Finalizuj wyniki
        $results['completed_at'] = date('Y-m-d H:i:s');
        $results['summary'] = "Zaimportowano: {$results['imported']}, pominięto: {$results['skipped_count']}, błędy: {$results['error_count']}";

        Yii::info("=== IMPORT ZAKOŃCZONY ===");
        Yii::info($results['summary']);

        if ($job) {
            $job->results = json_encode($results, JSON_PRETTY_PRINT);
            $job->save();
        }

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

}
