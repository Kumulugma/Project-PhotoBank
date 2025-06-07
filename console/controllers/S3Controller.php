<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\Photo;
use common\models\QueuedJob;

/**
 * S3 management console controller
 */
class S3Controller extends Controller
{
    /**
     * Test S3 connection and configuration
     * 
     * @return int Exit code
     */
    public function actionTest()
    {
        $this->stdout("Testing S3 connection...\n");
        
        if (!Yii::$app->has('s3')) {
            $this->stdout("S3 component not configured.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::CONFIG;
        }
        
        try {
            /** @var \common\components\S3Component $s3 */
            $s3 = Yii::$app->get('s3');
            $s3Settings = $s3->getSettings();
            
            // Check configuration
            if (empty($s3Settings['bucket']) || empty($s3Settings['region']) ||
                empty($s3Settings['access_key']) || empty($s3Settings['secret_key'])) {
                $this->stdout("S3 is not properly configured.\n", \yii\helpers\Console::FG_RED);
                return ExitCode::CONFIG;
            }
            
            $this->stdout("Configuration:\n");
            $this->stdout("- Bucket: {$s3Settings['bucket']}\n");
            $this->stdout("- Region: {$s3Settings['region']}\n");
            $this->stdout("- Directory: {$s3Settings['directory']}\n");
            
            // Test connection by listing bucket
            $result = $s3->listObjects([
                'Bucket' => $s3Settings['bucket'],
                'MaxKeys' => 1
            ]);
            
            $this->stdout("S3 connection successful!\n", \yii\helpers\Console::FG_GREEN);
            
        } catch (\Exception $e) {
            $this->stdout("S3 connection failed: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Sync photos to S3
     * 
     * @param int $limit Maximum number of photos to sync
     * @return int Exit code
     */
    public function actionSync($limit = 10)
    {
        $this->stdout("Syncing photos to S3 (limit: $limit)...\n");
        
        // Create sync job
        $job = QueuedJob::createJob('s3_sync', [
            'limit' => $limit,
            'delete_local' => false
        ]);
        
        if (!$job) {
            $this->stdout("Failed to create S3 sync job.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::SOFTWARE;
        }
        
        $this->stdout("S3 sync job created (ID: {$job->id}).\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("Run 'php yii queue/run' to process the job.\n");
        
        return ExitCode::OK;
    }
    
    /**
     * Show S3 sync status
     * 
     * @return int Exit code
     */
    public function actionStatus()
    {
        $this->stdout("=== S3 SYNC STATUS ===\n", \yii\helpers\Console::BOLD);
        
        $totalPhotos = Photo::find()->where(['status' => Photo::STATUS_ACTIVE])->count();
        $syncedPhotos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['is not', 's3_path', null])
            ->count();
        $pendingSync = $totalPhotos - $syncedPhotos;
        
        $this->stdout("Total active photos: $totalPhotos\n");
        $this->stdout("Synced to S3: $syncedPhotos\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("Pending sync: $pendingSync\n", \yii\helpers\Console::FG_YELLOW);
        
        if ($pendingSync > 0) {
            $percentage = round(($syncedPhotos / $totalPhotos) * 100, 1);
            $this->stdout("Sync progress: $percentage%\n");
        }
        
        return ExitCode::OK;
    }
    
    /**
     * List photos not synced to S3
     * 
     * @param int $limit Number of photos to show
     * @return int Exit code
     */
    public function actionListPending($limit = 20)
    {
        $this->stdout("Photos pending S3 sync:\n");
        
        $photos = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['s3_path' => null])
            ->limit($limit)
            ->all();
        
        if (empty($photos)) {
            $this->stdout("No photos pending sync.\n", \yii\helpers\Console::FG_GREEN);
            return ExitCode::OK;
        }
        
        foreach ($photos as $photo) {
            $created = date('Y-m-d H:i:s', $photo->created_at);
            $size = Yii::$app->formatter->asShortSize($photo->file_size);
            $this->stdout("- ID: {$photo->id} | {$photo->file_name} | $size | $created\n");
        }
        
        $total = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE])
            ->andWhere(['s3_path' => null])
            ->count();
            
        if ($total > $limit) {
            $remaining = $total - $limit;
            $this->stdout("... and $remaining more photos\n");
        }
        
        return ExitCode::OK;
    }
}