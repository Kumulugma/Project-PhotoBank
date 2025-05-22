<?php

use yii\db\Migration;

class m250522_123315_add_series_to_photo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%photo}}', 'series', $this->string(50)->null()->after('description'));
        
        // Dodaj indeks dla lepszej wydajności wyszukiwania
        $this->createIndex('idx-photo-series', '{{%photo}}', 'series');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-photo-series', '{{%photo}}');
        $this->dropColumn('{{%photo}}', 'series');
    }
}
