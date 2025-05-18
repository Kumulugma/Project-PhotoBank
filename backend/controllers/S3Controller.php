<?php

namespace backend\controllers;

use Yii;
use common\models\Settings;
use common\models\Photo;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * S3Controller handles AWS S3 integration.
 */
class S3Controller extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'test' => ['POST'],
                    'sync' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays S3 settings page.
     * @return mixed
     */
    public function actionIndex()
    {
        // Get S3 settings
        $s3Settings = [
            'bucket' => Settings::findOne(['key' => 's3.bucket'])->value ?? '',
            'region' => Settings::findOne(['key' => 's3.region'])->value ?? '',
            'access_key' => Settings::findOne(['key' => 's3.access_key'])->value ? '********' : '',
            'secret_key' => '********', // Always masked
            'directory' => Settings::findOne(['key' => 's3.directory'])->value ?? 'photos',
            'deleted_directory' => Settings::findOne(['key' => 's3.deleted_directory'])->value ?? 'deleted'
        ];
        
        return $this->render('index', [
            'settings' => $s3Settings,
        ]);
    }

    /**
     * Updates S3 settings.
     * @return mixed
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $bucket = Yii::$app->request->post('bucket');
            $region = Yii::$app->request->post('region');
            $accessKey = Yii::$app->request->post('access_key');
            $secretKey = Yii::$app->request->post('secret_key');
            $directory = Yii::$app->request->post('directory', 'photos');
            $deletedDirectory = Yii::$app->request->post('deleted_directory', 'deleted');
            
            // Validate required fields
            if (empty($bucket) || empty($region)) {
                Yii::$app->session->setFlash('error', 'Bucket and region are required.');
                return $this->redirect(['index']);
            }
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Update settings
                $this->updateSetting('s3.bucket', $bucket, 'S3 bucket name');
                $this->updateSetting('s3.region', $region, 'S3 region');
                
                // Update access key (only if not empty or masked)
                if (!empty($accessKey) && $accessKey !== '********') {
                    $this->updateSetting('s3.access_key', $accessKey, 'S3 access key');
                }
                
                // Update secret key (only if not empty or masked)
                if (!empty($secretKey) && $secretKey !== '********') {
                    $this->updateSetting('s3.secret_key', $secretKey, 'S3 secret key');
                }
                
                // Update directories
                $this->updateSetting('s3.directory', $directory, 'S3 photos directory');
                $this->updateSetting('s3.deleted_directory', $deletedDirectory, 'S3 deleted photos directory');
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'S3 settings updated successfully.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating S3 settings: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Tests S3 connection.
     * @return mixed
     */
    public function actionTest()
    {
        // Get S3 settings
        $bucket = Settings::findOne(['key' => 's3.bucket'])->value ?? '';
        $region = Settings::findOne(['key' => 's3.region'])->value ?? '';
        $accessKey = Settings::findOne(['key' => 's3.access_key'])->value ?? '';
        $secretKey = Settings::findOne(['key' => 's3.secret_key'])->value ?? '';
        
        // Validate required settings
        if (empty($bucket) || empty($region) || empty($accessKey) || empty($secretKey)) {
            Yii::$app->session->setFlash('error', 'Missing S3 settings. Please configure all required fields.');
            return $this->redirect(['index']);
        }
        
        try {
            // Create S3 client
            $s3Component = Yii::$app->get('s3');
            
            // Test connection by listing objects
            $result = $s3Component->listObjects([
                'Bucket' => $bucket,
                'MaxKeys' => 1
            ]);
            
            Yii::$app->session->setFlash('success', 'S3 connection test successful.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'S3 connection test failed: ' . $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Synchronizes photos with S3.
     * @return mixed
     */
    public function actionSync()
    {
        $deleteLocal = (bool)Yii::$app->request->post('delete_local', false);
        
        // Queue the synchronization task
        $task = new \common\models\QueuedJob();
        $task->type = 's3_sync';
        $task->params = json_encode(['delete_local' => $deleteLocal]);
        $task->status = \common\models\QueuedJob::STATUS_PENDING;
        $task->created_at = time();
        
        if ($task->save()) {
            Yii::$app->session->setFlash('success', 'S3 synchronization task queued successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error queuing S3 synchronization task: ' . json_encode($task->errors));
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Updates or creates a setting.
     * 
     * @param string $key Setting key
     * @param string $value Setting value
     * @param string $description Optional description
     * @return bool Success
     */
    protected function updateSetting($key, $value, $description = null)
    {
        $setting = Settings::findOne(['key' => $key]);
        
        if ($setting) {
            // Update existing setting
            $setting->value = $value;
            $setting->updated_at = time();
            
            if ($description !== null) {
                $setting->description = $description;
            }
        } else {
            // Create new setting
            $setting = new Settings();
            $setting->key = $key;
            $setting->value = $value;
            $setting->description = $description;
            $setting->created_at = time();
            $setting->updated_at = time();
        }
        
        return $setting->save();
    }
}