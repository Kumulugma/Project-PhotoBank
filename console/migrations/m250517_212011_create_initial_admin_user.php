<?php

use yii\db\Migration;
use common\models\User;

class m250517_212011_create_initial_admin_user extends Migration
{
        /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Utworzenie pierwszego administratora
        $user = new User();
        $user->username = 'admin';
        $user->email = 'admin@example.com';
        $user->setPassword('admin123'); // Zmień na bezpieczne hasło
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = time();
        $user->updated_at = time();
        
        if ($user->save()) {
            // Przypisanie roli administratora
            $auth = Yii::$app->authManager;
            $adminRole = $auth->getRole('admin');
            if ($adminRole) {
                $auth->assign($adminRole, $user->id);
            }
            
            echo "Admin user created successfully.\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
            echo "Email: admin@example.com\n";
        } else {
            echo "Error creating admin user: " . json_encode($user->errors) . "\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Usuń użytkownika admin
        $user = User::findOne(['username' => 'admin']);
        if ($user) {
            // Usuń przypisania ról
            $auth = Yii::$app->authManager;
            $auth->revokeAll($user->id);
            
            // Usuń użytkownika
            $user->delete();
            echo "Admin user removed.\n";
        }
    }
}
