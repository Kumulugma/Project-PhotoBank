<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\Photo;
use common\models\Category;
use common\models\Tag;

/**
 * GalleryController - tylko dla zalogowanych użytkowników
 */
class GalleryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all public photos
     */
    public function actionIndex()
    {
        $query = Photo::find()
            ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true])
            ->orderBy(['created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'] ?? 24,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single photo
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Get previous and next photos
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
     * Displays photos by category
     */
    public function actionCategory($slug)
    {
        $category = Category::findOne(['slug' => $slug]);
        if (!$category) {
            throw new NotFoundHttpException('Kategoria nie została znaleziona.');
        }
        
        $query = Photo::find()
            ->joinWith('photoCategories')
            ->where(['photo_category.category_id' => $category->id])
            ->andWhere(['photo.status' => Photo::STATUS_ACTIVE, 'photo.is_public' => true])
            ->orderBy(['photo.created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'] ?? 24,
            ],
        ]);

        return $this->render('category', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays photos by tag
     */
    public function actionTag($name)
    {
        $tag = Tag::findOne(['name' => $name]);
        if (!$tag) {
            throw new NotFoundHttpException('Tag nie został znaleziony.');
        }
        
        $query = Photo::find()
            ->joinWith('photoTags')
            ->where(['photo_tag.tag_id' => $tag->id])
            ->andWhere(['photo.status' => Photo::STATUS_ACTIVE, 'photo.is_public' => true])
            ->orderBy(['photo.created_at' => SORT_DESC]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['galleryPageSize'] ?? 24,
            ],
        ]);

        return $this->render('tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Find photo model
     */
    protected function findModel($id)
    {
        $model = Photo::findOne([
            'id' => $id, 
            'status' => Photo::STATUS_ACTIVE,
            'is_public' => true
        ]);
        
        if ($model === null) {
            throw new NotFoundHttpException('Zdjęcie nie zostało znalezione.');
        }

        return $model;
    }
}