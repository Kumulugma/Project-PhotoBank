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
class AiController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
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
    public function actionIndex() {
        $aiSettings = [
            'provider' => $this->getSettingValue('ai.provider', ''),
            'api_key' => $this->getSettingValue('ai.api_key') ? '********' : '',
            'region' => $this->getSettingValue('ai.region', ''),
            'model' => $this->getSettingValue('ai.model', ''),
            'enabled' => (bool) $this->getSettingValue('ai.enabled', false),
            'openai_model' => $this->getSettingValue('ai.openai_model', 'gpt-4-vision-preview'),
            'anthropic_model' => $this->getSettingValue('ai.anthropic_model', 'claude-3-sonnet-20240229'),
            'google_model' => $this->getSettingValue('ai.google_model', 'gemini-pro-vision')
        ];

        $providers = [
            'openai' => [
                'name' => 'OpenAI GPT-4 Vision',
                'description' => 'Zaawansowana analiza obrazów z użyciem GPT-4'
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude 3',
                'description' => 'Precyzyjna analiza wizualna Claude 3'
            ],
            'google' => [
                'name' => 'Google Gemini Vision',
                'description' => 'Wielojęzyczna analiza obrazów Gemini'
            ]
        ];

        return $this->render('index', [
                    'settings' => $aiSettings,
                    'providers' => $providers,
        ]);
    }

    /**
     * Updates AI settings.
     * @return mixed
     */
    public function actionUpdate() {
        if (Yii::$app->request->isPost) {
            $provider = Yii::$app->request->post('provider');
            $apiKey = Yii::$app->request->post('api_key');
            $region = Yii::$app->request->post('region');
            $model = Yii::$app->request->post('model');
            $enabled = (bool) Yii::$app->request->post('enabled', false);
            $aiMonthlyLimit = (int) Yii::$app->request->post('ai_monthly_limit', 1000);
            $generateEnglish = (bool) Yii::$app->request->post('generate_english_descriptions', false);

            $openaiModel = Yii::$app->request->post('openai_model');
            $anthropicModel = Yii::$app->request->post('anthropic_model');
            $googleModel = Yii::$app->request->post('google_model');

            if (empty($provider)) {
                Yii::$app->session->setFlash('error', 'AI provider is required.');
                return $this->redirect(['index']);
            }

            if (!in_array($provider, ['openai', 'anthropic', 'google'])) {
                Yii::$app->session->setFlash('error', 'Invalid AI provider.');
                return $this->redirect(['index']);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->updateSetting('ai.provider', $provider, 'AI provider (openai, anthropic, google)');

                if (!empty($apiKey) && $apiKey !== '********') {
                    $this->updateSetting('ai.api_key', $apiKey, 'AI API key');
                }

                $this->updateSetting('ai.region', $region, 'AI region (for AWS)');
                $this->updateSetting('ai.model', $model, 'AI model (legacy)');
                $this->updateSetting('ai.enabled', $enabled ? '1' : '0', 'AI integration enabled');
                $this->updateSetting('ai.monthly_limit', (string)$aiMonthlyLimit, 'Miesięczny limit zapytań AI');
                $this->updateSetting('ai.generate_english_descriptions', $generateEnglish ? '1' : '0', 'Czy generować opisy w języku angielskim przez AI');

                if ($openaiModel) {
                    $this->updateSetting('ai.openai_model', $openaiModel, 'OpenAI model');
                }
                if ($anthropicModel) {
                    $this->updateSetting('ai.anthropic_model', $anthropicModel, 'Anthropic model');
                }
                if ($googleModel) {
                    $this->updateSetting('ai.google_model', $googleModel, 'Google model');
                }

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
     * Updates AI counters
     */
    public function actionUpdateCounters()
    {
        if (Yii::$app->request->isPost) {
            $resetAiCounter = (bool) Yii::$app->request->post('reset_ai_counter', false);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Reset licznika AI jeśli wymagane
                if ($resetAiCounter) {
                    $this->updateSetting('ai.current_count', '0', 'Bieżąca liczba wykorzystanych zapytań AI w tym miesiącu');
                    AuditLog::logSystemEvent('Ręczny reset licznika AI', AuditLog::SEVERITY_INFO, AuditLog::ACTION_SETTINGS);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Licznik AI został zaktualizowany.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Błąd aktualizacji licznika AI: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }
    
    /**
     * Tests AI service connection.
     * @return mixed
     */
    public function actionTest() {
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
    public function actionAnalyzePhoto($id) {
        $photo = Photo::findOne($id);
        if (!$photo) {
            throw new NotFoundHttpException('The requested photo does not exist.');
        }

        // Check if AI is enabled
        $aiEnabled = (bool) $this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            Yii::$app->session->setFlash('error', 'AI integration is disabled. Please enable it in the settings.');
            return $this->redirect(['photos/view', 'id' => $id]);
        }

        $analyzeTags = (bool) Yii::$app->request->post('analyze_tags', true);
        $analyzeDescription = (bool) Yii::$app->request->post('analyze_description', true);
        $analyzeEnglishDescription = (bool) Yii::$app->request->post('analyze_english_description', true);

        // Create a queued job for analysis
        $job = new QueuedJob();
        $job->type = 'analyze_photo';
        $job->params = json_encode([
            'photo_id' => $id,
            'analyze_tags' => $analyzeTags,
            'analyze_description' => $analyzeDescription,
            'analyze_english_description' => $analyzeEnglishDescription
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
    public function actionAnalyzeBatch() {
        $idsStr = Yii::$app->request->post('ids', '');
        $ids = explode(',', $idsStr);

        if (empty($ids)) {
            Yii::$app->session->setFlash('error', 'No photos selected.');
            return $this->redirect(['photos/index']);
        }

        // Check if AI is enabled
        $aiEnabled = (bool) $this->getSettingValue('ai.enabled', false);
        if (!$aiEnabled) {
            Yii::$app->session->setFlash('error', 'AI integration is disabled. Please enable it in the settings.');
            return $this->redirect(['photos/index']);
        }

        $analyzeTags = (bool) Yii::$app->request->post('analyze_tags', true);
        $analyzeDescription = (bool) Yii::$app->request->post('analyze_description', true);

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
    public function actionApplyTags() {
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
    public function actionApplyDescription() {
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
    protected function getSettingValue($key, $default = null) {
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
    protected function updateSetting($key, $value, $description = null) {
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
