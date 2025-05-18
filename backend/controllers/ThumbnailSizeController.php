<?php

namespace backend\controllers;

use Yii;
use common\models\ThumbnailSize;
use common\models\ThumbnailSizeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

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
     * Regenerates thumbnails for all or specific photo.
     * @return mixed
     */
    public function actionRegenerate()
    {
        $photoId = Yii::$app->request->post('photo_id');
        
        // Queue the regeneration task
        $task = new \common\models\QueuedJob();
        $task->type = 'regenerate_thumbnails';
        $task->params = $photoId ? json_encode(['photo_id' => $photoId]) : '{}';
        $task->status = \common\models\QueuedJob::STATUS_PENDING;
        $task->created_at = time();
        
        if ($task->save()) {
            Yii::$app->session->setFlash('success', 'Thumbnail regeneration task queued successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error queuing thumbnail regeneration task: ' . json_encode($task->errors));
        }
        
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