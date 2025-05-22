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
use common\models\QueuedJob;
use common\helpers\PathHelper;

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
                    'import-from-ftp' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        if (in_array($action->id, ['upload-ajax', 'upload-chunk'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Finds photo by search code and redirects to view
     * @param string $code
     * @return mixed
     */
    public function actionFindByCode($code = null) {
        // Jeśli to żądanie AJAX, zwróć odpowiedź JSON
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (empty($code)) {
                return ['success' => false, 'message' => 'Nie podano kodu'];
            }

            $photo = Photo::findBySearchCode($code);

            if (!$photo) {
                return ['success' => false, 'message' => 'Nie znaleziono zdjęcia o kodzie: ' . $code];
            }

            return [
                'success' => true,
                'redirect' => Yii::$app->urlManager->createUrl(['photos/view', 'id' => $photo->id])
            ];
        }

        // Dla zwykłych żądań HTTP
        if (empty($code)) {
            $code = Yii::$app->request->get('code');
        }

        if (empty($code)) {
            Yii::$app->session->setFlash('error', 'Nie podano kodu wyszukiwania');
            return $this->redirect(['index']);
        }

        $photo = Photo::findBySearchCode($code);

        if (!$photo) {
            Yii::$app->session->setFlash('error', 'Nie znaleziono zdjęcia o kodzie: ' . $code);
            return $this->redirect(['index']);
        }

        return $this->redirect(['view', 'id' => $photo->id]);
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

        // Get available thumbnail sizes using PathHelper
        $thumbnailSizes = ThumbnailSize::find()->all();
        $thumbnails = [];

        foreach ($thumbnailSizes as $size) {
            $thumbnail = PathHelper::getAvailableThumbnail($size->name, $model->file_name);

            if ($thumbnail) {
                $thumbnails[$size->name] = $thumbnail['url'];
            } else {
                // Wygeneruj URL nawet jeśli plik nie istnieje (dla spójności)
                $thumbnails[$size->name] = PathHelper::getThumbnailUrl($size->name, $model->file_name);
            }
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

        // Generate filename with original name + hash
        $originalName = pathinfo($uploadedFile->name, PATHINFO_FILENAME);
        $extension = $uploadedFile->extension;
        $hash = substr(Yii::$app->security->generateRandomString(12), 0, 8);
        $fileName = $originalName . '_' . $hash . '.' . $extension;

        // Use PathHelper for file paths
        PathHelper::ensureDirectoryExists('temp');
        $filePath = PathHelper::getPhotoPath($fileName, 'temp');

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
        $photo->title = $originalName; // Use original name as title
        $photo->file_name = $fileName;
        $photo->file_size = $uploadedFile->size;
        $photo->mime_type = $uploadedFile->type;
        $photo->width = $width;
        $photo->height = $height;
        $photo->status = Photo::STATUS_QUEUE;
        $photo->is_public = 0;
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

        $photo->extractAndSaveExif();

        // Generate thumbnails using PathHelper
        PathHelper::ensureDirectoryExists('thumbnails');
        $thumbnailSizes = ThumbnailSize::find()->all();
        $thumbnails = [];

        foreach ($thumbnailSizes as $size) {
            $thumbnailPath = PathHelper::getThumbnailPath($size->name, $fileName);
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
            $thumbnails[$size->name] = PathHelper::getThumbnailUrl($size->name, $fileName);
        }

        return [
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'title' => $photo->title,
                'file_name' => $photo->file_name,
                'search_code' => $photo->search_code,
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
            $photo->is_public = 0;
            $photo->created_at = time();
            $photo->updated_at = time();
            $photo->created_by = Yii::$app->user->id;
            // search_code will be generated automatically in beforeSave()

            if (!$photo->save()) {
                unlink($filePath);
                return [
                    'success' => false,
                    'message' => 'Error saving photo data: ' . json_encode($photo->errors),
                ];
            }

            $photo->extractAndSaveExif();

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
                    'search_code' => $photo->search_code,
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
                    // Handle new tags (string IDs that are not numeric)
                    if (!is_numeric($tagId)) {
                        // Create new tag
                        $tag = new Tag();
                        $tag->name = $tagId;
                        $tag->frequency = 0;
                        $tag->created_at = time();
                        $tag->updated_at = time();

                        if ($tag->save()) {
                            $tagId = $tag->id;
                        } else {
                            throw new \Exception('Error creating new tag: ' . json_encode($tag->errors));
                        }
                    }

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
     * Batch update selected photos.
     *
     * @return mixed
     */
    public function actionBatchUpdate() {
        if (Yii::$app->request->isPost) {
            $ids = explode(',', Yii::$app->request->post('ids', ''));
            $status = Yii::$app->request->post('status', '');
            $isPublic = Yii::$app->request->post('is_public', '');
            $categories = Yii::$app->request->post('categories', []);
            $tags = Yii::$app->request->post('tags', []);
            $replace = (bool) Yii::$app->request->post('replace', false);

            if (empty($ids)) {
                Yii::$app->session->setFlash('error', 'Nie wybrano żadnych zdjęć do aktualizacji.');
                return $this->redirect(['index']);
            }

            $updatedCount = 0;
            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($ids as $id) {
                    $model = $this->findModel($id);

                    // Update status if provided
                    if ($status !== '') {
                        $model->status = (int) $status;
                    }

                    // Update visibility if provided
                    if ($isPublic !== '') {
                        $model->is_public = (int) $isPublic;
                    }

                    // Save model changes
                    if (!$model->save()) {
                        throw new \Exception('Error updating photo ID ' . $id . ': ' . json_encode($model->errors));
                    }

                    // Update categories
                    if (!empty($categories)) {
                        if ($replace) {
                            PhotoCategory::deleteAll(['photo_id' => $id]);
                        }

                        foreach ($categories as $categoryId) {
                            // Check if relation already exists
                            $existingRelation = PhotoCategory::findOne(['photo_id' => $id, 'category_id' => $categoryId]);
                            if (!$existingRelation) {
                                $photoCategory = new PhotoCategory();
                                $photoCategory->photo_id = $id;
                                $photoCategory->category_id = $categoryId;
                                $photoCategory->save();
                            }
                        }
                    }

                    // Update tags
                    if (!empty($tags)) {
                        if ($replace) {
                            PhotoTag::deleteAll(['photo_id' => $id]);
                        }

                        foreach ($tags as $tagId) {
                            // Check if relation already exists
                            $existingRelation = PhotoTag::findOne(['photo_id' => $id, 'tag_id' => $tagId]);
                            if (!$existingRelation) {
                                $photoTag = new PhotoTag();
                                $photoTag->photo_id = $id;
                                $photoTag->tag_id = $tagId;
                                if ($photoTag->save()) {
                                    // Update tag frequency
                                    $tag = Tag::findOne($tagId);
                                    if ($tag) {
                                        $tag->frequency += 1;
                                        $tag->save();
                                    }
                                }
                            }
                        }
                    }

                    $updatedCount++;
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Pomyślnie zaktualizowano $updatedCount zdjęć.");
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas aktualizacji: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Deletes a photo.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Remove relationships with tags and categories
            PhotoTag::deleteAll(['photo_id' => $id]);
            PhotoCategory::deleteAll(['photo_id' => $id]);

            // Change photo status to deleted
            $model->status = Photo::STATUS_DELETED;

            // If photo is stored on S3, move it to deleted directory
            if (!empty($model->s3_path)) {
                /** @var \common\components\S3Component $s3 */
                $s3 = Yii::$app->get('s3');
                $s3Settings = $s3->getSettings();

                // Target path in deleted directory
                $deletedKey = $s3Settings['deleted_directory'] . '/' . date('Y/m/d') . '/' . $model->file_name;

                // Copy file to deleted directory
                $s3->copyObject([
                    'Bucket' => $s3Settings['bucket'],
                    'CopySource' => $s3Settings['bucket'] . '/' . $model->s3_path,
                    'Key' => $deletedKey
                ]);

                // Delete original after copying
                $s3->deleteObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $model->s3_path
                ]);

                // Update S3 path to new location in deleted directory
                $model->s3_path = $deletedKey;
            }

            // Save model changes
            if (!$model->save()) {
                throw new \Exception('Cannot mark photo as deleted: ' . json_encode($model->errors));
            }

            // Move local file to deleted directory if exists
            $localPath = Yii::getAlias('@webroot/uploads/temp/' . $model->file_name);
            if (file_exists($localPath)) {
                // Create deleted directory if it doesn't exist
                $deletedDir = Yii::getAlias('@webroot/uploads/deleted/' . date('Y/m/d'));
                if (!file_exists($deletedDir)) {
                    \yii\helpers\FileHelper::createDirectory($deletedDir, 0777, true);
                }

                // Move file
                $deletedPath = $deletedDir . '/' . $model->file_name;
                rename($localPath, $deletedPath);
            }

            // Delete thumbnails - these are always completely removed
            $thumbnailSizes = ThumbnailSize::find()->all();
            foreach ($thumbnailSizes as $size) {
                $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $model->file_name);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Photo has been successfully deleted.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error occurred while deleting photo: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Batch delete photos.
     *
     * @return mixed
     */
    public function actionBatchDelete() {
        if (Yii::$app->request->isPost) {
            $ids = explode(',', Yii::$app->request->post('ids', ''));

            if (empty($ids)) {
                Yii::$app->session->setFlash('error', 'No photos selected for deletion.');
                return $this->redirect(['index']);
            }

            $transaction = Yii::$app->db->beginTransaction();
            $deletedCount = 0;

            try {
                foreach ($ids as $id) {
                    $model = $this->findModel($id);

                    // Remove relationships
                    PhotoTag::deleteAll(['photo_id' => $id]);
                    PhotoCategory::deleteAll(['photo_id' => $id]);

                    // Change photo status to deleted
                    $model->status = Photo::STATUS_DELETED;

                    // If photo is stored on S3, move it to deleted directory
                    if (!empty($model->s3_path)) {
                        /** @var \common\components\S3Component $s3 */
                        $s3 = Yii::$app->get('s3');
                        $s3Settings = $s3->getSettings();

                        // Target path in deleted directory
                        $deletedKey = $s3Settings['deleted_directory'] . '/' . date('Y/m/d') . '/' . $model->file_name;

                        // Copy file to deleted directory
                        $s3->copyObject([
                            'Bucket' => $s3Settings['bucket'],
                            'CopySource' => $s3Settings['bucket'] . '/' . $model->s3_path,
                            'Key' => $deletedKey
                        ]);

                        // Delete original after copying
                        $s3->deleteObject([
                            'Bucket' => $s3Settings['bucket'],
                            'Key' => $model->s3_path
                        ]);

                        // Update S3 path to new location in deleted directory
                        $model->s3_path = $deletedKey;
                    }

                    // Save model changes
                    if (!$model->save()) {
                        throw new \Exception('Cannot mark photo as deleted: ' . json_encode($model->errors));
                    }

                    // Move local file to deleted directory if exists
                    $localPath = Yii::getAlias('@webroot/uploads/temp/' . $model->file_name);
                    if (file_exists($localPath)) {
                        // Create deleted directory if it doesn't exist
                        $deletedDir = Yii::getAlias('@webroot/uploads/deleted/' . date('Y/m/d'));
                        if (!file_exists($deletedDir)) {
                            \yii\helpers\FileHelper::createDirectory($deletedDir, 0777, true);
                        }

                        // Move file
                        $deletedPath = $deletedDir . '/' . $model->file_name;
                        rename($localPath, $deletedPath);
                    }

                    // Delete thumbnails - these are always completely removed
                    $thumbnailSizes = ThumbnailSize::find()->all();
                    foreach ($thumbnailSizes as $size) {
                        $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $model->file_name);
                        if (file_exists($thumbnailPath)) {
                            unlink($thumbnailPath);
                        }
                    }

                    $deletedCount++;
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Successfully deleted $deletedCount photos.");
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error occurred while deleting photos: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Approves a photo in queue.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionApprove($id) {
        $model = $this->findModel($id);

        if ($model->status != Photo::STATUS_QUEUE) {
            Yii::$app->session->setFlash('error', 'Only photos in queue can be approved.');
            return $this->redirect(['queue']);
        }

        // Update status to active
        $model->status = Photo::STATUS_ACTIVE;

        if ($model->save()) {
            // Sync with S3 if needed and S3 is configured
            if (empty($model->s3_path) && Yii::$app->has('s3')) {
                try {
                    /** @var \common\components\S3Component $s3 */
                    $s3 = Yii::$app->get('s3');
                    $s3Settings = $s3->getSettings();

                    // Check if S3 is properly configured
                    if (!empty($s3Settings['bucket']) && !empty($s3Settings['region']) &&
                            !empty($s3Settings['access_key']) && !empty($s3Settings['secret_key'])) {

                        $filePath = Yii::getAlias('@webroot/uploads/temp/' . $model->file_name);

                        if (file_exists($filePath)) {
                            // Generate S3 path
                            $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d', $model->created_at) . '/' . $model->file_name;

                            // Upload file to S3
                            $s3->putObject([
                                'Bucket' => $s3Settings['bucket'],
                                'Key' => $s3Key,
                                'SourceFile' => $filePath,
                                'ContentType' => $model->mime_type
                            ]);

                            // Update S3 path in model
                            $model->s3_path = $s3Key;
                            $model->save();
                        }
                    } else {
                        Yii::$app->session->setFlash('warning', 'S3 is not properly configured. Photo was approved but not synced to S3 storage.');
                    }
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('warning', 'Photo was approved but error occurred during S3 sync: ' . $e->getMessage());
                }
            }

            Yii::$app->session->setFlash('success', 'Photo has been approved and moved to main gallery.');
        } else {
            Yii::$app->session->setFlash('error', 'Cannot approve photo: ' . json_encode($model->errors));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Batch approve photos in queue.
     *
     * @return mixed
     */
    public function actionApproveBatch() {
        if (Yii::$app->request->isPost) {
            $ids = explode(',', Yii::$app->request->post('ids', ''));
            $autoPublish = (bool) Yii::$app->request->post('auto_publish', false);

            if (empty($ids)) {
                Yii::$app->session->setFlash('error', 'No photos selected for approval.');
                return $this->redirect(['queue']);
            }

            $approvedCount = 0;
            $errorCount = 0;
            $s3ErrorCount = 0;

            // Check if S3 is available and configured
            $s3Available = false;
            $s3Settings = [];

            if (Yii::$app->has('s3')) {
                /** @var \common\components\S3Component $s3 */
                $s3 = Yii::$app->get('s3');
                $s3Settings = $s3->getSettings();

                // Check if S3 is properly configured
                if (!empty($s3Settings['bucket']) && !empty($s3Settings['region']) &&
                        !empty($s3Settings['access_key']) && !empty($s3Settings['secret_key'])) {
                    $s3Available = true;
                }
            }

            foreach ($ids as $id) {
                try {
                    $model = $this->findModel($id);

                    if ($model->status != Photo::STATUS_QUEUE) {
                        continue;
                    }

                    // Update status to active
                    $model->status = Photo::STATUS_ACTIVE;

                    // Set as public if option selected
                    if ($autoPublish) {
                        $model->is_public = 1;
                    }

                    if ($model->save()) {
                        $approvedCount++;

                        // Sync with S3 if needed and available
                        if (empty($model->s3_path) && $s3Available) {
                            $filePath = Yii::getAlias('@webroot/uploads/temp/' . $model->file_name);

                            if (file_exists($filePath)) {
                                // Generate S3 path
                                $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d', $model->created_at) . '/' . $model->file_name;

                                try {
                                    // Upload file to S3
                                    $s3->putObject([
                                        'Bucket' => $s3Settings['bucket'],
                                        'Key' => $s3Key,
                                        'SourceFile' => $filePath,
                                        'ContentType' => $model->mime_type
                                    ]);

                                    // Update S3 path in model
                                    $model->s3_path = $s3Key;
                                    $model->save();
                                } catch (\Exception $e) {
                                    // Log error but continue with next files
                                    Yii::error('S3 sync error for photo ID ' . $id . ': ' . $e->getMessage());
                                    $s3ErrorCount++;
                                }
                            }
                        }
                    } else {
                        $errorCount++;
                        Yii::error('Photo approval error for ID ' . $id . ': ' . json_encode($model->errors));
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Yii::error('Error during photo approval for ID ' . $id . ': ' . $e->getMessage());
                }
            }

            if ($approvedCount > 0) {
                $message = "Successfully approved $approvedCount photos.";

                if ($errorCount > 0) {
                    $message .= " Errors occurred with $errorCount photos.";
                }

                if ($s3ErrorCount > 0) {
                    $message .= " Failed to sync $s3ErrorCount photos with S3.";
                } else if (!$s3Available && $approvedCount > 0) {
                    $message .= " S3 is not configured - photos were approved locally.";
                }

                Yii::$app->session->setFlash('success', $message);
            } else if ($errorCount > 0) {
                Yii::$app->session->setFlash('error', "Failed to approve any photos. Errors occurred with $errorCount photos.");
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Imports photos from default FTP directory.
     *
     * @return mixed
     */
    public function actionImportFromFtp() {
        // Get default import directory from settings
        $importDirectory = Settings::findOne(['key' => 'upload.import_directory']);
        $directory = $importDirectory ? $importDirectory->value : 'uploads/import';

        // Additional options
        $recursive = (bool) Yii::$app->request->post('recursive', true);
        $deleteOriginals = (bool) Yii::$app->request->post('delete_originals', false);
        $runNow = (bool) Yii::$app->request->post('run_now', false);

        // Debug: Loguj parametry
        Yii::info("Import FTP - Katalog: $directory, Rekursywnie: " . ($recursive ? 'tak' : 'nie') .
                ", Usuń oryginały: " . ($deleteOriginals ? 'tak' : 'nie') .
                ", Uruchom teraz: " . ($runNow ? 'tak' : 'nie'));

        // Create background job for processing
        $job = new QueuedJob();
        $job->type = 'import_photos';
        $job->data = json_encode([
            'directory' => $directory,
            'recursive' => $recursive,
            'delete_originals' => $deleteOriginals,
            'created_by' => Yii::$app->user->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $job->status = QueuedJob::STATUS_PENDING;
        $job->created_at = time();
        $job->updated_at = time();

        if ($job->save()) {
            Yii::info('Utworzono zadanie importu ID: ' . $job->id);

            if ($runNow) {
                try {
                    Yii::info('Rozpoczynam natychmiastowe przetwarzanie zadania ID: ' . $job->id);

                    $jobProcessor = new \common\components\JobProcessor();
                    $job->markAsStarted();

                    if ($jobProcessor->processJob($job)) {
                        $job->markAsFinished();
                        Yii::$app->session->setFlash('success', 'Import zdjęć zakończony pomyślnie. Sprawdź szczegóły w widoku zadania.');
                    } else {
                        $job->markAsFailed('Błąd podczas przetwarzania zadania importu');
                        Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas importu zdjęć. Sprawdź szczegóły w widoku zadania.');
                    }

                    return $this->redirect(['queue/view', 'id' => $job->id]);
                } catch (\Exception $e) {
                    Yii::error('Błąd podczas importu: ' . $e->getMessage());
                    $job->markAsFailed($e->getMessage());
                    Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas importu: ' . $e->getMessage());
                    return $this->redirect(['queue/view', 'id' => $job->id]);
                }
            } else {
                Yii::$app->session->setFlash('success', 'Zadanie importu zostało dodane do kolejki. Zdjęcia pojawią się w poczekalni po przetworzeniu.');
                return $this->redirect(['queue/index']);
            }
        } else {
            $errorMsg = 'Nie udało się utworzyć zadania importu: ' . json_encode($job->errors);
            Yii::error($errorMsg);
            Yii::$app->session->setFlash('error', $errorMsg);
            return $this->redirect(['import']);
        }
    }

    /**
     * Renders the import form.
     *
     * @return mixed
     */
    public function actionImport() {
        return $this->render('import');
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
