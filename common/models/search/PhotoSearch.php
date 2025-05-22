<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Photo;

/**
 * PhotoSearch represents the model behind the search form of `common\models\Photo`.
 */
class PhotoSearch extends Photo
{
    /**
     * @var string Search code field for filtering
     */
    public $search_code;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file_size', 'status', 'is_public', 'width', 'height', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['title', 'description', 'file_name', 'mime_type', 's3_path', 'search_code'], 'safe'],
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'search_code' => 'Kod wyszukiwania',
        ]);
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

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
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

        // Wyszukiwanie po kodzie - jeśli podano kod, wyszukaj głównie po nim
        if (!empty($this->search_code)) {
            $query->andFilterWhere(['like', 'search_code', strtoupper($this->search_code)]);
            // Jeśli wyszukujemy po kodzie, nie stosuj większości innych filtrów
            // ale nadal pozwól na filtrowanie po statusie
            if (!empty($this->status)) {
                $query->andFilterWhere(['status' => $this->status]);
            }
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'file_size' => $this->file_size,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'width' => $this->width,
            'height' => $this->height,
            'created_by' => $this->created_by,
        ]);

        // Date filtering for created_at
        if (!empty($this->created_at)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->created_at)) {
                // If it's a date format, search for that day
                $timestamp = strtotime($this->created_at);
                $nextDay = $timestamp + 86400; // +24 hours
                $query->andFilterWhere(['>=', 'created_at', $timestamp])
                      ->andFilterWhere(['<', 'created_at', $nextDay]);
            }
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'mime_type', $this->mime_type])
            ->andFilterWhere(['like', 's3_path', $this->s3_path]);

        return $dataProvider;
    }
}