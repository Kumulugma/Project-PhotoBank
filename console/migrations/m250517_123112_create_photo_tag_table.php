<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photo_tag}}`.
 */
class m250517_123112_create_photo_tag_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%photo_tag}}', [
            'photo_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'PRIMARY KEY(photo_id, tag_id)',
        ]);

        // Dodanie kluczy obcych
        $this->addForeignKey('fk-photo_tag-photo_id', '{{%photo_tag}}', 'photo_id', '{{%photo}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-photo_tag-tag_id', '{{%photo_tag}}', 'tag_id', '{{%tag}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-photo_tag-tag_id', '{{%photo_tag}}');
        $this->dropForeignKey('fk-photo_tag-photo_id', '{{%photo_tag}}');
        $this->dropTable('{{%photo_tag}}');
    }
}
