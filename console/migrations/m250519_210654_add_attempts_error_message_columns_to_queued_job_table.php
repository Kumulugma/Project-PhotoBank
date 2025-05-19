<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%queued_job}}`.
 */
class m250519_210654_add_attempts_error_message_columns_to_queued_job_table extends Migration
{
 /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%queued_job}}', 'attempts', $this->integer()->notNull()->defaultValue(0)->comment('Liczba prób wykonania zadania'));
        $this->addColumn('{{%queued_job}}', 'error_message', $this->text()->null()->comment('Komunikat błędu w przypadku niepowodzenia'));
        
        // Dodaj indeksy dla optymalizacji
        $this->createIndex('idx_queued_job_attempts', '{{%queued_job}}', 'attempts');
        $this->createIndex('idx_queued_job_status_attempts', '{{%queued_job}}', ['status', 'attempts']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_queued_job_status_attempts', '{{%queued_job}}');
        $this->dropIndex('idx_queued_job_attempts', '{{%queued_job}}');
        $this->dropColumn('{{%queued_job}}', 'error_message');
        $this->dropColumn('{{%queued_job}}', 'attempts');
    }
}
