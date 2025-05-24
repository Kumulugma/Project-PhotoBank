<?php

use yii\db\Migration;

class m250524_140104_add_english_description_to_photo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%photo}}', 'english_description', $this->text()->after('description'));
        
        // Dodaj indeks dla przyszłych wyszukiwań
        $this->createIndex(
            'idx-photo-english_description',
            '{{%photo}}',
            'english_description'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-photo-english_description', '{{%photo}}');
        $this->dropColumn('{{%photo}}', 'english_description');
    }
}
