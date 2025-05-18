<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%settings}}`.
 */
class m250517_123121_create_settings_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'value' => $this->text()->notNull(),
            'description' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-settings-key', '{{%settings}}', 'key', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-settings-key', '{{%settings}}');
        $this->dropTable('{{%settings}}');
    }
}
