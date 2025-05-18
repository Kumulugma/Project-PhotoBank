<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ThumbnailSize;

/**
 * ThumbnailSizeSearch represents the model behind the search form of `common\models\ThumbnailSize`.
 */
class ThumbnailSizeSearch extends ThumbnailSize
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'width', 'height'], 'integer'],
            [['name', 'created_at', 'updated_at'], 'safe'],
            [['crop', 'watermark'], 'boolean'],
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
        $query = ThumbnailSize::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
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
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height,
            'crop' => $this->crop,
            'watermark' => $this->watermark,
        ]);
        
        // Date range filtering
        if (!empty($this->created_at)) {
            if (strpos($this->created_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->created_at);
                $query->andFilterWhere(['>=', 'created_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'created_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'created_at', strtotime($this->created_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'created_at', strtotime($this->created_at . ' 23:59:59')]);
            }
        }
        
        if (!empty($this->updated_at)) {
            if (strpos($this->updated_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->updated_at);
                $query->andFilterWhere(['>=', 'updated_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'updated_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'updated_at', strtotime($this->updated_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'updated_at', strtotime($this->updated_at . ' 23:59:59')]);
            }
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}