<?php

use yii\db\Migration;

class m250522_055448_add_search_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dodanie kolumny search_code do tabeli photo (początkowo nullable)
        $this->addColumn('{{%photo}}', 'search_code', $this->string(12)->null()->after('s3_path'));
        
        // Generowanie kodów dla istniejących rekordów PRZED utworzeniem indeksu
        $this->generateSearchCodesForExistingPhotos();
        
        // Teraz zmień kolumnę na NOT NULL (po wygenerowaniu kodów)
        $this->alterColumn('{{%photo}}', 'search_code', $this->string(12)->notNull());
        
        // Dodanie unikalnego indeksu dla pola search_code (po wypełnieniu wszystkich rekordów)
        $this->createIndex(
            'idx_photo_search_code',
            '{{%photo}}',
            'search_code',
            true // unique
        );
        
        echo "    > Pole search_code zostało dodane do tabeli photo\n";
        echo "    > Wygenerowano kody dla istniejących zdjęć\n";
        echo "    > Utworzono unikalny indeks idx_photo_search_code\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Usunięcie indeksu
        $this->dropIndex('idx_photo_search_code', '{{%photo}}');
        
        // Usunięcie kolumny
        $this->dropColumn('{{%photo}}', 'search_code');
        
        echo "    > Usunięto pole search_code z tabeli photo\n";
        echo "    > Usunięto indeks idx_photo_search_code\n";
    }
    
    /**
     * Generuje unikalne kody wyszukiwania dla istniejących zdjęć
     */
    private function generateSearchCodesForExistingPhotos()
    {
        // Pobierz wszystkie zdjęcia bez kodu (NULL lub puste)
        $photos = $this->db->createCommand("SELECT id FROM {{%photo}} WHERE search_code IS NULL OR search_code = ''")->queryAll();
        
        if (empty($photos)) {
            echo "    > Brak zdjęć wymagających wygenerowania kodów\n";
            return;
        }
        
        echo "    > Znaleziono " . count($photos) . " zdjęć wymagających wygenerowania kodów\n";
        
        $generatedCount = 0;
        $batchSize = 50; // Przetwarzaj w partiach
        
        foreach ($photos as $photo) {
            $photoId = $photo['id'];
            
            // Generuj unikalny kod z większą liczbą prób
            $maxAttempts = 100;
            $attempts = 0;
            
            do {
                $code = $this->generateRandomCode();
                $attempts++;
                
                if ($attempts > $maxAttempts) {
                    throw new \Exception("Nie można wygenerować unikalnego kodu dla zdjęcia ID: $photoId po $maxAttempts próbach");
                }
                
            } while ($this->codeExists($code));
            
            // Zaktualizuj rekord w bazie
            $this->db->createCommand()->update('{{%photo}}', [
                'search_code' => $code
            ], ['id' => $photoId])->execute();
            
            $generatedCount++;
            
            // Progress info co określoną liczbę rekordów
            if ($generatedCount % $batchSize === 0) {
                echo "    > Wygenerowano kody dla $generatedCount zdjęć...\n";
            }
        }
        
        echo "    > Wygenerowano kody dla łącznie $generatedCount zdjęć\n";
        
        // Sprawdzenie końcowe - czy wszystkie rekordy mają kody
        $remainingEmpty = $this->db->createCommand("SELECT COUNT(*) FROM {{%photo}} WHERE search_code IS NULL OR search_code = ''")->queryScalar();
        
        if ($remainingEmpty > 0) {
            throw new \Exception("Ostrzeżenie: $remainingEmpty zdjęć nadal nie ma kodów wyszukiwania!");
        }
        
        // Sprawdzenie unikalności
        $totalCodes = $this->db->createCommand("SELECT COUNT(*) FROM {{%photo}}")->queryScalar();
        $uniqueCodes = $this->db->createCommand("SELECT COUNT(DISTINCT search_code) FROM {{%photo}} WHERE search_code IS NOT NULL")->queryScalar();
        
        if ($totalCodes != $uniqueCodes) {
            throw new \Exception("Błąd: Wykryto duplikaty kodów! Wszystkich: $totalCodes, unikalnych: $uniqueCodes");
        }
        
        echo "    > Weryfikacja ukończona: wszystkie kody są unikalne\n";
    }
    
    /**
     * Generuje losowy 12-cyfrowy kod
     * 
     * @return string
     */
    private function generateRandomCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $code = '';
        
        // Użyj bardziej zaawansowanego generatora losowości
        for ($i = 0; $i < 12; $i++) {
            $code .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        
        return $code;
    }
    
    /**
     * Sprawdza czy kod już istnieje w bazie
     * 
     * @param string $code
     * @return bool
     */
    private function codeExists($code)
    {
        $count = $this->db->createCommand("SELECT COUNT(*) FROM {{%photo}} WHERE search_code = :code", [
            ':code' => $code
        ])->queryScalar();
        
        return $count > 0;
    }
}
