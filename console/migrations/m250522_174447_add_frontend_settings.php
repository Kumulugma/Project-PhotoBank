<?php

use yii\db\Migration;

class m250522_174447_add_frontend_settings extends Migration
{
        /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Dodaj ustawienia dla trybu frontendowego
        $this->batchInsert('{{%settings}}', ['key', 'value', 'description', 'created_at', 'updated_at'], [
            [
                'upload.frontend_mode',
                '0',
                'Czy zapisywać pliki bezpośrednio do katalogu frontendu (1 = tak, 0 = nie)',
                time(),
                time()
            ],
            [
                'upload.frontend_path',
                '',
                'Ścieżka do katalogu frontendu (np. /var/www/frontend/web)',
                time(),
                time()
            ],
            [
                'upload.frontend_url',
                '',
                'URL frontendu (np. https://gallery.example.com)',
                time(),
                time()
            ],
            [
                'upload.preserve_original_names',
                '1',
                'Czy zachowywać oryginalne nazwy plików z hashem (1 = tak, 0 = nie)',
                time(),
                time()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Usuń dodane ustawienia
        $keys = [
            'upload.frontend_mode',
            'upload.frontend_path',
            'upload.frontend_url',
            'upload.preserve_original_names'
        ];

        $this->delete('{{%settings}}', ['in', 'key', $keys]);
    }
}
