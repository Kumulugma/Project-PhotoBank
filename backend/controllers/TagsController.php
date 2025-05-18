<?php

namespace backend\controllers;

use Yii;
use common\models\Tag;
use common\models\TagSearch;
use common\models\PhotoTag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * TagsController implements the CRUD actions for Tag model.
 */
class TagsController extends Controller
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
     * Lists all Tag models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tag model.
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
     * Creates a new Tag model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tag();

        if ($model->load(Yii::$app->request->post())) {
            $model->frequency = 0; // No usages initially
            $model->created_at = time();
            $model->updated_at = time();
            
            // Check if similar tag already exists (case insensitive)
            $existingTag = Tag::find()
                ->where(['LOWER(name)' => strtolower($model->name)])
                ->one();
                
            if ($existingTag) {
                Yii::$app->session->setFlash('warning', 'A similar tag already exists: ' . $existingTag->name);
                return $this->redirect(['view', 'id' => $existingTag->id]);
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Tag created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tag model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Check if similar tag already exists (case insensitive)
            $existingTag = Tag::find()
                ->where(['LOWER(name)' => strtolower($model->name)])
                ->andWhere(['<>', 'id', $id])
                ->one();
                
            if ($existingTag) {
                Yii::$app->session->setFlash('warning', 'A similar tag already exists: ' . $existingTag->name);
                return $this->redirect(['view', 'id' => $existingTag->id]);
            }
            
            $model->updated_at = time();
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Tag updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Tag model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete all associated photo-tag relationships
            PhotoTag::deleteAll(['tag_id' => $id]);
            
            // Delete the tag
            $this->findModel($id)->delete();
            
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Tag deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error deleting tag: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Tag model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tag the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tag::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested tag does not exist.');
    }
}