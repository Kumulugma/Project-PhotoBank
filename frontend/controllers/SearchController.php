<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use common\models\Photo;
use common\models\Category;
use common\models\Tag;
use frontend\models\SearchForm;

/**
 * SearchController - tylko dla zalogowanych użytkowników
 */
class SearchController extends Controller
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
     * Search photos
     */
    public function actionIndex()
    {
        $model = new SearchForm();
        $dataProvider = null;
        
        if ($model->load(Yii::$app->request->get()) && $model->validate()) {
            $query = Photo::find()
                ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true]);
                
            // Search by keywords
            if (!empty($model->keywords)) {
                $query->andWhere([
                    'or',
                    ['like', 'title', $model->keywords],
                    ['like', 'description', $model->keywords]
                ]);
            }
            
            // Search by tags
            if (!empty($model->tags)) {
                $query->joinWith('photoTags');
                $query->andWhere(['in', 'photo_tag.tag_id', $model->tags]);
            }
            
            // Search by categories
            if (!empty($model->categories)) {
                $query->joinWith('photoCategories');
                $query->andWhere(['in', 'photo_category.category_id', $model->categories]);
            }
            
            $query->orderBy(['created_at' => SORT_DESC]);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Yii::$app->params['galleryPageSize'] ?? 24,
                ],
            ]);
        }
        
        // Get categories and tags for form
        $categories = Category::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        $tags = Tag::find()
            ->orderBy(['frequency' => SORT_DESC])
            ->limit(50)
            ->all();

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}