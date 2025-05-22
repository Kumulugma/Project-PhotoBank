<?php

use yii\db\Migration;

class m250522_124330_add_exif_settings extends Migration
{
    public function safeUp()
    {
        $time = time();
        
        // Dodaj kolumnę exif_data do tabeli photo
        $this->addColumn('{{%photo}}', 'exif_data', $this->text());
        
        // Lista ustawień EXIF do dodania - prawa autorskie na początku
        $exifSettings = [
            // Prawa autorskie - najważniejsze
            ['gallery.exif_show_copyright', '1', 'Wyświetlaj prawa autorskie i autora'],
            ['gallery.exif_show_author_info', '1', 'Wyświetlaj dodatkowe informacje o autorze (opis, ID)'],
            
            // Pozostałe dane EXIF
            ['gallery.exif_show_camera', '1', 'Wyświetlaj dane aparatu (marka, model)'],
            ['gallery.exif_show_lens', '1', 'Wyświetlaj dane obiektywu'],
            ['gallery.exif_show_exposure', '1', 'Wyświetlaj ustawienia ekspozycji (ISO, przysłona, czas)'],
            ['gallery.exif_show_datetime', '1', 'Wyświetlaj datę i czas wykonania zdjęcia'],
            ['gallery.exif_show_flash', '1', 'Wyświetlaj informacje o fleszu'],
            ['gallery.exif_show_dimensions', '1', 'Wyświetlaj wymiary oryginalne'],
            ['gallery.exif_show_gps', '0', 'Wyświetlaj dane GPS (lokalizacja) - uwaga: może naruszać prywatność'],
            ['gallery.exif_show_software', '0', 'Wyświetlaj oprogramowanie użyte do obróbki'],
            ['gallery.exif_show_technical', '0', 'Wyświetlaj zaawansowane dane techniczne'],
        ];
        
        // Pobierz istniejące klucze
        $existingSettings = $this->db->createCommand('SELECT `key` FROM {{%settings}}')->queryColumn();
        
        // Dodaj tylko brakujące ustawienia
        foreach ($exifSettings as $setting) {
            list($key, $value, $description) = $setting;
            
            if (!in_array($key, $existingSettings)) {
                $this->insert('{{%settings}}', [
                    'key' => $key,
                    'value' => $value,
                    'description' => $description,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
                
                echo "Dodano ustawienie: $key\n";
            } else {
                echo "Ustawienie $key już istnieje, pomijam\n";
            }
        }
        
        return true;
    }

    public function safeDown()
    {
        // Usuń kolumnę exif_data
        $this->dropColumn('{{%photo}}', 'exif_data');
        
        // Usuń ustawienia EXIF
        $exifKeys = [
            'gallery.exif_show_copyright',
            'gallery.exif_show_author_info',
            'gallery.exif_show_camera',
            'gallery.exif_show_lens', 
            'gallery.exif_show_exposure',
            'gallery.exif_show_datetime',
            'gallery.exif_show_gps',
            'gallery.exif_show_flash',
            'gallery.exif_show_dimensions',
            'gallery.exif_show_software',
            'gallery.exif_show_technical',
        ];
        
        foreach ($exifKeys as $key) {
            $this->delete('{{%settings}}', ['key' => $key]);
            echo "Usunięto ustawienie: $key\n";
        }
        
        return true;
    }
}
