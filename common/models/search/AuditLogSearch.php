<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AuditLog;

/**
 * AuditLogSearch represents the model behind the search form
 */
class AuditLogSearch extends AuditLog
{
    public $username;
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['id', 'model_id', 'user_id'], 'integer'],
            [['action', 'model_class', 'user_ip', 'user_agent', 'message', 'severity', 'username', 'date_from', 'date_to'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'username' => 'Nazwa użytkownika',
            'date_from' => 'Data od',
            'date_to' => 'Data do',
        ]);
    }

    public function search($params)
    {
        $query = AuditLog::find()
            ->joinWith('user')
            ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $dataProvider->sort->attributes['username'] = [
            'asc' => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'audit_log.id' => $this->id,
            'audit_log.model_id' => $this->model_id,
            'audit_log.user_id' => $this->user_id,
            'audit_log.action' => $this->action,
            'audit_log.severity' => $this->severity,
        ]);

        $query->andFilterWhere(['like', 'audit_log.model_class', $this->model_class])
            ->andFilterWhere(['like', 'audit_log.user_ip', $this->user_ip])
            ->andFilterWhere(['like', 'audit_log.message', $this->message])
            ->andFilterWhere(['like', 'user.username', $this->username]);

        // Filtrowanie dat
        if (!empty($this->date_from)) {
            $timestamp = strtotime($this->date_from . ' 00:00:00');
            if ($timestamp) {
                $query->andFilterWhere(['>=', 'audit_log.created_at', $timestamp]);
            }
        }

        if (!empty($this->date_to)) {
            $timestamp = strtotime($this->date_to . ' 23:59:59');
            if ($timestamp) {
                $query->andFilterWhere(['<=', 'audit_log.created_at', $timestamp]);
            }
        }

        return $dataProvider;
    }

    public static function getActionOptions()
    {
        return [
            AuditLog::ACTION_CREATE => 'Utworzenie',
            AuditLog::ACTION_UPDATE => 'Aktualizacja',
            AuditLog::ACTION_DELETE => 'Usunięcie',
            AuditLog::ACTION_LOGIN => 'Logowanie',
            AuditLog::ACTION_LOGOUT => 'Wylogowanie',
            AuditLog::ACTION_UPLOAD => 'Przesłanie',
            AuditLog::ACTION_APPROVE => 'Zatwierdzenie',
            AuditLog::ACTION_REJECT => 'Odrzucenie',
            AuditLog::ACTION_IMPORT => 'Import',
            AuditLog::ACTION_EXPORT => 'Eksport',
            AuditLog::ACTION_SYNC => 'Synchronizacja',
            AuditLog::ACTION_ACCESS => 'Dostęp',
            AuditLog::ACTION_SETTINGS => 'Ustawienia',
            AuditLog::ACTION_SYSTEM => 'System',
        ];
    }

    public static function getSeverityOptions()
    {
        return [
            AuditLog::SEVERITY_INFO => 'Informacja',
            AuditLog::SEVERITY_WARNING => 'Ostrzeżenie',
            AuditLog::SEVERITY_ERROR => 'Błąd',
            AuditLog::SEVERITY_SUCCESS => 'Sukces',
        ];
    }

    public static function getModelClassOptions()
    {
        return [
            'common\models\Photo' => 'Zdjęcie',
            'common\models\User' => 'Użytkownik',
            'common\models\Category' => 'Kategoria',
            'common\models\Tag' => 'Tag',
            'common\models\Settings' => 'Ustawienie',
            'common\models\ThumbnailSize' => 'Rozmiar miniatury',
            'common\models\QueuedJob' => 'Zadanie',
        ];
    }
}