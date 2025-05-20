<?php

namespace backend\controllers;

use Yii;
use common\models\Photo;
use common\models\search\PhotoSearch;
use common\models\Tag;
use common\models\Category;
use common\models\PhotoTag;
use common\models\PhotoCategory;
use common\models\ThumbnailSize;
use common\models\Settings;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * PhotosController handles photo management operations
 */
class PhotosController extends Controller {

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
                    'delete' => ['POST'],
                    'batch-delete' => ['POST'],
                    'approve' => ['POST'],
                    'approve-batch' => ['POST'],
                ],
            ],
        ];
    }

// Dodaj tę metodę do wyłączenia weryfikacji CSRF dla konkretnych akcji
    public function beforeAction($action) {
        if (in_array($action->id, ['upload-ajax', 'upload-chunk'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all active photos.
     *
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new PhotoSearch();
        $searchModel->status = Photo::STATUS_ACTIVE;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all photos in queue.
     *
     * @return mixed
     */
    public function actionQueue() {
        $searchModel = new PhotoSearch();
        $searchModel->status = Photo::STATUS_QUEUE;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('queue', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Photo model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->findModel($id);

        // Get available thumbnail sizes
        $thumbnailSizes = ThumbnailSize::find()->all();
        $thumbnails = [];

        foreach ($thumbnailSizes as $size) {
            $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $model->file_name);
            $thumbnails[$size->name] = $thumbnailUrl;
        }

        // Get associated tags and categories
        $tags = $model->getTags()->all();
        $categories = $model->getCategories()->all();

        return $this->render('view', [
                    'model' => $model,
                    'thumbnails' => $thumbnails,
                    'tags' => $tags,
                    'categories' => $categories,
        ]);
    }

    /**
     * Renders the upload form.
     *
     * @return mixed
     */
    public function actionUpload() {
        return $this->render('upload');
    }

    /**
     * Handles the file upload via AJAX.
     *
     * @return mixed
     */
    public function actionUploadAjax() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $uploadedFile = UploadedFile::getInstanceByName('file');
        if (!$uploadedFile) {
            return [
                'success' => false,
                'message' => 'No file was uploaded',
            ];
        }

        // Validate MIME type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile->type, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed.',
            ];
        }

        // Generate unique filename
        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $uploadedFile->extension;
        $filePath = Yii::getAlias('@webroot/uploads/temp/' . $fileName);

        // Save file
        if (!$uploadedFile->saveAs($filePath)) {
            return [
                'success' => false,
                'message' => 'Error saving file',
            ];
        }

        // Read image dimensions and metadata
        $image = Image::make($filePath);
        $width = $image->width();
        $height = $image->height();

        // Create database record
        $photo = new Photo();
        $photo->title = pathinfo($uploadedFile->name, PATHINFO_FILENAME); // Default title is the filename
        $photo->file_name = $fileName;
        $photo->file_size = $uploadedFile->size;
        $photo->mime_type = $uploadedFile->type;
        $photo->width = $width;
        $photo->height = $height;
        $photo->status = Photo::STATUS_QUEUE; // In queue
        $photo->is_public = false;
        $photo->created_at = time();
        $photo->updated_at = time();
        $photo->created_by = Yii::$app->user->id;

        if (!$photo->save()) {
            unlink($filePath); // Delete file if database save fails
            return [
                'success' => false,
                'message' => 'Error saving photo data: ' . json_encode($photo->errors),
            ];
        }

        // Generate thumbnails
        $thumbnailSizes = ThumbnailSize::find()->all();
        $thumbnails = [];

        foreach ($thumbnailSizes as $size) {
            $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $fileName);
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
                // Add watermark according to settings
                $this->addWatermark($thumbnailImage);
            }

            $thumbnailImage->save($thumbnailPath);
            $thumbnails[$size->name] = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $fileName);
        }

        return [
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'title' => $photo->title,
                'file_name' => $photo->file_name,
                'width' => $photo->width,
                'height' => $photo->height,
                'thumbnails' => $thumbnails
            ]
        ];
    }

    /**
     * Handles chunked file upload via AJAX.
     *
     * @return mixed
     */
    public function actionUploadChunk() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Uploaded chunk
        $uploadedChunk = UploadedFile::getInstanceByName('file');
        if (!$uploadedChunk) {
            return [
                'success' => false,
                'message' => 'No file chunk was uploaded',
            ];
        }

        // Chunked upload parameters
        $chunkNumber = (int) Yii::$app->request->post('chunk', 0);
        $totalChunks = (int) Yii::$app->request->post('chunks', 0);
        $originalFileName = Yii::$app->request->post('name', '');

        // Generate unique upload session ID
        $uploadId = md5($originalFileName . Yii::$app->user->id . date('Ymd'));
        $chunkDir = Yii::getAlias('@webroot/uploads/chunks/' . $uploadId);

        // Create directory for chunks if it doesn't exist
        if (!file_exists($chunkDir)) {
            FileHelper::createDirectory($chunkDir, 0777, true);
        }

        // Save chunk
        $chunkPath = $chunkDir . '/' . $chunkNumber;
        if (!$uploadedChunk->saveAs($chunkPath)) {
            return [
                'success' => false,
                'message' => 'Error saving file chunk',
            ];
        }

        // Check if this is the last chunk
        $isCompleted = ($chunkNumber == $totalChunks - 1);

        if ($isCompleted) {
            // Combine chunks into one file
            $fileName = Yii::$app->security->generateRandomString(16) . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);
            $filePath = Yii::getAlias('@webroot/uploads/temp/' . $fileName);

            $out = fopen($filePath, "wb");
            if (!$out) {
                return [
                    'success' => false,
                    'message' => 'Cannot create target file',
                ];
            }

            // Combine chunks
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $chunkDir . '/' . $i;
                if (!file_exists($chunkPath)) {
                    fclose($out);
                    unlink($filePath);
                    return [
                        'success' => false,
                        'message' => 'Missing file chunk: ' . $i,
                    ];
                }

                $in = fopen($chunkPath, "rb");
                if (!$in) {
                    fclose($out);
                    unlink($filePath);
                    return [
                        'success' => false,
                        'message' => 'Cannot read file chunk: ' . $i,
                    ];
                }

                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }

                fclose($in);
                unlink($chunkPath); // Delete chunk after processing
            }

            fclose($out);
            rmdir($chunkDir); // Delete chunks directory
            // Validate MIME type
            $mimeType = FileHelper::getMimeType($filePath);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes)) {
                unlink($filePath);
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed.',
                ];
            }

            // Create record in database and thumbnails - similar to actionUploadAjax
            $image = Image::make($filePath);
            $width = $image->width();
            $height = $image->height();

            $photo = new Photo();
            $photo->title = pathinfo($originalFileName, PATHINFO_FILENAME);
            $photo->file_name = $fileName;
            $photo->file_size = filesize($filePath);
            $photo->mime_type = $mimeType;
            $photo->width = $width;
            $photo->height = $height;
            $photo->status = Photo::STATUS_QUEUE;
            $photo->is_public = false;
            $photo->created_at = time();
            $photo->updated_at = time();
            $photo->created_by = Yii::$app->user->id;

            if (!$photo->save()) {
                unlink($filePath);
                return [
                    'success' => false,
                    'message' => 'Error saving photo data: ' . json_encode($photo->errors),
                ];
            }

            // Generate thumbnails
            $thumbnailSizes = ThumbnailSize::find()->all();
            $thumbnails = [];

            foreach ($thumbnailSizes as $size) {
                $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $fileName);
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
                    $this->addWatermark($thumbnailImage);
                }

                $thumbnailImage->save($thumbnailPath);
                $thumbnails[$size->name] = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $fileName);
            }

            return [
                'success' => true,
                'completed' => true,
                'photo' => [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'file_name' => $photo->file_name,
                    'width' => $photo->width,
                    'height' => $photo->height,
                    'thumbnails' => $thumbnails
                ]
            ];
        } else {
            // If this is not the last chunk, return progress info
            return [
                'success' => true,
                'completed' => false,
                'chunk' => $chunkNumber,
                'chunks' => $totalChunks
            ];
        }
    }

    /**
     * Updates an existing Photo model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        // Get all tags and categories for dropdown
        $allTags = Tag::find()->orderBy(['name' => SORT_ASC])->all();
        $allCategories = Category::find()->orderBy(['name' => SORT_ASC])->all();

        // Get currently selected tags and categories
        $selectedTags = $model->getTags()->select('id')->column();
        $selectedCategories = $model->getCategories()->select('id')->column();

        if ($model->load(Yii::$app->request->post())) {
            // Get submitted tags and categories
            $newTags = Yii::$app->request->post('tags', []);
            $newCategories = Yii::$app->request->post('categories', []);

            // Start a transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) {
                    throw new \Exception('Error saving photo: ' . json_encode($model->errors));
                }

                // Update tags
                PhotoTag::deleteAll(['photo_id' => $id]);
                foreach ($newTags as $tagId) {
                    $tag = Tag::findOne($tagId);
                    if ($tag) {
                        $photoTag = new PhotoTag();
                        $photoTag->photo_id = $id;
                        $photoTag->tag_id = $tagId;
                        if (!$photoTag->save()) {
                            throw new \Exception('Error saving tag relationship');
                        }

                        // Update tag frequency
                        $tag->frequency += 1;
                        $tag->save();
                    }
                }

                // Update categories
                PhotoCategory::deleteAll(['photo_id' => $id]);
                foreach ($newCategories as $categoryId) {
                    $category = Category::findOne($categoryId);
                    if ($category) {
                        $photoCategory = new PhotoCategory();
                        $photoCategory->photo_id = $id;
                        $photoCategory->category_id = $categoryId;
                        if (!$photoCategory->save()) {
                            throw new \Exception('Error saving category relationship');
                        }
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Photo updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
                    'model' => $model,
                    'allTags' => $allTags,
                    'allCategories' => $allCategories,
                    'selectedTags' => $selectedTags,
                    'selectedCategories' => $selectedCategories,
        ]);
    }

    /**
     * Finds the Photo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Photo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Photo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested photo does not exist.');
    }

    /**
     * Adds watermark to image
     * 
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    protected function addWatermark($image) {
        // Get watermark settings
        $watermarkType = Settings::findOne(['key' => 'watermark.type'])->value ?? 'text';
        $watermarkPosition = Settings::findOne(['key' => 'watermark.position'])->value ?? 'bottom-right';
        $watermarkOpacity = (float) Settings::findOne(['key' => 'watermark.opacity'])->value ?? 0.5;

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
            $watermarkText = Settings::findOne(['key' => 'watermark.text'])->value ?? '';

            if (!empty($watermarkText)) {
                $fontSize = min($image->width(), $image->height()) / 20; // Scale font size

                $image->text($watermarkText, $image->width() - 20, $image->height() - 20, function ($font) use ($fontSize, $watermarkOpacity) {
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity * 255]);
                    $font->align('right');
                    $font->valign('bottom');
                });
            }
        } elseif ($watermarkType === 'image') {
            // Image watermark
            $watermarkImage = Settings::findOne(['key' => 'watermark.image'])->value ?? '';

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
