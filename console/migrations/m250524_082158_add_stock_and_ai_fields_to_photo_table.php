<?php

use yii\db\Migration;

class m250524_082158_add_stock_and_ai_fields_to_photo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dodaj pola dla platform stockowych
        $this->addColumn('{{%photo}}', 'uploaded_to_shutterstock', $this->boolean()->defaultValue(false)->after('exif_data'));
        $this->addColumn('{{%photo}}', 'uploaded_to_adobe_stock', $this->boolean()->defaultValue(false)->after('uploaded_to_shutterstock'));
        $this->addColumn('{{%photo}}', 'used_in_private_project', $this->boolean()->defaultValue(false)->after('uploaded_to_adobe_stock'));
        
        // Dodaj pola dla AI
        $this->addColumn('{{%photo}}', 'is_ai_generated', $this->boolean()->defaultValue(false)->after('used_in_private_project'));
        $this->addColumn('{{%photo}}', 'ai_prompt', $this->text()->null()->after('is_ai_generated'));
        $this->addColumn('{{%photo}}', 'ai_generator_url', $this->string()->null()->after('ai_prompt'));
        
        // Dodaj indeksy dla lepszej wydajności
        $this->createIndex('idx_photo_uploaded_to_shutterstock', '{{%photo}}', 'uploaded_to_shutterstock');
        $this->createIndex('idx_photo_uploaded_to_adobe_stock', '{{%photo}}', 'uploaded_to_adobe_stock');
        $this->createIndex('idx_photo_used_in_private_project', '{{%photo}}', 'used_in_private_project');
        $this->createIndex('idx_photo_is_ai_generated', '{{%photo}}', 'is_ai_generated');
        
        echo "    > Dodano pola stockowe i AI do tabeli photo\n";
        echo "    > Utworzono indeksy dla nowych pól\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Usuń indeksy
        $this->dropIndex('idx_photo_is_ai_generated', '{{%photo}}');
        $this->dropIndex('idx_photo_used_in_private_project', '{{%photo}}');
        $this->dropIndex('idx_photo_uploaded_to_adobe_stock', '{{%photo}}');
        $this->dropIndex('idx_photo_uploaded_to_shutterstock', '{{%photo}}');
        
        // Usuń kolumny
        $this->dropColumn('{{%photo}}', 'ai_generator_url');
        $this->dropColumn('{{%photo}}', 'ai_prompt');
        $this->dropColumn('{{%photo}}', 'is_ai_generated');
        $this->dropColumn('{{%photo}}', 'used_in_private_project');
        $this->dropColumn('{{%photo}}', 'uploaded_to_adobe_stock');
        $this->dropColumn('{{%photo}}', 'uploaded_to_shutterstock');
        
        echo "    > Usunięto pola stockowe i AI z tabeli photo\n";
    }
}
