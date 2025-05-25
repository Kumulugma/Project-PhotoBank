<?php

use yii\db\Migration;

class m250525_130940_add_ai_s3_counters_and_exif_settings extends Migration
{
        /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dodaj liczniki i limitery dla AI
        $this->insert('{{%settings}}', [
            'key' => 'ai.monthly_limit',
            'value' => '1000',
            'description' => 'Miesięczny limit zapytań AI',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
        $this->insert('{{%settings}}', [
            'key' => 'ai.current_count',
            'value' => '0',
            'description' => 'Bieżąca liczba wykorzystanych zapytań AI w tym miesiącu',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
        $this->insert('{{%settings}}', [
            'key' => 'ai.reset_date',
            'value' => date('Y-m-01'),
            'description' => 'Data ostatniego zresetowania licznika AI',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Dodaj liczniki i limitery dla S3
        $this->insert('{{%settings}}', [
            'key' => 's3.monthly_limit',
            'value' => '10000',
            'description' => 'Miesięczny limit operacji S3',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
        $this->insert('{{%settings}}', [
            'key' => 's3.current_count',
            'value' => '0',
            'description' => 'Bieżąca liczba wykorzystanych operacji S3 w tym miesiącu',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
        $this->insert('{{%settings}}', [
            'key' => 's3.reset_date',
            'value' => date('Y-m-01'),
            'description' => 'Data ostatniego zresetowania licznika S3',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Dodaj ustawienia EXIF autora/artysty
        $this->insert('{{%settings}}', [
            'key' => 'exif.default_artist',
            'value' => '',
            'description' => 'Domyślny artysta/autor do ustawienia w EXIF',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        
        $this->insert('{{%settings}}', [
            'key' => 'exif.default_copyright',
            'value' => '',
            'description' => 'Domyślne prawa autorskie do ustawienia w EXIF',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Dodaj ustawienie dla opisów angielskich w AI
        $this->insert('{{%settings}}', [
            'key' => 'ai.generate_english_descriptions',
            'value' => '1',
            'description' => 'Czy generować opisy w języku angielskim przez AI',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%settings}}', ['key' => 'ai.monthly_limit']);
        $this->delete('{{%settings}}', ['key' => 'ai.current_count']);
        $this->delete('{{%settings}}', ['key' => 'ai.reset_date']);
        $this->delete('{{%settings}}', ['key' => 's3.monthly_limit']);
        $this->delete('{{%settings}}', ['key' => 's3.current_count']);
        $this->delete('{{%settings}}', ['key' => 's3.reset_date']);
        $this->delete('{{%settings}}', ['key' => 'exif.default_artist']);
        $this->delete('{{%settings}}', ['key' => 'exif.default_copyright']);
        $this->delete('{{%settings}}', ['key' => 'ai.generate_english_descriptions']);
    }
}
