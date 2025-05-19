<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QueuedJob;

/**
 * QueuedJobSearch represents the model behind the search form of `common\models\QueuedJob`.
 */
class QueuedJobSearch extends QueuedJob
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'started_at', 'finished_at', 'attempts'], 'integer'],
            [['type', 'params', 'error_message'], 'safe'],
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
        $query = QueuedJob::find();

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
            'id' => $this->id,
            'status' => $this->status,
            'attempts' => $this->attempts,
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
        
        if (!empty($this->started_at)) {
            if (strpos($this->started_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->started_at);
                $query->andFilterWhere(['>=', 'started_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'started_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'started_at', strtotime($this->started_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'started_at', strtotime($this->started_at . ' 23:59:59')]);
            }
        }
        
        if (!empty($this->finished_at)) {
            if (strpos($this->finished_at, ' - ') !== false) {
                list($start_date, $end_date) = explode(' - ', $this->finished_at);
                $query->andFilterWhere(['>=', 'finished_at', strtotime($start_date . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'finished_at', strtotime($end_date . ' 23:59:59')]);
            } else {
                $query->andFilterWhere(['>=', 'finished_at', strtotime($this->finished_at . ' 00:00:00')])
                    ->andFilterWhere(['<=', 'finished_at', strtotime($this->finished_at . ' 23:59:59')]);
            }
        }

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'params', $this->params])
            ->andFilterWhere(['like', 'error_message', $this->error_message]);

        return $dataProvider;
    }
}