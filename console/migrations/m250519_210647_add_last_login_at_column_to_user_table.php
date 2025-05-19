<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m250519_210647_add_last_login_at_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'last_login_at', $this->integer()->null()->comment('Timestamp ostatniego logowania'));
        
        // Dodaj indeks dla optymalizacji zapytań
        $this->createIndex('idx_user_last_login_at', '{{%user}}', 'last_login_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_user_last_login_at', '{{%user}}');
        $this->dropColumn('{{%user}}', 'last_login_at');
    }
}
