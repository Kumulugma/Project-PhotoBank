<?php

use yii\db\Migration;

class m250521_203126_add_import_directory_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();
        
        // Sprawdź czy ustawienie już istnieje
        $existingSettings = $this->db->createCommand('SELECT `key` FROM {{%settings}} WHERE `key` = :key', [
            ':key' => 'upload.import_directory'
        ])->queryColumn();
        
        if (empty($existingSettings)) {
            // Dodaj nowe ustawienie
            $this->insert('{{%settings}}', [
                'key' => 'upload.import_directory',
                'value' => 'uploads/import',
                'description' => 'Domyślny katalog do importu zdjęć',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            
            echo "Dodano ustawienie: upload.import_directory\n";
        } else {
            echo "Ustawienie upload.import_directory już istnieje, pomijam\n";
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%settings}}', [
            'key' => 'upload.import_directory'
        ]);
        
        echo "Usunięto ustawienie: upload.import_directory\n";
        
        return true;
    }
}
