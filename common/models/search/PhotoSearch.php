<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Photo;
use yii\helpers\ArrayHelper;

/**
 * PhotoSearch represents the model behind the search form of `common\models\Photo`.
 */
class PhotoSearch extends Photo
{
    public $tag;
    public $category;
    public $from_date;
    public $to_date;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'width', 'height', 'status', 'created_by'], 'integer'],
            [['title', 'description', 'file_name', 'mime_type', 's3_path', 'tag', 'category', 'from_date', 'to_date'], 'safe'],
            [['is_public'], 'boolean'],
            [['file_size'], 'number'],
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
        $query = Photo::find();
        $query->joinWith(['photoTags', 'photoCategories']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'photo.id' => $this->id,
            'photo.width' => $this->width,
            'photo.height' => $this->height,
            'photo.file_size' => $this->file_size,
            'photo.status' => $this->status,
            'photo.is_public' => $this->is_public,
            'photo.created_by' => $this->created_by,
        ]);

        // Date range filtering
        if (!empty($this->from_date)) {
            $query->andFilterWhere(['>=', 'photo.created_at', strtotime($this->from_date . ' 00:00:00')]);
        }
        
        if (!empty($this->to_date)) {
            $query->andFilterWhere(['<=', 'photo.created_at', strtotime($this->to_date . ' 23:59:59')]);
        }

        $query->andFilterWhere(['like', 'photo.title', $this->title])
            ->andFilterWhere(['like', 'photo.description', $this->description])
            ->andFilterWhere(['like', 'photo.file_name', $this->file_name])
            ->andFilterWhere(['like', 'photo.mime_type', $this->mime_type])
            ->andFilterWhere(['like', 'photo.s3_path', $this->s3_path]);
        
        // Tag filtering
        if (!empty($this->tag)) {
            $query->andFilterWhere(['tag.id' => $this->tag]);
        }
        
        // Category filtering
        if (!empty($this->category)) {
            $query->andFilterWhere(['category.id' => $this->category]);
        }
        
        // Group by photo.id to remove duplicates from joins
        $query->groupBy('photo.id');

        return $dataProvider;
    }
}