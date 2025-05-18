<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%thumbnail_size}}`.
 */
class m250517_123127_create_thumbnail_size_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%thumbnail_size}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'width' => $this->integer()->notNull(),
            'height' => $this->integer()->notNull(),
            'crop' => $this->boolean()->defaultValue(false),
            'watermark' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-thumbnail_size-name', '{{%thumbnail_size}}', 'name', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-thumbnail_size-name', '{{%thumbnail_size}}');
        $this->dropTable('{{%thumbnail_size}}');
    }
}
