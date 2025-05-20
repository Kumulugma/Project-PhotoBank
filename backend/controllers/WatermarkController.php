<?php

namespace backend\controllers;

use Yii;
use common\models\Settings;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * WatermarkController handles watermark settings.
 */
class WatermarkController extends Controller
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
                    'update' => ['POST'],
                    'preview' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays watermark settings page.
     * @return mixed
     */
    public function actionIndex()
    {
        // Get watermark settings
        $watermarkSettings = [
            'type' => $this->getSettingValue('watermark.type', 'text'),
            'text' => $this->getSettingValue('watermark.text', ''),
            'image' => $this->getSettingValue('watermark.image', ''),
            'position' => $this->getSettingValue('watermark.position', 'bottom-right'),
            'opacity' => (float)$this->getSettingValue('watermark.opacity', 0.5),
        ];
        
        // Get image URL if exists
        if (!empty($watermarkSettings['image'])) {
            $watermarkSettings['image_url'] = Yii::getAlias('@web/uploads/watermark/' . $watermarkSettings['image']);
        } else {
            $watermarkSettings['image_url'] = '';
        }
        
        return $this->render('index', [
            'settings' => $watermarkSettings,
        ]);
    }

    /**
     * Updates watermark settings.
     * @return mixed
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $type = Yii::$app->request->post('type', 'text');
            $text = Yii::$app->request->post('text', '');
            $position = Yii::$app->request->post('position', 'bottom-right');
            $opacity = (float)Yii::$app->request->post('opacity', 0.5);
            
            // Validate type
            if (!in_array($type, ['text', 'image'])) {
                Yii::$app->session->setFlash('error', 'Invalid watermark type.');
                return $this->redirect(['index']);
            }
            
            // Validate position
            if (!in_array($position, ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'])) {
                Yii::$app->session->setFlash('error', 'Invalid watermark position.');
                return $this->redirect(['index']);
            }
            
            // Validate opacity
            if ($opacity < 0 || $opacity > 1) {
                Yii::$app->session->setFlash('error', 'Opacity must be between 0 and 1.');
                return $this->redirect(['index']);
            }
            
            // Validate text for text watermark
            if ($type === 'text' && empty($text)) {
                Yii::$app->session->setFlash('error', 'Watermark text is required for text watermark.');
                return $this->redirect(['index']);
            }
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Update settings
                $this->updateSetting('watermark.type', $type, 'Watermark type (text or image)');
                $this->updateSetting('watermark.text', $text, 'Watermark text');
                $this->updateSetting('watermark.position', $position, 'Watermark position');
                $this->updateSetting('watermark.opacity', (string)$opacity, 'Watermark opacity (0-1)');
                
                // Process image upload if provided
                if ($type === 'image') {
                    $uploadedImage = UploadedFile::getInstanceByName('image');
                    if ($uploadedImage) {
                        // Validate image type
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!in_array($uploadedImage->type, $allowedTypes)) {
                            throw new \Exception('Invalid image file type. Only JPG, PNG and GIF are allowed.');
                        }
                        
                        // Generate unique filename
                        $fileName = 'watermark_' . Yii::$app->security->generateRandomString(8) . '.' . $uploadedImage->extension;
                        $filePath = Yii::getAlias('@webroot/uploads/watermark/' . $fileName);
                        
                        // Create directory if it doesn't exist
                        $dir = Yii::getAlias('@webroot/uploads/watermark');
                        if (!is_dir($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        
                        // Save file
                        if (!$uploadedImage->saveAs($filePath)) {
                            throw new \Exception('Error saving watermark image file.');
                        }
                        
                        // Update image setting
                        $this->updateSetting('watermark.image', $fileName, 'Watermark image file');
                    }
                }
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Watermark settings updated successfully.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating watermark settings: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Generates a watermark preview.
     * @return mixed
     */
    public function actionPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $type = Yii::$app->request->post('type', 'text');
        $text = Yii::$app->request->post('text', '');
        $position = Yii::$app->request->post('position', 'bottom-right');
        $opacity = (float)Yii::$app->request->post('opacity', 0.5);
        
        try {
            // Create a sample image
            $img = Image::canvas(600, 400, '#eeeeee');
            
            // Add text
            $img->text('Sample Image', 300, 200, function($font) {
                //$font->file(Yii::getAlias('@webroot/fonts/arial.ttf'));
                $font->size(30);
                $font->color('#999999');
                $font->align('center');
                $font->valign('center');
            });
            
            // Apply watermark
            if ($type === 'text' && !empty($text)) {
                // Position mapping
                $positionMap = [
                    'top-left' => 'top-left',
                    'top-right' => 'top-right',
                    'bottom-left' => 'bottom-left',
                    'bottom-right' => 'bottom-right',
                    'center' => 'center',
                ];
                
                $pos = $positionMap[$position] ?? 'bottom-right';
                
                // Calculate coordinates based on position
                if ($pos === 'top-left') {
                    $x = 20;
                    $y = 20;
                    $alignH = 'left';
                    $alignV = 'top';
                } elseif ($pos === 'top-right') {
                    $x = 580;
                    $y = 20;
                    $alignH = 'right';
                    $alignV = 'top';
                } elseif ($pos === 'bottom-left') {
                    $x = 20;
                    $y = 380;
                    $alignH = 'left';
                    $alignV = 'bottom';
                } elseif ($pos === 'bottom-right') {
                    $x = 580;
                    $y = 380;
                    $alignH = 'right';
                    $alignV = 'bottom';
                } else { // center
                    $x = 300;
                    $y = 200;
                    $alignH = 'center';
                    $alignV = 'center';
                }
                
                // Add watermark text
                $img->text($text, $x, $y, function($font) use ($opacity, $alignH, $alignV) {
                    //$font->file(Yii::getAlias('@webroot/fonts/arial.ttf'));
                    $font->size(20);
                    $font->color(array(255, 255, 255, $opacity * 255));
                    $font->align($alignH);
                    $font->valign($alignV);
                });
            } elseif ($type === 'image') {
                // Get watermark image
                $watermarkImage = UploadedFile::getInstanceByName('image');
                $watermarkPath = null;
                
                if ($watermarkImage) {
                    // Save uploaded image temporarily
                    $tempPath = Yii::getAlias('@runtime/temp_watermark.' . $watermarkImage->extension);
                    $watermarkImage->saveAs($tempPath);
                    $watermarkPath = $tempPath;
                } else {
                    // Use existing watermark image if available
                    $existingImage = $this->getSettingValue('watermark.image', '');
                    if (!empty($existingImage)) {
                        $watermarkPath = Yii::getAlias('@webroot/uploads/watermark/' . $existingImage);
                    }
                }
                
                if ($watermarkPath && file_exists($watermarkPath)) {
                    $watermark = Image::make($watermarkPath);
                    
                    // Scale watermark
                    $maxWidth = $img->width() / 4; // Max 25% of image width
                    $maxHeight = $img->height() / 4; // Max 25% of image height
                    
                    if ($watermark->width() > $maxWidth || $watermark->height() > $maxHeight) {
                        $watermark->resize($maxWidth, $maxHeight, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }
                    
                    // Add opacity
                    $watermark->opacity($opacity * 100);
                    
                    // Insert watermark
                    $img->insert($watermark, $position);
                    
                    // Delete temporary file if created
                    if (isset($tempPath) && file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }
            }
            
            // Convert to base64
            $data = (string)$img->encode('data-url');
            
            return [
                'success' => true,
                'preview' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage()
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