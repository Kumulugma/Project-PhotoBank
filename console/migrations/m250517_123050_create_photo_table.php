<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photo}}`.
 */
class m250517_123050_create_photo_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%photo}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'file_name' => $this->string()->notNull(),
            'file_size' => $this->integer()->notNull(),
            'mime_type' => $this->string()->notNull(),
            's3_path' => $this->string(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'is_public' => $this->boolean()->defaultValue(false),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ]);

        // Dodanie indeksów dla wydajniejszych zapytań
        $this->createIndex('idx-photo-status', '{{%photo}}', 'status');
        $this->createIndex('idx-photo-is_public', '{{%photo}}', 'is_public');
        $this->createIndex('idx-photo-created_by', '{{%photo}}', 'created_by');
        
        // Dodanie klucza obcego
        $this->addForeignKey('fk-photo-created_by', '{{%photo}}', 'created_by', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-photo-created_by', '{{%photo}}');
        $this->dropIndex('idx-photo-created_by', '{{%photo}}');
        $this->dropIndex('idx-photo-is_public', '{{%photo}}');
        $this->dropIndex('idx-photo-status', '{{%photo}}');
        $this->dropTable('{{%photo}}');
    }
}
