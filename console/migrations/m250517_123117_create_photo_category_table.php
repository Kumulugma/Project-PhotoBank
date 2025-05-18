<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photo_category}}`.
 */
class m250517_123117_create_photo_category_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%photo_category}}', [
            'photo_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'PRIMARY KEY(photo_id, category_id)',
        ]);

        // Dodanie kluczy obcych
        $this->addForeignKey('fk-photo_category-photo_id', '{{%photo_category}}', 'photo_id', '{{%photo}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-photo_category-category_id', '{{%photo_category}}', 'category_id', '{{%category}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-photo_category-category_id', '{{%photo_category}}');
        $this->dropForeignKey('fk-photo_category-photo_id', '{{%photo_category}}');
        $this->dropTable('{{%photo_category}}');
    }
}
