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
            'errors' => []
        ];

        Yii::info("Rozpoczynam import zdjęć z katalogu: {$params['directory']}, rekursywnie: " . (isset($params['recursive']) && $params['recursive'] ? 'tak' : 'nie'));

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

        // Upewnij się, że katalogi docelowe istnieją
        PathHelper::ensureDirectoryExists('temp');

        $options = [
            'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif'],
            'recursive' => $recursive,
        ];

        try {
            $files = FileHelper::findFiles($directory, $options);
            Yii::info("Znaleziono " . count($files) . " plików do importu");
            $results['files_found'] = count($files);

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

        if (empty($files)) {
            $warnMsg = "Nie znaleziono plików do importu w katalogu {$directory}";
            Yii::warning($warnMsg);
            $results['warning'] = $warnMsg;

            if ($job) {
                $job->results = json_encode($results, JSON_PRETTY_PRINT);
                $job->save();
            }

            return true;
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        PathHelper::ensureDirectoryExists('thumbnails');

        foreach ($files as $filePath) {
            Yii::info("Przetwarzam plik: {$filePath}");
            $fileInfo = [
                'source' => $filePath,
                'filename' => basename($filePath),
                'time' => date('Y-m-d H:i:s')
            ];

            try {
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

                // Generuj nazwę pliku z oryginalną nazwą i hashem
                $preserveNames = $this->getSettingValue('upload.preserve_original_names', '1');
                
                if ($preserveNames == '1') {
                    $originalName = pathinfo($filePath, PATHINFO_FILENAME);
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $hash = substr(Yii::$app->security->generateRandomString(12), 0, 8);
                    $fileName = $originalName . '_' . $hash . '.' . $extension;
                } else {
                    $fileName = Yii::$app->security->generateRandomString(16) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
                }

                $destPath = PathHelper::getPhotoPath($fileName, 'temp');
                $fileInfo['new_filename'] = $fileName;
                $fileInfo['destination'] = $destPath;

                if (!copy($filePath, $destPath)) {
                    throw new \Exception("Nie można skopiować pliku {$filePath} do {$destPath}");
                }

                Yii::info("Plik skopiowany do: {$destPath}");
                Yii::info("Plik docelowy istnieje: " . (file_exists($destPath) ? 'tak' : 'nie'));
                Yii::info("Rozmiar pliku docelowego: " . filesize($destPath) . " bajtów");

                $image = Image::make($destPath);
                $width = $image->width();
                $height = $image->height();
                $fileInfo['width'] = $width;
                $fileInfo['height'] = $height;

                $photo = new Photo();
                $photo->title = pathinfo($filePath, PATHINFO_FILENAME);
                $photo->file_name = $fileName;
                $photo->file_size = filesize($destPath);
                $photo->mime_type = $mimeType;
                $photo->width = $width;
                $photo->height = $height;
                $photo->status = Photo::STATUS_QUEUE;
                $photo->is_public = false;
                $photo->created_at = time();
                $photo->updated_at = time();
                $photo->created_by = 1;

                if (!$photo->save()) {
                    throw new \Exception("Błąd zapisywania informacji o zdjęciu: " . json_encode($photo->errors));
                }

                $fileInfo['photo_id'] = $photo->id;
                $fileInfo['search_code'] = $photo->search_code;
                Yii::info("Utworzono rekord zdjęcia ID: {$photo->id} z kodem: {$photo->search_code}");

                $thumbnailSizes = ThumbnailSize::find()->all();
                $fileInfo['thumbnails'] = [];

                foreach ($thumbnailSizes as $size) {
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
                        'path' => $thumbnailPath
                    ];
                }

                Yii::info("Wygenerowano miniatury dla zdjęcia ID: {$photo->id}");

                $imported++;
                $results['processed'][] = $fileInfo;

                if (isset($params['delete_originals']) && $params['delete_originals']) {
                    Yii::info("Próba usunięcia pliku źródłowego: {$filePath}");
                    Yii::info("Parametr delete_originals: " . var_export($params['delete_originals'], true));

                    if (file_exists($filePath)) {
                        Yii::info("Plik istnieje");
                        if (is_writable($filePath)) {
                            Yii::info("Plik ma uprawnienia do zapisu");

                            $result = unlink($filePath);
                            if ($result) {
                                Yii::info("Pomyślnie usunięto plik: {$filePath}");
                                $fileInfo['original_deleted'] = true;
                            } else {
                                Yii::error("Nie udało się usunąć pliku: {$filePath}");
                                $fileInfo['original_deleted'] = false;
                                $fileInfo['delete_error'] = "Funkcja unlink() nie powiodła się";
                            }
                        } else {
                            Yii::error("Brak uprawnień do usunięcia pliku: {$filePath}");
                            $fileInfo['original_deleted'] = false;
                            $fileInfo['delete_error'] = "Brak uprawnień do pliku";
                        }
                    } else {
                        Yii::error("Plik nie istnieje: {$filePath}");
                        $fileInfo['original_deleted'] = false;
                        $fileInfo['delete_error'] = "Plik nie istnieje";
                    }
                }

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

        $results['completed_at'] = date('Y-m-d H:i:s');
        $results['imported'] = $imported;
        $results['skipped'] = $skipped;
        $results['errors'] = $errors;
        $results['summary'] = "Zaimportowano: {$imported}, pominięto: {$skipped}, błędy: {$errors}";

        Yii::info("Import zakończony. {$results['summary']}");

        if ($job) {
            $job->results = json_encode($results, JSON_PRETTY_PRINT);
            $job->save();
        }

        return true;
    }

    protected function addWatermark($image) {
        $watermarkType = $this->getSettingValue('watermark.type', 'text');
        $watermarkPosition = $this->getSettingValue('watermark.position', 'bottom-right');
        $watermarkOpacity = (float) $this->getSettingValue('watermark.opacity', 0.5);

        $positionMap = [
            'top-left' => 'top-left',
            'top-right' => 'top-right',
            'bottom-left' => 'bottom-left',
            'bottom-right' => 'bottom-right',
            'center' => 'center'
        ];

        $position = $positionMap[$watermarkPosition] ?? 'bottom-right';

        if ($watermarkType === 'text') {
            $watermarkText = $this->getSettingValue('watermark.text', '');

            if (!empty($watermarkText)) {
                $fontSize = min($image->width(), $image->height()) / 20;

                $image->text($watermarkText, $image->width() - 20, $image->height() - 20, function ($font) use ($fontSize, $watermarkOpacity) {
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity * 255]);
                    $font->align('right');
                    $font->valign('bottom');
                });
            }
        } elseif ($watermarkType === 'image') {
            $watermarkImage = $this->getSettingValue('watermark.image', '');

            if (!empty($watermarkImage)) {
                $watermarkPath = PathHelper::getUploadPath('watermark') . '/' . $watermarkImage;

                if (file_exists($watermarkPath)) {
                    $watermark = Image::make($watermarkPath);

                    $maxWidth = $image->width() / 4;
                    $maxHeight = $image->height() / 4;

                    if ($watermark->width() > $maxWidth || $watermark->height() > $maxHeight) {
                        $watermark->resize($maxWidth, $maxHeight, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $watermark->opacity($watermarkOpacity * 100);
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