<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tag}}`.
 */
class m250517_123107_create_tag_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%tag}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'frequency' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-tag-name', '{{%tag}}', 'name', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-tag-name', '{{%tag}}');
        $this->dropTable('{{%tag}}');
    }
}
