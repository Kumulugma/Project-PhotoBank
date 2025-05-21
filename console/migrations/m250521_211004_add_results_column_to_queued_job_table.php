<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%queued_job}}`.
 */
class m250521_211004_add_results_column_to_queued_job_table extends Migration
{
/**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dodaj kolumnę results jeśli nie istnieje
        if (!$this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('results')) {
            $this->addColumn('{{%queued_job}}', 'results', $this->text()->null()->comment('Wyniki przetwarzania zadania w formacie JSON'));
        }
        
        // Dodaj kolumnę error_message jeśli nie istnieje
        if (!$this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('error_message')) {
            $this->addColumn('{{%queued_job}}', 'error_message', $this->text()->null()->comment('Komunikat błędu'));
        }
        
        // Dodaj kolumnę attempts jeśli nie istnieje
        if (!$this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('attempts')) {
            $this->addColumn('{{%queued_job}}', 'attempts', $this->integer()->notNull()->defaultValue(0)->comment('Liczba prób wykonania zadania'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Usuń kolumny jeśli istnieją
        if ($this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('attempts')) {
            $this->dropColumn('{{%queued_job}}', 'attempts');
        }
        
        if ($this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('error_message')) {
            $this->dropColumn('{{%queued_job}}', 'error_message');
        }
        
        if ($this->getDb()->getSchema()->getTableSchema('{{%queued_job}}')->getColumn('results')) {
            $this->dropColumn('{{%queued_job}}', 'results');
        }
    }
}
