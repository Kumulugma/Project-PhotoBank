<?php

use yii\db\Migration;

class m250517_132926_initial_data extends Migration
{
    public function safeUp()
    {
        // Utworzenie konta administratora
        $auth = Yii::$app->authManager;
        $time = time();
        
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'email' => 'admin@example.com',
            'status' => 10, // STATUS_ACTIVE
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        $adminId = Yii::$app->db->getLastInsertID();
        
        // Tworzenie ról i uprawnień
        // (Ten fragment będzie działać tylko jeśli authManager jest już skonfigurowany)
        if ($auth) {
            // Uprawnienia
            $viewPhotos = $auth->createPermission('viewPhotos');
            $viewPhotos->description = 'Przeglądanie zdjęć (publicznych oraz własnych)';
            $auth->add($viewPhotos);
            
            $managePhotos = $auth->createPermission('managePhotos');
            $managePhotos->description = 'Zarządzanie wszystkimi zdjęciami';
            $auth->add($managePhotos);
            
            $manageUsers = $auth->createPermission('manageUsers');
            $manageUsers->description = 'Zarządzanie użytkownikami';
            $auth->add($manageUsers);
            
            $manageSettings = $auth->createPermission('manageSettings');
            $manageSettings->description = 'Zarządzanie ustawieniami systemu';
            $auth->add($manageSettings);
            
            // Role
            $user = $auth->createRole('user');
            $auth->add($user);
            $auth->addChild($user, $viewPhotos);
            
            $admin = $auth->createRole('admin');
            $auth->add($admin);
            $auth->addChild($admin, $user);
            $auth->addChild($admin, $managePhotos);
            $auth->addChild($admin, $manageUsers);
            $auth->addChild($admin, $manageSettings);
            
            // Przypisanie roli admin administratorowi
            $auth->assign($admin, $adminId);
        }
        
        // Dodanie domyślnych rozmiarów miniatur
        $thumbnailSizes = [
            ['name' => 'thumb', 'width' => 150, 'height' => 150, 'crop' => 1, 'watermark' => 0],
            ['name' => 'medium', 'width' => 400, 'height' => 300, 'crop' => 0, 'watermark' => 0],
            ['name' => 'large', 'width' => 800, 'height' => 600, 'crop' => 0, 'watermark' => 1],
        ];
        
        foreach ($thumbnailSizes as $size) {
            $this->insert('{{%thumbnail_size}}', array_merge($size, [
                'created_at' => $time,
                'updated_at' => $time,
            ]));
        }
        
        // Dodanie domyślnych ustawień
        $defaultSettings = [
            // Ustawienia znaku wodnego
            ['key' => 'watermark.type', 'value' => 'text', 'description' => 'Typ znaku wodnego (text/image)'],
            ['key' => 'watermark.text', 'value' => 'Zasobnik B', 'description' => 'Tekst znaku wodnego'],
            ['key' => 'watermark.position', 'value' => 'bottom-right', 'description' => 'Pozycja znaku wodnego'],
            ['key' => 'watermark.opacity', 'value' => '0.7', 'description' => 'Przezroczystość znaku wodnego (0-1)'],
            
            // Ustawienia S3
            ['key' => 's3.bucket', 'value' => '', 'description' => 'Nazwa bucketu S3'],
            ['key' => 's3.region', 'value' => 'eu-central-1', 'description' => 'Region AWS'],
            ['key' => 's3.directory', 'value' => 'photos', 'description' => 'Katalog dla zdjęć na S3'],
            ['key' => 's3.deleted_directory', 'value' => 'deleted', 'description' => 'Katalog dla usuniętych zdjęć na S3'],
            
            // Ustawienia AI
            ['key' => 'ai.provider', 'value' => 'openai', 'description' => 'Dostawca usługi AI (aws/google/openai)'],
            ['key' => 'ai.model', 'value' => 'gpt-4-vision-preview', 'description' => 'Model AI'],
            ['key' => 'ai.enabled', 'value' => '0', 'description' => 'Czy AI jest włączone'],
        ];
        
        foreach ($defaultSettings as $setting) {
            $this->insert('{{%settings}}', array_merge($setting, [
                'created_at' => $time,
                'updated_at' => $time,
            ]));
        }
    }

    public function safeDown()
    {
        // Usuwanie ustawień
        $this->delete('{{%settings}}');
        
        // Usuwanie rozmiarów miniatur
        $this->delete('{{%thumbnail_size}}');
        
        // Usuwanie ról RBAC (jeśli authManager jest skonfigurowany)
        $auth = Yii::$app->authManager;
        if ($auth) {
            $auth->removeAllAssignments();
            $auth->removeAllRoles();
            $auth->removeAllPermissions();
        }
        
        // Usuwanie użytkownika admin
        $this->delete('{{%user}}', ['username' => 'admin']);
    }
}
