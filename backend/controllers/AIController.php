<?php

namespace backend\controllers;

use Yii;
use common\models\Photo;
use common\models\Settings;
use common\models\Tag;
use common\models\PhotoTag;
use common\models\QueuedJob;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;

/**
 * AIController handles AI integration for photo analysis.
 */
class AIController extends Controller
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
                    'analyze-photo' => ['POST'],
                    'analyze-batch' => ['POST'],
                    'apply-tags' => ['POST'],
                    'apply-description' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays AI settings page.
     * @return mixed
     */
    public function actionIndex()
    {
        // Get AI settings
        $aiSettings = [
            'provider' => $this->getSettingValue('ai.provider', ''),
            'api_key' => $this->getSettingValue('ai.api_key') ? '********' : '',
            'region' => $this->getSettingValue('ai.region', ''),
            'model' => $this->getSettingValue('ai.model', ''),
            'enabled' => (bool)$this->getSettingValue('ai.enabled', false)
        ];
        
        return $this->render('index', [
            'settings' => $aiSettings,
        ]);
    }

    /**
     * Updates AI settings.
     * @return mixed
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $provider = Yii::$app->request->post('provider');
            $apiKey = Yii::$app->request->post('api_key');
            $region = Yii::$app->request->post('region');
            $model = Yii::$app->request->post('model');
            $enabled = (bool)Yii::$app->request->post('enabled', false);
            
            // Validate provider
            if (empty($provider)) {
                Yii::$app->session->setFlash('error', 'AI provider is required.');
                return $this->redirect(['index']);
            }
            
            if (!in_array($provider, ['aws', 'google', 'openai'])) {
                Yii::$app->session->setFlash('error', 'Invalid AI provider.');
                return $this->redirect(['index']);
            }
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Update settings
                $this->updateSetting('ai.provider', $provider, 'AI provider (aws, google, openai)');
                
                // Update API key (only if not empty or masked)
                if (!empty($apiKey) && $apiKey !== '********') {
                    $this->updateSetting('ai.api_key', $apiKey, 'AI API key');
                }
                
                // Update region
                $this->updateSetting('ai.region', $region, 'AI region (for AWS)');
                
                // Update model
                $this->updateSetting('ai.model', $model, 'AI model (for OpenAI)');
                
                // Update enabled status
                $this->updateSetting('ai.enabled', $enabled ? '1' : '0', 'AI integration enabled');
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'AI settings updated successfully.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating AI settings: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Tests AI service connection.
     * @return mixed
     */
    public function actionTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Get AI settings
        $provider = $this->getSettingValue('ai.provider', '');
        $apiKey = $this->getSettingValue('ai.api_key', '');
        $region = $this->getSettingValue('ai.region', '');
        $model = $this->getSettingValue('ai.model', '');
        
        // Validate required settings
        if (empty($provider) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Missing AI settings. Please configure provider and API key.'
            ];
        }
        
        try {
            // Test connection based on provider
            if ($provider === 'aws') {
                // AWS Rekognition
                $rekognitionClient = Yii::$app->get('awsRekognition', false);
                if (!$rekognitionClient) {
                    // Create client if not configured
                    $rekognitionClient = new \Aws\Rekognition\RekognitionClient([
                        'version' => 'latest',
                        'region' => $region,
                        'credentials' => [
                            'key' => $apiKey,
                            'secret' => $this->getSettingValue('ai.api_secret', ''),
                        ]
                    ]);
                }
                
                // Test service (describe collection to check connection)
                $rekognitionClient->listCollections(['MaxResults' => 1]);
                $message = 'AWS Rekognition connection test successful.';
            } elseif ($provider === 'google') {
                // Google Vision
                $visionClient = new \Google\Cloud\Vision\VisionClient([
                    'keyFilePath' => Yii::getAlias('@common/config/google-vision-key.json')
                ]);
                
                // Test service
                $visionClient->annotate('');
                $message = 'Google Vision connection test successful.';
            } elseif ($provider === 'openai') {
                // OpenAI API
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://api.openai.com/v1/models', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey
                    ]
                ]);
                
                if ($response->getStatusCode() === 200) {
                    $message = 'OpenAI API connection test successful.';
                } else {
                    throw new \Exception('API returned status code ' . $response->getStatusCode());
                }
            }
            
            return [
                'success' => true,
                'message' => $message
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'AI service connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyzes a photo using AI service.
     * @param integer $id The photo ID
     * @return mixed
     */
    public function actionAnalyzePhoto($id)
    {
        $photo = Photo::findOne($id);
        if (!$photo) {
            throw new NotFoundHttpException('The requested photo does not exist.');
        }
        
        // Check if AI is enabled
        $aiEnabled = (bool)$this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            Yii::$app->session->setFlash('error', 'AI integration is disabled. Please enable it in the settings.');
            return $this->redirect(['photos/view', 'id' => $id]);
        }
        
        $analyzeTags = (bool)Yii::$app->request->post('analyze_tags', true);
        $analyzeDescription = (bool)Yii::$app->request->post('analyze_description', true);
        
        // Create a queued job for analysis
        $job = new QueuedJob();
        $job->type = 'analyze_photo';
        $job->params = json_encode([
            'photo_id' => $id,
            'analyze_tags' => $analyzeTags,
            'analyze_description' => $analyzeDescription
        ]);
        $job->status = QueuedJob::STATUS_PENDING;
        $job->created_at = time();
        $job->updated_at = time();
        
        if ($job->save()) {
            Yii::$app->session->setFlash('success', 'Photo analysis job queued successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error queuing photo analysis job: ' . json_encode($job->errors));
        }
        
        return $this->redirect(['photos/view', 'id' => $id]);
    }

    /**
     * Batch analyzes photos using AI service.
     * @return mixed
     */
    public function actionAnalyzeBatch()
    {
        $idsStr = Yii::$app->request->post('ids', '');
        $ids = explode(',', $idsStr);
        
        if (empty($ids)) {
            Yii::$app->session->setFlash('error', 'No photos selected.');
            return $this->redirect(['photos/index']);
        }
        
        // Check if AI is enabled
        $aiEnabled = (bool)$this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            Yii::$app->session->setFlash('error', 'AI integration is disabled. Please enable it in the settings.');
            return $this->redirect(['photos/index']);
        }
        
        $analyzeTags = (bool)Yii::$app->request->post('analyze_tags', true);
        $analyzeDescription = (bool)Yii::$app->request->post('analyze_description', true);
        
        // Create a queued job for batch analysis
        $job = new QueuedJob();
        $job->type = 'analyze_batch';
        $job->params = json_encode([
            'photo_ids' => $ids,
            'analyze_tags' => $analyzeTags,
            'analyze_description' => $analyzeDescription
        ]);
        $job->status = QueuedJob::STATUS_PENDING;
        $job->created_at = time();
        $job->updated_at = time();
        
        if ($job->save()) {
            Yii::$app->session->setFlash('success', 'Batch photo analysis job queued successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error queuing batch photo analysis job: ' . json_encode($job->errors));
        }
        
        return $this->redirect(['photos/index']);
    }

    /**
     * Applies AI-suggested tags to a photo.
     * @return mixed
     */
    public function actionApplyTags()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $photoId = Yii::$app->request->post('photo_id');
        $selectedTags = Yii::$app->request->post('selected_tags', []);
        
        if (!$photoId || empty($selectedTags)) {
            return [
                'success' => false,
                'message' => 'Missing photo ID or tags.'
            ];
        }
        
        $photo = Photo::findOne($photoId);
        if (!$photo) {
            return [
                'success' => false,
                'message' => 'Photo not found.'
            ];
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $tagsAdded = 0;
            
            foreach ($selectedTags as $tagName) {
                // Find or create tag
                $tag = Tag::findOne(['name' => $tagName]);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->name = $tagName;
                    $tag->frequency = 0;
                    $tag->created_at = time();
                    $tag->updated_at = time();
                    
                    if (!$tag->save()) {
                        throw new \Exception('Error creating tag: ' . json_encode($tag->errors));
                    }
                }
                
                // Check if relationship already exists
                $existingLink = PhotoTag::findOne(['photo_id' => $photoId, 'tag_id' => $tag->id]);
                if ($existingLink) {
                    continue; // Skip existing relationship
                }
                
                // Create new relationship
                $photoTag = new PhotoTag();
                $photoTag->photo_id = $photoId;
                $photoTag->tag_id = $tag->id;
                
                if (!$photoTag->save()) {
                    throw new \Exception('Error creating tag relationship: ' . json_encode($photoTag->errors));
                }
                
                // Update tag frequency
                $tag->frequency += 1;
                $tag->save();
                
                $tagsAdded++;
            }
            
            $transaction->commit();
            
            return [
                'success' => true,
                'message' => $tagsAdded . ' tags applied successfully.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            
            return [
                'success' => false,
                'message' => 'Error applying tags: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Applies AI-suggested description to a photo.
     * @return mixed
     */
    public function actionApplyDescription()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $photoId = Yii::$app->request->post('photo_id');
        $description = Yii::$app->request->post('description');
        
        if (!$photoId || $description === null) {
            return [
                'success' => false,
                'message' => 'Missing photo ID or description.'
            ];
        }
        
        $photo = Photo::findOne($photoId);
        if (!$photo) {
            return [
                'success' => false,
                'message' => 'Photo not found.'
            ];
        }
        
        try {
            $photo->description = $description;
            $photo->updated_at = time();
            
            if ($photo->save()) {
                return [
                    'success' => true,
                    'message' => 'Description applied successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error saving description: ' . json_encode($photo->errors)
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error applying description: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a setting value with fallback default
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return string Setting value or default
     */
    protected function getSettingValue($key, $default = null)
    {
        $setting = Settings::findOne(['key' => $key]);
        return $setting ? $setting->value : $default;
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