<?php

use yii\db\Migration;

class m250520_202206_populate_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();
        
        // Lista wszystkich ustawień, które powinny być w systemie
        $allSettings = [
            // General settings
            ['general.site_name', 'Zasobnik B', 'Nazwa witryny'],
            ['general.admin_email', 'admin@example.com', 'Adres email administratora'],
            ['general.support_email', 'support@example.com', 'Adres email wsparcia'],
            ['general.sender_email', 'noreply@example.com', 'Adres email nadawcy wiadomości systemowych'],
            ['general.sender_name', 'Zasobnik B', 'Nazwa nadawcy wiadomości systemowych'],
            ['general.pagination_size', '20', 'Domyślna liczba elementów na stronę'],
            ['general.timezone', 'Europe/Warsaw', 'Domyślna strefa czasowa'],
            
            // Upload settings
            ['upload.max_size', '100', 'Maksymalny rozmiar przesyłanego pliku w MB'],
            ['upload.allowed_types', 'image/jpeg,image/png,image/gif', 'Dozwolone typy MIME dla przesyłanych plików'],
            ['upload.auto_approve', '0', 'Automatyczne zatwierdzanie przesłanych zdjęć (0 - wyłączone, 1 - włączone)'],
            ['upload.auto_publish', '0', 'Automatyczne publikowanie przesłanych zdjęć (0 - wyłączone, 1 - włączone)'],
            ['upload.enable_chunking', '1', 'Włącz przesyłanie podzielone na części (0 - wyłączone, 1 - włączone)'],
            ['upload.chunk_size', '1', 'Rozmiar części w MB dla przesyłania podzielonego na części'],
            
            // S3 settings
            ['s3.bucket', '', 'Nazwa bucketu S3'],
            ['s3.region', 'eu-central-1', 'Region S3'],
            ['s3.access_key', '', 'Klucz dostępu S3'],
            ['s3.secret_key', '', 'Klucz sekretny S3'],
            ['s3.directory', 'photos', 'Katalog S3 dla zdjęć'],
            ['s3.deleted_directory', 'deleted', 'Katalog S3 dla usuniętych zdjęć'],
            
            // Watermark settings
            ['watermark.type', 'text', 'Typ znaku wodnego (text lub image)'],
            ['watermark.text', 'Zasobnik B', 'Tekst znaku wodnego'],
            ['watermark.image', '', 'Plik obrazu znaku wodnego'],
            ['watermark.position', 'bottom-right', 'Pozycja znaku wodnego'],
            ['watermark.opacity', '0.7', 'Przezroczystość znaku wodnego (0-1)'],
            
            // AI settings
            ['ai.provider', '', 'Dostawca AI (aws, google, openai)'],
            ['ai.api_key', '', 'Klucz API AI'],
            ['ai.region', '', 'Region AI (dla AWS)'],
            ['ai.model', '', 'Model AI (dla OpenAI)'],
            ['ai.enabled', '0', 'Integracja AI włączona (0 - wyłączona, 1 - włączona)'],
            
            // Email settings
            ['email.use_smtp', '0', 'Używaj SMTP do wysyłania e-maili (0 - wyłączone, 1 - włączone)'],
            ['email.smtp_host', '', 'Host SMTP'],
            ['email.smtp_port', '587', 'Port SMTP'],
            ['email.smtp_username', '', 'Nazwa użytkownika SMTP'],
            ['email.smtp_password', '', 'Hasło SMTP'],
            ['email.smtp_encryption', 'tls', 'Szyfrowanie SMTP (tls, ssl)'],
            
            // Gallery settings
            ['gallery.show_exif', '1', 'Pokaż dane EXIF (0 - wyłączone, 1 - włączone)'],
            ['gallery.enable_comments', '1', 'Włącz komentarze (0 - wyłączone, 1 - włączone)'],
            ['gallery.enable_likes', '1', 'Włącz polubienia (0 - wyłączone, 1 - włączone)'],
            ['gallery.items_per_page', '12', 'Liczba zdjęć na stronę w galerii'],
            ['gallery.thumbnail_size', 'medium', 'Domyślny rozmiar miniatury w galerii'],
            ['gallery.enable_download', '1', 'Włącz pobieranie oryginalnych zdjęć (0 - wyłączone, 1 - włączone)'],
            ['gallery.enable_social_sharing', '1', 'Włącz przyciski udostępniania w mediach społecznościowych (0 - wyłączone, 1 - włączone)'],
            
            // Security settings
            ['security.password_reset_token_expire', '3600', 'Czas wygaśnięcia tokenu resetowania hasła w sekundach'],
            ['security.password_min_length', '8', 'Minimalna długość hasła'],
            ['security.jwt_secret_key', 'twoj_tajny_klucz_jwt', 'Tajny klucz JWT'],
            ['security.jwt_expire', '86400', 'Czas wygaśnięcia tokenu JWT w sekundach'],
        ];
        
        // Pobierz wszystkie istniejące klucze z tabeli settings
        $existingSettings = $this->db->createCommand('SELECT `key` FROM {{%settings}}')->queryColumn();
        
        // Dodaj tylko brakujące ustawienia
        foreach ($allSettings as $setting) {
            list($key, $value, $description) = $setting;
            
            // Sprawdź czy ustawienie już istnieje
            if (!in_array($key, $existingSettings)) {
                // Dodaj tylko jeśli nie istnieje
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
                
                // Opcjonalnie - aktualizacja tylko opisu, jeśli brakuje
                $settingWithoutDesc = $this->db->createCommand('SELECT id FROM {{%settings}} WHERE `key`=:key AND (description IS NULL OR description = "")', [':key' => $key])->queryOne();
                if ($settingWithoutDesc) {
                    $this->update('{{%settings}}', [
                        'description' => $description,
                        'updated_at' => $time,
                    ], ['id' => $settingWithoutDesc['id']]);
                    echo "Zaktualizowano opis dla: $key\n";
                }
            }
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250517_125300_add_missing_settings nie może być cofnięta.\n";

        return false;
    }
}
