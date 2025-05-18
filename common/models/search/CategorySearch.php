<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Category;

/**
 * CategorySearch represents the model behind the search form of `common\models\Category`.
 */
class CategorySearch extends Category
{
    public $photoCount;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'slug', 'description', 'created_at', 'updated_at', 'photoCount'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Category::find();
        
        // add conditions that should always apply here
        $query->joinWith('photoCategories');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ]
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        
        // Add a custom sort attribute for photo count
        $dataProvider->sort->attributes['photoCount'] = [
            'asc' => ['COUNT(photo_category.photo_id)' => SORT_ASC],
            'desc' => ['COUNT(photo_category.photo_id)' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        
        // Date range filtering
        if (!empty($this->created_at)) {
            if (strpos($this->created_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->created_at);
                $query->andFilterWhere(['>=', 'category.created_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'category.created_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'category.created_at', strtotime($this->created_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'category.created_at', strtotime($this->created_at . ' 23:59:59')]);
            }
        }
        
        if (!empty($this->updated_at)) {
            if (strpos($this->updated_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->updated_at);
                $query->andFilterWhere(['>=', 'category.updated_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'category.updated_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'category.updated_at', strtotime($this->updated_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'category.updated_at', strtotime($this->updated_at . ' 23:59:59')]);
            }
        }

        $query->andFilterWhere(['like', 'category.name', $this->name])
            ->andFilterWhere(['like', 'category.slug', $this->slug])
            ->andFilterWhere(['like', 'category.description', $this->description]);
            
        // Group by category.id and calculate photo count
        $query->groupBy('category.id');
        $query->select(['category.*', 'COUNT(photo_category.photo_id) as photoCount']);

        return $dataProvider;
    }
}