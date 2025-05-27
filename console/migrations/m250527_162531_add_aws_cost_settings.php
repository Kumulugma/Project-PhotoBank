<?php

use yii\db\Migration;

class m250527_162531_add_aws_cost_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $settings = [
            [
                'key' => 'aws.cost_enabled',
                'value' => '0',
                'description' => 'Włącz integrację z AWS Cost Explorer'
            ],
            [
                'key' => 'aws.cost_access_key_id',
                'value' => '',
                'description' => 'AWS Access Key ID dla Cost Explorer'
            ],
            [
                'key' => 'aws.cost_secret_access_key',
                'value' => '',
                'description' => 'AWS Secret Access Key dla Cost Explorer'
            ],
            [
                'key' => 'aws.cost_region',
                'value' => 'us-east-1',
                'description' => 'Region AWS dla Cost Explorer'
            ],
            [
                'key' => 'aws.cost_cache_duration',
                'value' => '3600',
                'description' => 'Czas cache dla danych kosztów (w sekundach)'
            ],
            [
                'key' => 'aws.cost_monthly_budget',
                'value' => '100',
                'description' => 'Miesięczny budżet AWS (USD)'
            ],
            [
                'key' => 'aws.cost_alert_threshold',
                'value' => '80',
                'description' => 'Próg alertu kosztów (% budżetu)'
            ]
        ];

        foreach ($settings as $setting) {
            // Sprawdź czy ustawienie już istnieje
            $exists = $this->db->createCommand(
                'SELECT COUNT(*) FROM {{%settings}} WHERE [[key]] = :key'
            )->bindValue(':key', $setting['key'])->queryScalar();

            if (!$exists) {
                $this->insert('{{%settings}}', array_merge($setting, [
                    'created_at' => time(),
                    'updated_at' => time()
                ]));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $keys = [
            'aws.cost_enabled',
            'aws.cost_access_key_id',
            'aws.cost_secret_access_key',
            'aws.cost_region',
            'aws.cost_cache_duration',
            'aws.cost_monthly_budget',
            'aws.cost_alert_threshold'
        ];

        foreach ($keys as $key) {
            $this->delete('{{%settings}}', ['key' => $key]);
        }
    }
}
