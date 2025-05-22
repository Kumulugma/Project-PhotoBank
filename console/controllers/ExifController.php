<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\Photo;
use yii\helpers\Console;

/**
 * EXIF management commands
 */
class ExifController extends Controller
{
    /**
     * Extract EXIF data from existing photos
     * 
     * @param int $limit Maximum number of photos to process (0 = all)
     * @param bool $force Force re-extraction even if EXIF data already exists
     * @return int
     */
    public function actionExtract($limit = 0, $force = false)
    {
        $this->stdout("Starting EXIF extraction for existing photos...\n", Console::FG_GREEN);
        
        // Check if EXIF extension is available
        if (!function_exists('exif_read_data')) {
            $this->stderr("Error: EXIF extension is not installed!\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }
        
        // Build query
        $query = Photo::find()
            ->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]]);
            
        if (!$force) {
            $query->andWhere(['or', ['exif_data' => null], ['exif_data' => '']]);
        }
        
        if ($limit > 0) {
            $query->limit($limit);
        }
        
        $photos = $query->all();
        $totalPhotos = count($photos);
        
        if ($totalPhotos === 0) {
            $this->stdout("No photos found for EXIF extraction.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }
        
        $this->stdout("Found {$totalPhotos} photos to process.\n\n", Console::FG_CYAN);
        
        $processed = 0;
        $successful = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($photos as $photo) {
            $processed++;
            
            $this->stdout("[{$processed}/{$totalPhotos}] Processing photo ID {$photo->id} ({$photo->file_name})... ");
            
            // Check if file exists
            $filePath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
            if (!file_exists($filePath)) {
                $this->stdout("SKIP - File not found\n", Console::FG_YELLOW);
                $skipped++;
                continue;
            }
            
            // Extract EXIF
            if ($photo->extractAndSaveExif()) {
                $this->stdout("OK\n", Console::FG_GREEN);
                $successful++;
                
                // Show copyright info if found
                $copyrightInfo = $photo->getCopyrightInfo();
                if (!empty($copyrightInfo)) {
                    $this->stdout("    → Copyright info found: ");
                    if (isset($copyrightInfo['copyright'])) {
                        $this->stdout("© " . $copyrightInfo['copyright'] . " ");
                    }
                    if (isset($copyrightInfo['artist'])) {
                        $this->stdout("by " . $copyrightInfo['artist']);
                    }
                    $this->stdout("\n", Console::FG_PURPLE);
                }
            } else {
                $this->stdout("FAIL\n", Console::FG_RED);
                $failed++;
            }
            
            // Add small delay to avoid overwhelming the system
            usleep(100000); // 0.1 second
        }
        
        // Summary
        $this->stdout("\n" . str_repeat("=", 50) . "\n", Console::FG_CYAN);
        $this->stdout("EXIF Extraction Summary:\n", Console::FG_CYAN);
        $this->stdout("Total processed: {$processed}\n");
        $this->stdout("Successful: {$successful}\n", Console::FG_GREEN);
        $this->stdout("Failed: {$failed}\n", Console::FG_RED);
        $this->stdout("Skipped: {$skipped}\n", Console::FG_YELLOW);
        
        return ExitCode::OK;
    }
    
    /**
     * Show photos with copyright information
     * 
     * @param int $limit Maximum number of photos to show (0 = all)
     * @return int
     */
    public function actionShowCopyright($limit = 20)
    {
        $this->stdout("Photos with copyright information:\n\n", Console::FG_GREEN);
        
        $photos = Photo::find()
            ->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]])
            ->andWhere(['is not', 'exif_data', null])
            ->andWhere(['!=', 'exif_data', ''])
            ->limit($limit)
            ->all();
            
        if (empty($photos)) {
            $this->stdout("No photos with EXIF data found.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }
        
        $found = 0;
        
        foreach ($photos as $photo) {
            $copyrightInfo = $photo->getCopyrightInfo();
            
            if (!empty($copyrightInfo)) {
                $found++;
                $this->stdout("Photo ID: {$photo->id} ({$photo->file_name})\n", Console::FG_CYAN);
                $this->stdout("Title: {$photo->title}\n");
                
                if (isset($copyrightInfo['copyright'])) {
                    $this->stdout("Copyright: {$copyrightInfo['copyright']}\n", Console::FG_RED);
                }
                if (isset($copyrightInfo['artist'])) {
                    $this->stdout("Artist: {$copyrightInfo['artist']}\n", Console::FG_PURPLE);
                }
                if (isset($copyrightInfo['description'])) {
                    $this->stdout("Description: {$copyrightInfo['description']}\n");
                }
                if (isset($copyrightInfo['user_comment'])) {
                    $this->stdout("Comment: {$copyrightInfo['user_comment']}\n");
                }
                
                $this->stdout(str_repeat("-", 40) . "\n\n");
            }
        }
        
        if ($found === 0) {
            $this->stdout("No photos with copyright information found in the checked photos.\n", Console::FG_YELLOW);
        } else {
            $this->stdout("Found {$found} photos with copyright information.\n", Console::FG_GREEN);
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Clean EXIF data (remove EXIF from all photos)
     * 
     * @param bool $confirm Confirm the action
     * @return int
     */
    public function actionClean($confirm = false)
    {
        if (!$confirm) {
            $this->stdout("This will remove all EXIF data from the database.\n", Console::FG_RED);
            $this->stdout("Use --confirm=1 to proceed.\n", Console::FG_YELLOW);
            return ExitCode::USAGE;
        }
        
        $this->stdout("Cleaning EXIF data from all photos...\n", Console::FG_RED);
        
        $count = Photo::updateAll(['exif_data' => null]);
        
        $this->stdout("Cleaned EXIF data from {$count} photos.\n", Console::FG_GREEN);
        
        return ExitCode::OK;
    }
    
    /**
     * Show statistics about EXIF data
     * 
     * @return int
     */
    public function actionStats()
    {
        $this->stdout("EXIF Data Statistics:\n\n", Console::FG_GREEN);
        
        $totalPhotos = Photo::find()->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]])->count();
        $photosWithExif = Photo::find()
            ->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]])
            ->andWhere(['is not', 'exif_data', null])
            ->andWhere(['!=', 'exif_data', ''])
            ->count();
            
        $photosWithCopyright = 0;
        $photos = Photo::find()
            ->where(['status' => [Photo::STATUS_ACTIVE, Photo::STATUS_QUEUE]])
            ->andWhere(['is not', 'exif_data', null])
            ->andWhere(['!=', 'exif_data', ''])
            ->all();
            
        foreach ($photos as $photo) {
            if ($photo->hasCopyrightInfo()) {
                $photosWithCopyright++;
            }
        }
        
        $this->stdout("Total photos: {$totalPhotos}\n");
        $this->stdout("Photos with EXIF data: {$photosWithExif}\n", Console::FG_CYAN);
        $this->stdout("Photos with copyright info: {$photosWithCopyright}\n", Console::FG_RED);
        
        if ($totalPhotos > 0) {
            $exifPercentage = round(($photosWithExif / $totalPhotos) * 100, 1);
            $copyrightPercentage = round(($photosWithCopyright / $totalPhotos) * 100, 1);
            
            $this->stdout("\nPercentages:\n");
            $this->stdout("EXIF coverage: {$exifPercentage}%\n", Console::FG_CYAN);
            $this->stdout("Copyright coverage: {$copyrightPercentage}%\n", Console::FG_RED);
        }
        
        return ExitCode::OK;
    }
}