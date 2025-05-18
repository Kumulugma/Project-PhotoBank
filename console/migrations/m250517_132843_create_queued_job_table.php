<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%queued_job}}`.
 */
class m250517_132843_create_queued_job_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%queued_job}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'data' => $this->text()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'error' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'started_at' => $this->integer(),
            'finished_at' => $this->integer(),
        ]);

        $this->createIndex('idx-queued_job-status', '{{%queued_job}}', 'status');
        $this->createIndex('idx-queued_job-type', '{{%queued_job}}', 'type');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-queued_job-type', '{{%queued_job}}');
        $this->dropIndex('idx-queued_job-status', '{{%queued_job}}');
        $this->dropTable('{{%queued_job}}');
    }
}
