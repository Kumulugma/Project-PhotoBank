<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audit_log}}`.
 */
class m250523_220036_create_audit_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%audit_log}}', [
            'id' => $this->primaryKey(),
            'action' => $this->string(255)->notNull()->comment('Typ akcji (create, update, delete, login, etc.)'),
            'model_class' => $this->string(255)->null()->comment('Klasa modelu którego dotyczy akcja'),
            'model_id' => $this->integer()->null()->comment('ID rekordu którego dotyczy akcja'),
            'user_id' => $this->integer()->null()->comment('ID użytkownika wykonującego akcję'),
            'user_ip' => $this->string(45)->null()->comment('Adres IP użytkownika'),
            'user_agent' => $this->text()->null()->comment('User Agent przeglądarki'),
            'old_values' => $this->json()->null()->comment('Stare wartości (przed zmianą)'),
            'new_values' => $this->json()->null()->comment('Nowe wartości (po zmianie)'),
            'message' => $this->text()->null()->comment('Opisowa wiadomość o zdarzeniu'),
            'severity' => $this->string(20)->defaultValue('info')->comment('Poziom ważności (info, warning, error, success)'),
            'created_at' => $this->integer()->notNull()->comment('Timestamp utworzenia'),
        ]);

        // Indeksy dla lepszej wydajności
        $this->createIndex('idx_audit_log_created_at', '{{%audit_log}}', 'created_at');
        $this->createIndex('idx_audit_log_action', '{{%audit_log}}', 'action');
        $this->createIndex('idx_audit_log_user_id', '{{%audit_log}}', 'user_id');
        $this->createIndex('idx_audit_log_model', '{{%audit_log}}', ['model_class', 'model_id']);
        $this->createIndex('idx_audit_log_severity', '{{%audit_log}}', 'severity');
        $this->createIndex('idx_audit_log_user_ip', '{{%audit_log}}', 'user_ip');

        // Klucz obcy do tabeli użytkowników
        $this->addForeignKey(
            'fk_audit_log_user_id',
            '{{%audit_log}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Dodaj przykładowe wpisy
        $this->insert('{{%audit_log}}', [
            'action' => 'system',
            'message' => 'System dziennika zdarzeń został zainstalowany',
            'severity' => 'success',
            'created_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_audit_log_user_id', '{{%audit_log}}');
        $this->dropTable('{{%audit_log}}');
    }
}
