<?php

namespace backend\controllers;

use Yii;
use common\models\Settings;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * SettingsController implements the actions for Settings model.
 */
class SettingsController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Settings models.
     * @return mixed
     */
    public function actionIndex()
    {
        // Get all settings and group them by category
        $allSettings = Settings::find()->orderBy(['key' => SORT_ASC])->all();
        $settings = [];
        
        foreach ($allSettings as $setting) {
            // Parse setting key - assuming format category.name
            $parts = explode('.', $setting->key);
            
            if (count($parts) >= 2) {
                $category = $parts[0];
                $name = $parts[1];
                
                if (!isset($settings[$category])) {
                    $settings[$category] = [];
                }
                
                // Mask sensitive data
                $value = $setting->value;
                if (in_array($name, ['secret_key', 'password', 'api_key'])) {
                    $value = '********';
                }
                
                $settings[$category][$name] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $value,
                    'description' => $setting->description,
                ];
            } else {
                // For settings without category
                if (!isset($settings['general'])) {
                    $settings['general'] = [];
                }
                
                $settings['general'][$setting->key] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            }
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Updates settings.
     * @return mixed
     */
    public function actionUpdate()
    {
        $settingsData = Yii::$app->request->post('Settings', []);
        
        if (!empty($settingsData)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($settingsData as $key => $value) {
                    // Skip masked values
                    if ($value === '********') {
                        continue;
                    }
                    
                    // Find or create setting
                    $setting = Settings::findOne(['key' => $key]);
                    if (!$setting) {
                        $setting = new Settings();
                        $setting->key = $key;
                        $setting->created_at = time();
                    }
                    
                    $setting->value = $value;
                    $setting->updated_at = time();
                    
                    if (!$setting->save()) {
                        throw new \Exception('Error saving setting ' . $key . ': ' . json_encode($setting->errors));
                    }
                }
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Settings updated successfully.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
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