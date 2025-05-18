<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use common\models\Photo;
use common\models\Category;
use common\models\Tag;
use frontend\models\SearchForm;

/**
 * SearchController implements the search functionality.
 */
class SearchController extends Controller
{
    /**
     * Search photos by keywords, tags and categories.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new SearchForm();
        $dataProvider = null;
        
        if ($model->load(Yii::$app->request->get()) && $model->validate()) {
            $query = Photo::find()
                ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true]);
                
            // Wyszukiwanie po słowach kluczowych
            if (!empty($model->keywords)) {
                $query->andWhere([
                    'or',
                    ['like', 'title', $model->keywords],
                    ['like', 'description', $model->keywords]
                ]);
            }
            
            // Wyszukiwanie po tagach
            if (!empty($model->tags)) {
                $query->joinWith('photoTags');
                $query->andWhere(['in', 'photo_tag.tag_id', $model->tags]);
            }
            
            // Wyszukiwanie po kategoriach
            if (!empty($model->categories)) {
                $query->joinWith('photoCategories');
                $query->andWhere(['in', 'photo_category.category_id', $model->categories]);
            }
            
            // Sortowanie
            $query->orderBy(['created_at' => SORT_DESC]);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Yii::$app->params['galleryPageSize'],
                ],
            ]);
        }
        
        // Pobieranie listy kategorii dla formularza
        $categories = Category::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        // Pobieranie popularnych tagów dla formularza
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