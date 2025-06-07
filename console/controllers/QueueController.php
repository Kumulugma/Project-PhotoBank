<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\QueuedJob;
use common\components\JobProcessor;

/**
 * Queue management console controller
 */
class QueueController extends Controller
{
    /**
     * Run pending jobs from the queue
     * 
     * @param int $limit Maximum number of jobs to process
     * @return int Exit code
     */
    public function actionRun($limit = 5)
    {
        $this->stdout("Starting queue processor (limit: $limit)...\n");
        
        // Find pending jobs
        $jobs = QueuedJob::find()
            ->where(['status' => QueuedJob::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit($limit)
            ->all();
        
        if (empty($jobs)) {
            $this->stdout("No pending jobs found.\n");
            return ExitCode::OK;
        }
        
        $processed = 0;
        $successful = 0;
        $failed = 0;
        
        $jobProcessor = new JobProcessor();
        
        foreach ($jobs as $job) {
            $this->stdout("Processing job #{$job->id} ({$job->type})... ");
            
            try {
                // Mark job as processing
                $job->status = QueuedJob::STATUS_PROCESSING;
                $job->started_at = time();
                $job->updated_at = time();
                $job->save();
                
                // Process the job
                $result = $jobProcessor->processJob($job);
                
                if ($result) {
                    $job->status = QueuedJob::STATUS_COMPLETED;
                    $job->finished_at = time();
                    $job->error_message = null;
                    $successful++;
                    $this->stdout("SUCCESS\n", \yii\helpers\Console::FG_GREEN);
                } else {
                    $job->status = QueuedJob::STATUS_FAILED;
                    $job->error_message = 'Job processing failed.';
                    $failed++;
                    $this->stdout("FAILED\n", \yii\helpers\Console::FG_RED);
                }
            } catch (\Exception $e) {
                $job->status = QueuedJob::STATUS_FAILED;
                $job->error_message = $e->getMessage();
                $failed++;
                $this->stdout("ERROR: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            }
            
            $job->updated_at = time();
            $job->save();
            $processed++;
        }
        
        $this->stdout("\nQueue processing completed:\n");
        $this->stdout("- Processed: $processed\n");
        $this->stdout("- Successful: $successful\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("- Failed: $failed\n", \yii\helpers\Console::FG_RED);
        
        return ExitCode::OK;
    }
    
    /**
     * Show queue status
     * 
     * @return int Exit code
     */
    public function actionStatus()
    {
        $this->stdout("=== QUEUE STATUS ===\n", \yii\helpers\Console::BOLD);
        
        $stats = [
            'pending' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_PENDING])->count(),
            'processing' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_PROCESSING])->count(),
            'completed' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_COMPLETED])->count(),
            'failed' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_FAILED])->count(),
        ];
        
        $this->stdout("Pending: {$stats['pending']}\n", \yii\helpers\Console::FG_YELLOW);
        $this->stdout("Processing: {$stats['processing']}\n", \yii\helpers\Console::FG_BLUE);
        $this->stdout("Completed: {$stats['completed']}\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("Failed: {$stats['failed']}\n", \yii\helpers\Console::FG_RED);
        
        return ExitCode::OK;
    }
    
    /**
     * Clear completed jobs
     * 
     * @return int Exit code
     */
    public function actionClearCompleted()
    {
        $count = QueuedJob::deleteAll(['status' => QueuedJob::STATUS_COMPLETED]);
        $this->stdout("Cleared $count completed jobs.\n", \yii\helpers\Console::FG_GREEN);
        
        return ExitCode::OK;
    }
    
    /**
     * Clear failed jobs
     * 
     * @return int Exit code
     */
    public function actionClearFailed()
    {
        $count = QueuedJob::deleteAll(['status' => QueuedJob::STATUS_FAILED]);
        $this->stdout("Cleared $count failed jobs.\n", \yii\helpers\Console::FG_GREEN);
        
        return ExitCode::OK;
    }
}