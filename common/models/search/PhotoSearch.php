<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Photo;

class PhotoSearch extends Photo
{
    public $search_code;

    public function rules()
    {
        return [
            [['id', 'file_size', 'status', 'is_public', 'width', 'height', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['title', 'description', 'series', 'file_name', 'mime_type', 's3_path', 'search_code'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'search_code' => 'Kod wyszukiwania',
        ]);
    }

    public function search($params)
    {
        $query = Photo::find();

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
            return $dataProvider;
        }

        // Wyszukiwanie po kodzie - jeśli podano kod, wyszukaj głównie po nim
        if (!empty($this->search_code)) {
            $query->andFilterWhere(['like', 'search_code', strtoupper($this->search_code)]);
            if (!empty($this->status)) {
                $query->andFilterWhere(['status' => $this->status]);
            }
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'file_size' => $this->file_size,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'width' => $this->width,
            'height' => $this->height,
            'created_by' => $this->created_by,
        ]);

        // Filtrowanie po serii
        $query->andFilterWhere(['like', 'series', $this->series]);

        // Date filtering for created_at
        if (!empty($this->created_at)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->created_at)) {
                $timestamp = strtotime($this->created_at);
                $nextDay = $timestamp + 86400;
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