<?php

namespace common\models\search;

use common\models\Photo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PhotoSearch represents the model behind the search form of `common\models\Photo`.
 */
class PhotoSearch extends Photo
{
    public $has_copyright;
    public $stock_filter;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file_size', 'status', 'is_public', 'width', 'height', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['uploaded_to_shutterstock', 'uploaded_to_adobe_stock', 'used_in_private_project', 'is_ai_generated'], 'boolean'],
            [['title', 'description', 'english_description', 'series', 'file_name', 'mime_type', 's3_path', 'search_code', 'exif_data', 'ai_prompt', 'ai_generator_url'], 'safe'],
            [['has_copyright', 'stock_filter'], 'safe'],
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

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
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
            'file_size' => $this->file_size,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'uploaded_to_shutterstock' => $this->uploaded_to_shutterstock,
            'uploaded_to_adobe_stock' => $this->uploaded_to_adobe_stock,
            'used_in_private_project' => $this->used_in_private_project,
            'is_ai_generated' => $this->is_ai_generated,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'english_description', $this->english_description])
            ->andFilterWhere(['like', 'series', $this->series])
            ->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'mime_type', $this->mime_type])
            ->andFilterWhere(['like', 's3_path', $this->s3_path])
            ->andFilterWhere(['like', 'search_code', $this->search_code])
            ->andFilterWhere(['like', 'exif_data', $this->exif_data])
            ->andFilterWhere(['like', 'ai_prompt', $this->ai_prompt])
            ->andFilterWhere(['like', 'ai_generator_url', $this->ai_generator_url]);

        // Handle copyright filter
        if ($this->has_copyright !== null && $this->has_copyright !== '') {
            if ($this->has_copyright == 1) {
                // Photos with copyright info
                $query->andWhere(['or',
                    ['like', 'exif_data', '"Copyright"'],
                    ['like', 'exif_data', '"Artist"'],
                    ['like', 'exif_data', '"UserComment"'],
                ]);
            } else {
                // Photos without copyright info
                $query->andWhere(['and',
                    ['not like', 'exif_data', '"Copyright"'],
                    ['not like', 'exif_data', '"Artist"'],
                    ['not like', 'exif_data', '"UserComment"'],
                ]);
            }
        }

        // Handle stock/AI filter
        if ($this->stock_filter !== null && $this->stock_filter !== '') {
            switch ($this->stock_filter) {
                case 'ai':
                    $query->andWhere(['is_ai_generated' => 1]);
                    break;
                case 'shutterstock':
                    $query->andWhere(['uploaded_to_shutterstock' => 1]);
                    break;
                case 'adobe':
                    $query->andWhere(['uploaded_to_adobe_stock' => 1]);
                    break;
                case 'private':
                    $query->andWhere(['used_in_private_project' => 1]);
                    break;
                case 'stock_any':
                    $query->andWhere(['or',
                        ['uploaded_to_shutterstock' => 1],
                        ['uploaded_to_adobe_stock' => 1]
                    ]);
                    break;
                case 'unused':
                    $query->andWhere(['and',
                        ['uploaded_to_shutterstock' => 0],
                        ['uploaded_to_adobe_stock' => 0],
                        ['used_in_private_project' => 0],
                        ['is_ai_generated' => 0]
                    ]);
                    break;
            }
        }

        // Handle date filtering
        if (!empty($this->created_at)) {
            // Convert date string to timestamp range
            $date = strtotime($this->created_at);
            if ($date !== false) {
                $startOfDay = strtotime('midnight', $date);
                $endOfDay = strtotime('tomorrow midnight', $date) - 1;
                $query->andFilterWhere(['between', 'created_at', $startOfDay, $endOfDay]);
            }
        }

        return $dataProvider;
    }
}