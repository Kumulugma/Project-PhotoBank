<?php

namespace common\components;

use Yii;
use common\models\QueuedJob;
use common\models\Photo;
use common\models\PhotoTag;
use common\models\Tag;
use common\models\ThumbnailSize;
use common\models\Settings;
use yii\helpers\FileHelper;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * JobProcessor obsługuje przetwarzanie zadań w kolejce.
 */
class JobProcessor {

    /**
     * Przetwarza zadanie z kolejki.
     *
     * @param QueuedJob $job Zadanie do przetworzenia
     * @return bool Czy przetwarzanie się powiodło
     */
    public function processJob($job) {
        if (!$job) {
            return false;
        }

        // Dekoduj parametry zadania
        $params = json_decode($job->data, true) ?: [];

        // Inicjalizuj wyniki zadania
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'job_type' => $job->type,
            'job_id' => $job->id
        ];

        // Przetwarzaj różne typy zadań
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

    /**
     * Synchronizuje zdjęcia z magazynem S3.
     *
     * @param array $params Parametry zadania
     * @return bool Czy synchronizacja się powiodła
     */
    protected function syncWithS3($params) {
        // Sprawdź czy S3 jest skonfigurowane
        if (!Yii::$app->has('s3')) {
            throw new \Exception("Komponent S3 nie jest skonfigurowany");
        }

        /** @var \common\components\S3Component $s3 */
        $s3 = Yii::$app->get('s3');
        $s3Settings = $s3->getSettings();

        // Sprawdź czy S3 jest poprawnie skonfigurowane
        if (empty($s3Settings['bucket']) || empty($s3Settings['region']) ||
                empty($s3Settings['access_key']) || empty($s3Settings['secret_key'])) {
            throw new \Exception("S3 nie jest poprawnie skonfigurowane");
        }

        // Pobierz zdjęcia do synchronizacji
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
                $filePath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);

                if (!file_exists($filePath)) {
                    Yii::warning("Plik {$filePath} nie istnieje, pomijam synchronizację zdjęcia ID {$photo->id}");
                    continue;
                }

                // Generuj ścieżkę na S3
                $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d', $photo->created_at) . '/' . $photo->file_name;

                // Wrzuć plik na S3
                $s3->putObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $s3Key,
                    'SourceFile' => $filePath,
                    'ContentType' => $photo->mime_type
                ]);

                // Aktualizuj ścieżkę S3 w modelu
                $photo->s3_path = $s3Key;
                $photo->updated_at = time();

                if (!$photo->save()) {
                    throw new \Exception("Błąd aktualizacji modelu: " . json_encode($photo->errors));
                }

                // Usuń lokalny plik jeśli potrzeba
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

    /**
     * Regeneruje miniatury zdjęć.
     *
     * @param array $params Parametry zadania
     * @return bool Czy regeneracja się powiodła
     */
    protected function regenerateThumbnails($params) {
        // Przygotuj warunki zapytania
        $query = Photo::find()->where(['!=', 'status', Photo::STATUS_DELETED]);

        // Filtruj po konkretnym zdjęciu jeśli podano
        if (isset($params['photo_id']) && $params['photo_id']) {
            $query->andWhere(['id' => $params['photo_id']]);
        }

        // Pobierz rozmiary miniatur
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

        // Pobierz zdjęcia
        $photos = $query->all();

        if (empty($photos)) {
            Yii::info("Brak zdjęć do regeneracji miniatur");
            return true;
        }

        $regeneratedCount = 0;
        $errorCount = 0;

        foreach ($photos as $photo) {
            try {
                $filePath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);

                // Jeśli brak pliku lokalnie, spróbuj pobrać z S3
                if (!file_exists($filePath) && !empty($photo->s3_path)) {
                    if (Yii::$app->has('s3')) {
                        /** @var \common\components\S3Component $s3 */
                        $s3 = Yii::$app->get('s3');
                        $s3Settings = $s3->getSettings();

                        try {
                            // Utwórz tymczasowy katalog jeśli nie istnieje
                            $tempDir = Yii::getAlias('@webroot/uploads/temp');
                            if (!is_dir($tempDir)) {
                                FileHelper::createDirectory($tempDir, 0777, true);
                            }

                            // Pobierz plik z S3
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

                // Generuj miniatury dla każdego rozmiaru
                foreach ($thumbnailSizes as $size) {
                    $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);

                    // Jeśli tryb częściowy i miniatura już istnieje, pomiń
                    if (isset($params['partial']) && $params['partial'] && file_exists($thumbnailPath)) {
                        continue;
                    }

                    // Utwórz katalog miniatur jeśli nie istnieje
                    $thumbnailDir = Yii::getAlias('@webroot/uploads/thumbnails');
                    if (!is_dir($thumbnailDir)) {
                        FileHelper::createDirectory($thumbnailDir, 0777, true);
                    }

                    // Utwórz miniaturę
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

                // Usuń tymczasowy plik pobrany z S3 jeśli był
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

    /**
     * Analizuje pojedyncze zdjęcie za pomocą AI.
     *
     * @param array $params Parametry zadania
     * @return bool Czy analiza się powiodła
     */
    protected function analyzePhoto($params) {
        // Sprawdź czy podano ID zdjęcia
        if (empty($params['photo_id'])) {
            throw new \Exception("Nie podano ID zdjęcia do analizy");
        }

        $photoId = $params['photo_id'];
        $analyzeTags = isset($params['analyze_tags']) ? (bool) $params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool) $params['analyze_description'] : true;

        // Pobierz zdjęcie
        $photo = Photo::findOne($photoId);
        if (!$photo) {
            throw new \Exception("Nie znaleziono zdjęcia o ID {$photoId}");
        }

        // Sprawdź czy AI jest włączone i skonfigurowane
        $aiEnabled = (bool) $this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            throw new \Exception("Integracja AI jest wyłączona");
        }

        $aiProvider = $this->getSettingValue('ai.provider', '');
        $apiKey = $this->getSettingValue('ai.api_key', '');

        if (empty($aiProvider) || empty($apiKey)) {
            throw new \Exception("Brak konfiguracji dostawcy AI lub klucza API");
        }

        // Pobierz ścieżkę do zdjęcia
        $filePath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);

        // Jeśli brak pliku lokalnie, spróbuj pobrać z S3
        if (!file_exists($filePath) && !empty($photo->s3_path)) {
            if (Yii::$app->has('s3')) {
                /** @var \common\components\S3Component $s3 */
                $s3 = Yii::$app->get('s3');
                $s3Settings = $s3->getSettings();

                try {
                    // Utwórz tymczasowy katalog jeśli nie istnieje
                    $tempDir = Yii::getAlias('@webroot/uploads/temp');
                    if (!is_dir($tempDir)) {
                        FileHelper::createDirectory($tempDir, 0777, true);
                    }

                    // Pobierz plik z S3
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

        // Symulacja wyników analizy AI
        $results = [];

        // W rzeczywistości tutaj byłoby wywołanie odpowiedniego API AI
        switch ($aiProvider) {
            case 'aws':
                // Tutaj byłoby wywołanie AWS Rekognition
                // Przykładowe wyniki:
                $results = [
                    'tags' => ['nature', 'landscape', 'sky', 'outdoor'],
                    'description' => 'A beautiful outdoor landscape with a clear blue sky.'
                ];
                break;

            case 'google':
                // Tutaj byłoby wywołanie Google Vision API
                // Przykładowe wyniki:
                $results = [
                    'tags' => ['landscape', 'sky', 'nature', 'outdoor', 'cloud'],
                    'description' => 'Outdoor landscape with blue sky and clouds.'
                ];
                break;

            case 'openai':
                // Tutaj byłoby wywołanie OpenAI API
                // Przykładowe wyniki:
                $results = [
                    'tags' => ['nature', 'landscape', 'mountains', 'sky', 'outdoor', 'scenic'],
                    'description' => 'A serene landscape showing mountains against a blue sky, with natural elements creating a peaceful outdoor scene.'
                ];
                break;

            default:
                throw new \Exception("Nieobsługiwany dostawca AI: {$aiProvider}");
        }

        // Przetwórz wyniki analizy
        if ($analyzeTags && !empty($results['tags'])) {
            $this->applyTags($photo, $results['tags']);
        }

        if ($analyzeDescription && !empty($results['description'])) {
            $photo->description = $results['description'];
            $photo->updated_at = time();
            $photo->save();
        }

        // Usuń tymczasowy plik pobrany z S3 jeśli był
        if (!empty($photo->s3_path) && !file_exists(Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name))) {
            @unlink($filePath);
        }

        return true;
    }

    /**
     * Analizuje wiele zdjęć za pomocą AI.
     *
     * @param array $params Parametry zadania
     * @return bool Czy analiza się powiodła
     */
    protected function analyzeBatch($params) {
        // Sprawdź czy podano ID zdjęć
        if (empty($params['photo_ids']) || !is_array($params['photo_ids'])) {
            throw new \Exception("Nie podano ID zdjęć do analizy");
        }

        $photoIds = $params['photo_ids'];
        $analyzeTags = isset($params['analyze_tags']) ? (bool) $params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool) $params['analyze_description'] : true;

        // Analizuj każde zdjęcie
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

    /**
     * Importuje zdjęcia z określonego katalogu.
     *
     * @param array $params Parametry zadania
     * @param QueuedJob $job Obiekt zadania
     * @return bool Czy import się powiódł
     */
    protected function importPhotos($params, $job = null) {
        // Inicjalizuj wyniki zadania
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'directory' => $params['directory'] ?? 'nie określono',
            'recursive' => isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie',
            'processed' => [],
            'skipped' => [],
            'errors' => []
        ];

        // Loguj start zadania
        Yii::info("Rozpoczynam import zdjęć z katalogu: {$params['directory']}, rekursywnie: " . (isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie'));

        // Sprawdź parametry
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

        $directory = Yii::getAlias('@webroot/' . $params['directory']);
        $recursive = isset($params['recursive']) ? (bool) $params['recursive'] : false;

        // Sprawdź czy katalog istnieje
        if (!is_dir($directory)) {
            $errorMsg = "Katalog {$directory} nie istnieje";
            Yii::error($errorMsg);
            $results['error'] = $errorMsg;

            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }

            throw new \Exception($errorMsg);
        }

        // Utwórz katalog tymczasowy jeśli nie istnieje
        $tempDir = Yii::getAlias('@webroot/uploads/temp');
        if (!is_dir($tempDir)) {
            try {
                FileHelper::createDirectory($tempDir, 0777, true);
                Yii::info("Utworzono katalog tymczasowy: {$tempDir}");
            } catch (\Exception $e) {
                $errorMsg = "Nie można utworzyć katalogu tymczasowego: " . $e->getMessage();
                Yii::error($errorMsg);
                $results['error'] = $errorMsg;

                if ($job) {
                    $job->results = json_encode($results, JSON_PRETTY_PRINT);
                    $job->save();
                }

                throw new \Exception($errorMsg);
            }
        }

        // Pobierz listę plików
        $options = [
            'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif'],
            'recursive' => $recursive,
        ];

        try {
            $files = FileHelper::findFiles($directory, $options);
            Yii::info("Znaleziono " . count($files) . " plików do importu");
            $results['files_found'] = count($files);

            // Log lista plików (tylko dla debugowania)
            foreach ($files as $index => $file) {
                Yii::info("Plik {$index}: {$file}");
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

        // Sprawdź czy znaleziono pliki
        if (empty($files)) {
            $warnMsg = "Nie znaleziono plików do importu w katalogu {$directory}";
            Yii::warning($warnMsg);
            $results['warning'] = $warnMsg;

            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }

            return true; // Sukces, ale bez plików
        }

        // Inicjalizuj liczniki
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Utwórz katalog miniatur jeśli nie istnieje
        $thumbnailDir = Yii::getAlias('@webroot/uploads/thumbnails');
        if (!is_dir($thumbnailDir)) {
            try {
                FileHelper::createDirectory($thumbnailDir, 0777, true);
                Yii::info("Utworzono katalog miniatur: {$thumbnailDir}");
            } catch (\Exception $e) {
                $errorMsg = "Nie można utworzyć katalogu miniatur: " . $e->getMessage();
                Yii::error($errorMsg);
                $results['error'] = $errorMsg;

                if ($job) {
                    $job->results = json_encode($results, JSON_PRETTY_PRINT);
                    $job->save();
                }

                throw new \Exception($errorMsg);
            }
        }

        // Przetwórz każdy plik
        foreach ($files as $filePath) {
            Yii::info("Przetwarzam plik: {$filePath}");
            $fileInfo = [
                'source' => $filePath,
                'filename' => basename($filePath),
                'time' => date('Y-m-d H:i:s')
            ];

            try {
                // Sprawdź czy plik jest obrazem
                $mimeType = FileHelper::getMimeType($filePath);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileInfo['mime_type'] = $mimeType;

                if (!in_array($mimeType, $allowedTypes)) {
                    Yii::warning("Pominięto plik {$filePath} - nieprawidłowy typ MIME: {$mimeType}");
                    $fileInfo['reason'] = "Nieprawidłowy typ MIME: {$mimeType}";
                    $results['skipped'][] = $fileInfo;
                    $skipped++;
                    continue;
                }

                // Generuj unikalną nazwę pliku
                $fileName = Yii::$app->security->generateRandomString(16) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
                $destPath = Yii::getAlias('@webroot/uploads/temp/' . $fileName);
                $fileInfo['new_filename'] = $fileName;
                $fileInfo['destination'] = $destPath;

                // Skopiuj plik do katalogu tymczasowego
                if (!copy($filePath, $destPath)) {
                    throw new \Exception("Nie można skopiować pliku {$filePath} do {$destPath}");
                }

                Yii::info("Plik skopiowany do: {$destPath}");

                // Odczytaj wymiary obrazu
                $image = Image::make($destPath);
                $width = $image->width();
                $height = $image->height();
                $fileInfo['width'] = $width;
                $fileInfo['height'] = $height;

                // Utwórz rekord w bazie danych
                $photo = new Photo();
                $photo->title = pathinfo($filePath, PATHINFO_FILENAME); // Tytuł to nazwa pliku
                $photo->file_name = $fileName;
                $photo->file_size = filesize($destPath);
                $photo->mime_type = $mimeType;
                $photo->width = $width;
                $photo->height = $height;
                $photo->status = Photo::STATUS_QUEUE; // W kolejce
                $photo->is_public = false;
                $photo->created_at = time();
                $photo->updated_at = time();
                $photo->created_by = 1; // ID administratora lub innego użytkownika systemowego

                if (!$photo->save()) {
                    throw new \Exception("Błąd zapisywania informacji o zdjęciu: " . json_encode($photo->errors));
                }

                $fileInfo['photo_id'] = $photo->id;
                Yii::info("Utworzono rekord zdjęcia ID: {$photo->id}");

                // Generuj miniatury
                $thumbnailSizes = ThumbnailSize::find()->all();
                $fileInfo['thumbnails'] = [];

                foreach ($thumbnailSizes as $size) {
                    $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $fileName);
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
                        'path' => $thumbnailPath
                    ];
                }

                Yii::info("Wygenerowano miniatury dla zdjęcia ID: {$photo->id}");

                $imported++;
                $results['processed'][] = $fileInfo;

                // Opcjonalnie - usuń oryginalny plik po imporcie
                if (isset($params['delete_originals']) && $params['delete_originals']) {
                    @unlink($filePath);
                    $fileInfo['original_deleted'] = true;
                    Yii::info("Usunięto oryginalny plik: {$filePath}");
                }

                // Aktualizuj wyniki zadania co 5 przetworzonych plików
                if ($job && ($imported % 5 === 0)) {
                    $results['imported'] = $imported;
                    $results['skipped'] = $skipped;
                    $results['errors'] = $errors;
                    $job->results = json_encode($results, JSON_PRETTY_PRINT);
                    $job->save();
                }
            } catch (\Exception $e) {
                $errorMsg = "Błąd importu pliku {$filePath}: " . $e->getMessage();
                Yii::error($errorMsg);
                $fileInfo['error'] = $e->getMessage();
                $results['errors'][] = $fileInfo;
                $errors++;
            }
        }

        // Podsumowanie importu
        $results['completed_at'] = date('Y-m-d H:i:s');
        $results['imported'] = $imported;
        $results['skipped'] = $skipped;
        $results['errors'] = $errors;
        $results['summary'] = "Zaimportowano: {$imported}, pominięto: {$skipped}, błędy: {$errors}";

        Yii::info("Import zakończony. {$results['summary']}");

        // Zapisz wyniki do zadania
        if ($job) {
            $job->results = json_encode($results, JSON_PRETTY_PRINT);
            $job->save();
        }

        return true;
    }

    /**
     * Dodaje znaki wodne do obrazu.
     *
     * @param \Intervention\Image\Image $image Obraz do modyfikacji
     * @return \Intervention\Image\Image Zmodyfikowany obraz
     */
    protected function addWatermark($image) {
        // Get watermark settings
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
                $watermarkPath = Yii::getAlias('@webroot/uploads/watermark/' . $watermarkImage);

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

    /**
     * Dodaje tagi do zdjęcia na podstawie wyników analizy AI.
     *
     * @param Photo $photo Zdjęcie
     * @param array $tagNames Nazwy tagów
     * @return void
     */
    protected function applyTags($photo, $tagNames) {
        if (empty($tagNames) || empty($photo)) {
            return;
        }

        foreach ($tagNames as $tagName) {
            // Szukaj istniejącego tagu lub utwórz nowy
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

            // Sprawdź czy relacja już istnieje
            $existingRelation = PhotoTag::findOne(['photo_id' => $photo->id, 'tag_id' => $tag->id]);

            if (!$existingRelation) {
                // Utwórz nową relację
                $photoTag = new PhotoTag();
                $photoTag->photo_id = $photo->id;
                $photoTag->tag_id = $tag->id;

                if ($photoTag->save()) {
                    // Zaktualizuj licznik częstotliwości tagu
                    $tag->frequency += 1;
                    $tag->updated_at = time();
                    $tag->save();
                } else {
                    Yii::warning("Nie można przypisać tagu '{$tagName}' do zdjęcia ID {$photo->id}: " . json_encode($photoTag->errors));
                }
            }
        }
    }

    /**
     * Pobiera wartość ustawienia z tabeli settings.
     *
     * @param string $key Klucz ustawienia
     * @param mixed $default Domyślna wartość
     * @return mixed Wartość ustawienia lub domyślna wartość
     */
    protected function getSettingValue($key, $default = null) {
        $setting = Settings::findOne(['key' => $key]);
        return $setting ? $setting->value : $default;
    }

}
