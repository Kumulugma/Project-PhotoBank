<?php

namespace backend\controllers;

use Yii;
use common\models\ThumbnailSize;
use common\models\search\ThumbnailSizeSearch;
use common\models\QueuedJob;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * ThumbnailSizeController implements the CRUD actions for ThumbnailSize model.
 */
class ThumbnailSizeController extends Controller
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
                    'regenerate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ThumbnailSize models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ThumbnailSizeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ThumbnailSize model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ThumbnailSize model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ThumbnailSize();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = time();
            $model->updated_at = time();
            
            // Validate unique name
            $existingSize = ThumbnailSize::findOne(['name' => $model->name]);
            if ($existingSize) {
                Yii::$app->session->setFlash('error', 'A thumbnail size with this name already exists.');
            } else if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Thumbnail size created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ThumbnailSize model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Validate unique name
            $existingSize = ThumbnailSize::findOne(['name' => $model->name]);
            if ($existingSize && $existingSize->id != $id) {
                Yii::$app->session->setFlash('error', 'A thumbnail size with this name already exists.');
            } else {
                $model->updated_at = time();
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Thumbnail size updated successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ThumbnailSize model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // Check if this is not the only size
        $totalSizes = ThumbnailSize::find()->count();
        if ($totalSizes <= 1) {
            Yii::$app->session->setFlash('error', 'Cannot delete the only thumbnail size.');
            return $this->redirect(['index']);
        }
        
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Thumbnail size deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Regenerates thumbnails for a specific size or for all photos.
     * @return mixed
     */
    public function actionRegenerate()
    {
        // Get the size ID and other parameters
        $sizeId = Yii::$app->request->post('size_id');
        $photoId = Yii::$app->request->post('photo_id');
        $partial = (bool)Yii::$app->request->post('partial', false);
        
        // Validate size ID if provided
        if ($sizeId && !ThumbnailSize::findOne($sizeId)) {
            Yii::$app->session->setFlash('error', 'Invalid thumbnail size ID.');
            return $this->redirect(['index']);
        }
        
        // Queue the regeneration task
        $job = new QueuedJob();
        $job->type = 'regenerate_thumbnails';
        
        // Build job parameters
        $params = [];
        if ($sizeId) {
            $params['size_id'] = $sizeId;
        }
        if ($photoId) {
            $params['photo_id'] = $photoId;
        }
        if ($partial) {
            $params['partial'] = true;
        }
        
        $job->params = json_encode($params);
        $job->status = QueuedJob::STATUS_PENDING;
        $job->created_at = time();
        $job->updated_at = time();
        
        if ($job->save()) {
            $sizeModel = $sizeId ? ThumbnailSize::findOne($sizeId) : null;
            $sizeName = $sizeModel ? "for size '{$sizeModel->name}'" : "for all sizes";
            
            Yii::$app->session->setFlash('success', "Thumbnail regeneration $sizeName queued successfully.");
        } else {
            Yii::$app->session->setFlash('error', 'Error queuing thumbnail regeneration task: ' . json_encode($job->errors));
        }
        
        // Redirect based on request type
        if ($sizeId) {
            return $this->redirect(['view', 'id' => $sizeId]);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * Exports thumbnail size configuration.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionExport($id)
    {
        $model = $this->findModel($id);
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->setDownloadHeaders(
            "thumbnail-size-{$model->name}.json",
            'application/json',
            false,
            strlen(json_encode($model->attributes))
        );
        
        return $model->attributes;
    }

    /**
     * Imports thumbnail size configuration.
     * @return mixed
     */
    public function actionImport()
    {
        // TO BE IMPLEMENTED
        return $this->redirect(['index']);
    }

    /**
     * Finds the ThumbnailSize model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ThumbnailSize the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ThumbnailSize::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested thumbnail size does not exist.');
    }
}