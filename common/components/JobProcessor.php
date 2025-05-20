<?php

namespace common\components;

use Yii;
use common\models\Photo;
use common\models\QueuedJob;
use common\models\Settings;
use common\models\ThumbnailSize;
use common\models\Tag;
use common\models\PhotoTag;
use Intervention\Image\ImageManagerStatic as Image;
use yii\helpers\FileHelper;

/**
 * JobProcessor handles processing of queued jobs.
 */
class JobProcessor
{
    /**
     * Process a job based on its type.
     * 
     * @param QueuedJob $job The job to process
     * @return bool Success
     * @throws \Exception If job processing fails
     */
    public function processJob($job)
    {
        // Decode job parameters
        $params = !empty($job->params) ? json_decode($job->params, true) : [];
        
        // Process job based on type
        switch ($job->type) {
            case 's3_sync':
                return $this->processS3Sync($params);
                
            case 'regenerate_thumbnails':
                return $this->processRegenerateThumbnails($params);
                
            case 'analyze_photo':
                return $this->processAnalyzePhoto($params);
                
            case 'analyze_batch':
                return $this->processAnalyzeBatch($params);
                
            case 'import_photos':
                return $this->processImportPhotos($params);
                
            default:
                throw new \Exception("Unknown job type: {$job->type}");
        }
    }
    
    /**
     * Process S3 synchronization job.
     * 
     * @param array $params Job parameters
     * @return bool Success
     * @throws \Exception If synchronization fails
     */
    protected function processS3Sync($params)
    {
        $deleteLocal = isset($params['delete_local']) ? (bool)$params['delete_local'] : false;
        
        // Get S3 settings
        $s3Settings = [
            'bucket' => Settings::findOne(['key' => 's3.bucket'])->value ?? '',
            'region' => Settings::findOne(['key' => 's3.region'])->value ?? '',
            'access_key' => Settings::findOne(['key' => 's3.access_key'])->value ?? '',
            'secret_key' => Settings::findOne(['key' => 's3.secret_key'])->value ?? '',
            'directory' => Settings::findOne(['key' => 's3.directory'])->value ?? 'photos',
        ];
        
        // Validate required settings
        if (empty($s3Settings['bucket']) || empty($s3Settings['region']) || 
            empty($s3Settings['access_key']) || empty($s3Settings['secret_key'])) {
            throw new \Exception('Missing S3 settings. Please configure all required fields.');
        }
        
        // Create S3 client
        $s3Client = Yii::$app->get('s3');
        
        // Find photos to synchronize (active without S3 path)
        $photos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['OR', ['s3_path' => null], ['s3_path' => '']])
            ->all();
        
        $syncedCount = 0;
        
        foreach ($photos as $photo) {
            $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
            
            // Check if file exists locally
            if (!file_exists($tempPath)) {
                continue;
            }
            
            // Generate S3 path
            $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d', $photo->created_at) . '/' . $photo->file_name;
            
            try {
                // Upload file to S3
                $result = $s3Client->putObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $s3Key,
                    'SourceFile' => $tempPath,
                    'ContentType' => $photo->mime_type
                ]);
                
                // Update database record
                $photo->s3_path = $s3Key;
                $photo->updated_at = time();
                
                if ($photo->save()) {
                    $syncedCount++;
                    
                    // Delete local copy if required
                    if ($deleteLocal) {
                        unlink($tempPath);
                    }
                }
            } catch (\Exception $e) {
                Yii::error('Error synchronizing photo ID ' . $photo->id . ' with S3: ' . $e->getMessage());
                continue;
            }
        }
        
        Yii::info("S3 synchronization complete. Synced $syncedCount photos.");
        return true;
    }
    
    /**
     * Process thumbnail regeneration job.
     * 
     * @param array $params Job parameters
     * @return bool Success
     * @throws \Exception If regeneration fails
     */
    protected function processRegenerateThumbnails($params)
    {
        $photoId = isset($params['photo_id']) ? $params['photo_id'] : null;
        $sizeId = isset($params['size_id']) ? $params['size_id'] : null;
        $partial = isset($params['partial']) ? (bool)$params['partial'] : false;
        
        // Prepare query for photos to regenerate
        $query = Photo::find()->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]]);
        
        // If specific photo ID is provided, limit to it
        if ($photoId) {
            $query->andWhere(['id' => $photoId]);
        }
        
        $photos = $query->all();
        
        // Get the thumbnail sizes
        $thumbnailSizes = $sizeId 
            ? [ThumbnailSize::findOne($sizeId)] 
            : ThumbnailSize::find()->all();
        
        // Filter out null in case the size doesn't exist
        $thumbnailSizes = array_filter($thumbnailSizes);
        
        if (empty($thumbnailSizes)) {
            throw new \Exception('No thumbnail sizes defined');
        }
        
        $regeneratedCount = 0;
        
        foreach ($photos as $photo) {
            // Check if source file exists (locally or on S3)
            $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
            $sourceExists = file_exists($tempPath);
            
            // If no local copy but exists on S3, download it temporarily
            if (!$sourceExists && !empty($photo->s3_path)) {
                try {
                    // Get S3 settings
                    $s3Settings = [
                        'bucket' => Settings::findOne(['key' => 's3.bucket'])->value ?? '',
                        'region' => Settings::findOne(['key' => 's3.region'])->value ?? '',
                        'access_key' => Settings::findOne(['key' => 's3.access_key'])->value ?? '',
                        'secret_key' => Settings::findOne(['key' => 's3.secret_key'])->value ?? ''
                    ];
                    
                    // Create S3 client
                    $s3Client = Yii::$app->get('s3');
                    
                    // Download file from S3
                    $s3Client->getObject([
                        'Bucket' => $s3Settings['bucket'],
                        'Key' => $photo->s3_path,
                        'SaveAs' => $tempPath
                    ]);
                    
                    $sourceExists = true;
                } catch (\Exception $e) {
                    Yii::error('Error downloading photo from S3: ' . $e->getMessage());
                    continue;
                }
            }
            
            if (!$sourceExists) {
                continue; // Skip photo without available source
            }
            
            // Regenerate thumbnails
            foreach ($thumbnailSizes as $size) {
                $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);
                
                // Skip if partial regeneration and thumbnail already exists
                if ($partial && file_exists($thumbnailPath)) {
                    continue;
                }
                
                try {
                    $thumbnailImage = Image::make($tempPath);
                    
                    if ($size->crop) {
                        $thumbnailImage->fit($size->width, $size->height);
                    } else {
                        $thumbnailImage->resize($size->width, $size->height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }
                    
                    if ($size->watermark) {
                        // Add watermark according to settings
                        $this->addWatermark($thumbnailImage);
                    }
                    
                    // Create thumbnails directory if it doesn't exist
                    $thumbnailDir = dirname($thumbnailPath);
                    if (!is_dir($thumbnailDir)) {
                        FileHelper::createDirectory($thumbnailDir, 0777, true);
                    }
                    
                    $thumbnailImage->save($thumbnailPath);
                    $regeneratedCount++;
                } catch (\Exception $e) {
                    Yii::error('Error regenerating thumbnail: ' . $e->getMessage());
                    continue;
                }
            }
            
            // If file was temporarily downloaded from S3, delete it
            if (!empty($photo->s3_path) && file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
        
        Yii::info("Thumbnail regeneration complete. Regenerated $regeneratedCount thumbnails.");
        return true;
    }
    
    /**
     * Process photo analysis job using AI.
     * 
     * @param array $params Job parameters
     * @return bool Success
     * @throws \Exception If analysis fails
     */
    protected function processAnalyzePhoto($params)
    {
        $photoId = isset($params['photo_id']) ? $params['photo_id'] : null;
        $analyzeTags = isset($params['analyze_tags']) ? (bool)$params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool)$params['analyze_description'] : true;
        
        if (!$photoId) {
            throw new \Exception('Missing photo ID for analysis.');
        }
        
        $photo = Photo::findOne($photoId);
        if (!$photo) {
            throw new \Exception('Photo not found.');
        }
        
        // Check if AI is enabled
        $aiEnabled = (bool)Settings::findOne(['key' => 'ai.enabled'])->value ?? false;
        if (!$aiEnabled) {
            throw new \Exception('AI integration is disabled.');
        }
        
        // Get AI settings
        $aiProvider = Settings::findOne(['key' => 'ai.provider'])->value ?? '';
        $aiApiKey = Settings::findOne(['key' => 'ai.api_key'])->value ?? '';
        $aiRegion = Settings::findOne(['key' => 'ai.region'])->value ?? '';
        $aiModel = Settings::findOne(['key' => 'ai.model'])->value ?? '';
        
        if (empty($aiProvider) || empty($aiApiKey)) {
            throw new \Exception('Missing AI settings.');
        }
        
        // Get photo file
        $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
        $s3Path = $photo->s3_path;
        
        // Check if file exists locally
        $fileExists = file_exists($tempPath);
        $deleteAfterAnalysis = false;
        
        // If no local copy but exists on S3, download it temporarily
        if (!$fileExists && !empty($s3Path)) {
            try {
                // Get S3 settings
                $s3Settings = [
                    'bucket' => Settings::findOne(['key' => 's3.bucket'])->value ?? '',
                    'region' => Settings::findOne(['key' => 's3.region'])->value ?? '',
                    'access_key' => Settings::findOne(['key' => 's3.access_key'])->value ?? '',
                    'secret_key' => Settings::findOne(['key' => 's3.secret_key'])->value ?? ''
                ];
                
                // Create S3 client
                $s3Client = Yii::$app->get('s3');
                
                // Download file from S3
                $s3Client->getObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $s3Path,
                    'SaveAs' => $tempPath
                ]);
                
                $fileExists = true;
                $deleteAfterAnalysis = true; // Mark for deletion after analysis
            } catch (\Exception $e) {
                throw new \Exception('Error downloading photo from S3: ' . $e->getMessage());
            }
        }
        
        if (!$fileExists) {
            throw new \Exception('Photo file does not exist.');
        }
        
        $generatedTags = [];
        $generatedDescription = '';
        
        // Analyze photo using selected AI provider
        try {
            // Implementation for different AI providers...
            // This is a simplified implementation. In a real application, 
            // you would implement the specific API calls for each provider.
            
            if ($aiProvider === 'aws') {
                // AWS Rekognition implementation
                // ...
                
                // For demonstration purposes, generate sample results
                if ($analyzeTags) {
                    $generatedTags = [
                        ['name' => 'nature', 'confidence' => 95.5],
                        ['name' => 'landscape', 'confidence' => 92.1],
                        ['name' => 'sky', 'confidence' => 88.7],
                        ['name' => 'cloud', 'confidence' => 85.3],
                        ['name' => 'mountain', 'confidence' => 82.9],
                    ];
                }
                
                if ($analyzeDescription) {
                    $generatedDescription = 'A beautiful landscape scene with mountains and cloudy sky.';
                }
            } elseif ($aiProvider === 'google') {
                // Google Vision implementation
                // ...
                
                // For demonstration purposes, generate sample results
                if ($analyzeTags) {
                    $generatedTags = [
                        ['name' => 'landscape', 'confidence' => 94.2],
                        ['name' => 'nature', 'confidence' => 93.8],
                        ['name' => 'mountain', 'confidence' => 91.5],
                        ['name' => 'sky', 'confidence' => 90.1],
                        ['name' => 'outdoor', 'confidence' => 89.7],
                    ];
                }
                
                if ($analyzeDescription) {
                    $generatedDescription = 'An outdoor mountain landscape with clear sky and natural scenery.';
                }
            } elseif ($aiProvider === 'openai') {
                // OpenAI API implementation
                // ...
                
                // For demonstration purposes, generate sample results
                if ($analyzeTags) {
                    $generatedTags = [
                        ['name' => 'landscape', 'confidence' => 99.0],
                        ['name' => 'nature', 'confidence' => 98.0],
                        ['name' => 'mountains', 'confidence' => 97.0],
                        ['name' => 'sky', 'confidence' => 96.0],
                        ['name' => 'sunset', 'confidence' => 95.0],
                        ['name' => 'clouds', 'confidence' => 94.0],
                        ['name' => 'hiking', 'confidence' => 93.0],
                        ['name' => 'outdoors', 'confidence' => 92.0],
                    ];
                }
                
                if ($analyzeDescription) {
                    $generatedDescription = 'A breathtaking mountainous landscape captured during sunset, with dramatic clouds painting the sky in vibrant colors. The scene evokes a sense of peace and adventure, perfect for hiking enthusiasts and nature lovers.';
                }
            }
            
            // Save analysis results
            if ($analyzeDescription && !empty($generatedDescription)) {
                $photo->description = $generatedDescription;
                $photo->updated_at = time();
                $photo->save();
            }
            
            // Create suggested tags
            if ($analyzeTags && !empty($generatedTags)) {
                foreach ($generatedTags as $tagData) {
                    $tagName = $tagData['name'];
                    
                    // Find or create tag
                    $tag = Tag::findOne(['name' => $tagName]);
                    if (!$tag) {
                        $tag = new Tag();
                        $tag->name = $tagName;
                        $tag->frequency = 0;
                        $tag->created_at = time();
                        $tag->updated_at = time();
                        $tag->save();
                    }
                    
                    // Create relationship if not exists
                    $existingLink = PhotoTag::findOne(['photo_id' => $photoId, 'tag_id' => $tag->id]);
                    if (!$existingLink) {
                        $photoTag = new PhotoTag();
                        $photoTag->photo_id = $photoId;
                        $photoTag->tag_id = $tag->id;
                        
                        if ($photoTag->save()) {
                            // Update tag frequency
                            $tag->frequency += 1;
                            $tag->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Clean up if file was downloaded temporarily
            if ($deleteAfterAnalysis && file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            throw new \Exception('Error analyzing photo: ' . $e->getMessage());
        }
        
        // Clean up if file was downloaded temporarily
        if ($deleteAfterAnalysis && file_exists($tempPath)) {
            unlink($tempPath);
        }
        
        Yii::info("Photo analysis complete for photo ID $photoId.");
        return true;
    }
    
    /**
     * Process batch photo analysis job using AI.
     * 
     * @param array $params Job parameters
     * @return bool Success
     * @throws \Exception If batch analysis fails
     */
    protected function processAnalyzeBatch($params)
    {
        $photoIds = isset($params['photo_ids']) ? $params['photo_ids'] : [];
        $analyzeTags = isset($params['analyze_tags']) ? (bool)$params['analyze_tags'] : true;
        $analyzeDescription = isset($params['analyze_description']) ? (bool)$params['analyze_description'] : true;
        
        if (empty($photoIds)) {
            throw new \Exception('No photos selected for analysis.');
        }
        
        $success = true;
        $processedCount = 0;
        
        foreach ($photoIds as $photoId) {
            try {
                // Process each photo using the single photo analysis method
                $result = $this->processAnalyzePhoto([
                    'photo_id' => $photoId,
                    'analyze_tags' => $analyzeTags,
                    'analyze_description' => $analyzeDescription
                ]);
                
                if ($result) {
                    $processedCount++;
                } else {
                    $success = false;
                }
            } catch (\Exception $e) {
                Yii::error('Error analyzing photo ID ' . $photoId . ': ' . $e->getMessage());
                $success = false;
                continue;
            }
        }
        
        Yii::info("Batch photo analysis complete. Processed $processedCount photos.");
        return $success;
    }
    
    /**
     * Process photo import job.
     * 
     * @param array $params Job parameters
     * @return bool Success
     * @throws \Exception If import fails
     */
    protected function processImportPhotos($params)
    {
        $directory = isset($params['directory']) ? $params['directory'] : '';
        $recursive = isset($params['recursive']) ? (bool)$params['recursive'] : false;
        
        if (empty($directory)) {
            throw new \Exception('Import directory not specified.');
        }
        
        // Validate path
        $fullPath = Yii::getAlias('@webroot/' . $directory);
        if (!file_exists($fullPath) || !is_dir($fullPath)) {
            throw new \Exception('Invalid directory.');
        }
        
        // Get file list
        $fileOptions = [
            'only' => ['*.jpg', '*.jpeg', '*.png', '*.gif'],
            'recursive' => $recursive
        ];
        $files = FileHelper::findFiles($fullPath, $fileOptions);
        
        if (empty($files)) {
            Yii::info('No files found for import in directory: ' . $directory);
            return true;
        }
        
        $importedCount = 0;
        
        foreach ($files as $file) {
            // Check MIME type
            $mimeType = FileHelper::getMimeType($file);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($mimeType, $allowedTypes)) {
                continue; // Skip files with invalid type
            }
            
            // Generate unique filename
            $fileName = Yii::$app->security->generateRandomString(16) . '.' . pathinfo($file, PATHINFO_EXTENSION);
            $destPath = Yii::getAlias('@webroot/uploads/temp/' . $fileName);
            
            // Copy file to temporary directory
            copy($file, $destPath);
            
            // Read dimensions
            $image = Image::make($destPath);
            $width = $image->width();
            $height = $image->height();
            
            // Create database record
            $photo = new Photo();
            $photo->title = pathinfo($file, PATHINFO_FILENAME);
            $photo->file_name = $fileName;
            $photo->file_size = filesize($destPath);
            $photo->mime_type = $mimeType;
            $photo->width = $width;
            $photo->height = $height;
            $photo->status = Photo::STATUS_QUEUE;
            $photo->is_public = false;
            $photo->created_at = time();
            $photo->updated_at = time();
            $photo->created_by = 1; // Admin user ID
            
            if (!$photo->save()) {
                unlink($destPath);
                Yii::error('Error saving imported photo: ' . json_encode($photo->errors));
                continue;
            }
            
            // Generate thumbnails
            $thumbnailSizes = ThumbnailSize::find()->all();
            
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
                    // Add watermark
                    $this->addWatermark($thumbnailImage);
                }
                
                $thumbnailImage->save($thumbnailPath);
            }
            
            $importedCount++;
        }
        
        Yii::info("Photo import complete. Imported $importedCount photos.");
        return true;
    }
    
    /**
     * Adds watermark to image based on settings.
     * 
     * @param \Intervention\Image\Image $image The image
     * @return \Intervention\Image\Image The image with watermark
     */
    protected function addWatermark($image)
    {
        // Get watermark settings
        $watermarkSetting = Settings::findOne(['key' => 'watermark.type']);
        $watermarkType = $watermarkSetting ? $watermarkSetting->value : 'text';
        
        $positionSetting = Settings::findOne(['key' => 'watermark.position']);
        $watermarkPosition = $positionSetting ? $positionSetting->value : 'bottom-right';
        
        $opacitySetting = Settings::findOne(['key' => 'watermark.opacity']);
        $watermarkOpacity = $opacitySetting ? (float)$opacitySetting->value : 0.5;
        
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
            $textSetting = Settings::findOne(['key' => 'watermark.text']);
            $watermarkText = $textSetting ? $textSetting->value : '';
            
            if (!empty($watermarkText)) {
                $fontSize = min($image->width(), $image->height()) / 20; // Scale font size
                
                $image->text($watermarkText, $image->width() - 20, $image->height() - 20, function($font) use ($fontSize, $watermarkOpacity) {
                    $font->file(Yii::getAlias('@webroot/fonts/arial.ttf'));
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity * 255]);
                    $font->align('right');
                    $font->valign('bottom');
                });
            }
        } elseif ($watermarkType === 'image') {
            // Image watermark
            $imageSetting = Settings::findOne(['key' => 'watermark.image']);
            $watermarkImage = $imageSetting ? $imageSetting->value : '';
            
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
}