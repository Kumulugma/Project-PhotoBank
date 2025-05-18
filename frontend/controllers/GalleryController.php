<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use common\models\Photo;
use common\models\Category;
use common\models\Tag;
use yii\helpers\Url;

/**
 * GalleryController implements the gallery actions.
 */
class GalleryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'category' => ['get'],
                    'tag' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Lists all public photos.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true])
            ->orderBy(['created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Photo model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Pobieranie wcześniejszego i następnego zdjęcia
        $prevPhoto = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true])
            ->andWhere(['<', 'id', $model->id])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $nextPhoto = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true])
            ->andWhere(['>', 'id', $model->id])
            ->orderBy(['id' => SORT_ASC])
            ->one();

        return $this->render('view', [
            'model' => $model,
            'prevPhoto' => $prevPhoto,
            'nextPhoto' => $nextPhoto,
        ]);
    }

    /**
     * Displays photos by category.
     * @param string $slug Category slug
     * @return mixed
     * @throws NotFoundHttpException if the category cannot be found
     */
    public function actionCategory($slug)
    {
        $category = Category::findOne(['slug' => $slug]);
        if (!$category) {
            throw new NotFoundHttpException('Kategoria nie istnieje.');
        }
        
        $query = Photo::find()
            ->joinWith('photoCategories')
            ->where(['photo_category.category_id' => $category->id])
            ->andWhere(['photo.status' => Photo::STATUS_ACTIVE, 'photo.is_public' => true])
            ->orderBy(['photo.created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'],
            ],
        ]);

        return $this->render('category', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays photos by tag.
     * @param string $name Tag name
     * @return mixed
     * @throws NotFoundHttpException if the tag cannot be found
     */
    public function actionTag($name)
    {
        $tag = Tag::findOne(['name' => $name]);
        if (!$tag) {
            throw new NotFoundHttpException('Tag nie istnieje.');
        }
        
        $query = Photo::find()
            ->joinWith('photoTags')
            ->where(['photo_tag.tag_id' => $tag->id])
            ->andWhere(['photo.status' => Photo::STATUS_ACTIVE, 'photo.is_public' => true])
            ->orderBy(['photo.created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'],
            ],
        ]);

        return $this->render('tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the Photo model based on its primary key value.
     * @param integer $id
     * @return Photo the loaded model
     * @throws NotFoundHttpException if the model cannot be found or not public
     */
    protected function findModel($id)
    {
        $model = Photo::findOne([
            'id' => $id, 
            'status' => Photo::STATUS_ACTIVE,
            'is_public' => true
        ]);
        
        if ($model === null) {
            throw new NotFoundHttpException('Zdjęcie nie istnieje lub nie jest publiczne.');
        }

        return $model;
    }
}