<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends Model
{
    public $id;
    public $username;
    public $email;
    public $status;
    public $role;
    public $last_login_at;
    public $created_at;
    public $updated_at;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'last_login_at'], 'integer'],
            [['username', 'email', 'role', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Nazwa użytkownika',
            'email' => 'Email',
            'status' => 'Status',
            'role' => 'Rola',
            'last_login_at' => 'Ostatnie logowanie',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
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
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);
        
        // Date range filtering for created_at
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
        
        // Date range filtering for updated_at
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

        // Text filtering
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);
            
        // Role filtering
        if (!empty($this->role)) {
            $auth = \Yii::$app->authManager;
            if ($auth) {
                $userIds = $auth->getUserIdsByRole($this->role);
                if (!empty($userIds)) {
                    $query->andWhere(['id' => $userIds]);
                } else {
                    $query->andWhere(['id' => -1]);
                }
            }
        }

        return $dataProvider;
    }
}