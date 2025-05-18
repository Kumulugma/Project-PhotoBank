<?php
namespace common\components;

use Yii;
use yii\base\Component;

/**
 * Komponent do zarządzania rolami i uprawnieniami
 */
class RbacComponent extends Component
{
    /**
     * Inicjalizacja komponentu
     */
    public function init()
    {
        parent::init();
    }
    
    /**
     * Inicjalizuje role i uprawnienia w systemie
     * 
     * @return bool Sukces
     */
    public function initRoles()
    {
        $auth = Yii::$app->authManager;
        
        // Czyszczenie istniejących ról i uprawnień
        $auth->removeAllRoles();
        $auth->removeAllPermissions();
        
        // Dodawanie uprawnień
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
        
        // Tworzenie ról
        $user = $auth->createRole('user');
        $auth->add($user);
        $auth->addChild($user, $viewPhotos);
        
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $user);
        $auth->addChild($admin, $managePhotos);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $manageSettings);
        
        return true;
    }
    
    /**
     * Sprawdza czy użytkownik ma dostęp do zdjęcia
     * 
     * @param \common\models\Photo $photo Zdjęcie
     * @param int $userId ID użytkownika (null = aktualnie zalogowany)
     * @param bool $requireOwnership Czy wymagać własności
     * @return bool Czy użytkownik ma dostęp
     */
    public function checkPhotoAccess($photo, $userId = null, $requireOwnership = false)
    {
        if ($userId === null) {
            $userId = Yii::$app->user->id;
        }
        
        // Administrator ma dostęp do wszystkiego
        if (Yii::$app->user->can('managePhotos')) {
            return true;
        }
        
        // Właściciel ma dostęp do swojego zdjęcia
        $isOwner = $photo->created_by === $userId;
        
        if ($isOwner) {
            return true;
        }
        
        // Jeśli wymagana własność, a użytkownik nie jest właścicielem
        if ($requireOwnership) {
            return false;
        }
        
        // Sprawdzenie czy zdjęcie jest publiczne
        return (bool)$photo->is_public;
    }
    
    /**
     * Przypisuje rolę do użytkownika
     * 
     * @param int $userId ID użytkownika
     * @param string $roleName Nazwa roli
     * @return bool Sukces
     */
    public function assignRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        
        if (!$role) {
            return false;
        }
        
        try {
            $auth->assign($role, $userId);
            return true;
        } catch (\Exception $e) {
            Yii::error('Błąd przypisywania roli: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Usuwa rolę z użytkownika
     * 
     * @param int $userId ID użytkownika
     * @param string $roleName Nazwa roli
     * @return bool Sukces
     */
    public function revokeRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        
        if (!$role) {
            return false;
        }
        
        try {
            $auth->revoke($role, $userId);
            return true;
        } catch (\Exception $e) {
            Yii::error('Błąd usuwania roli: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Pobiera wszystkie role użytkownika
     * 
     * @param int $userId ID użytkownika
     * @return array Nazwy ról
     */
    public function getUserRoles($userId)
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($userId);
        
        return array_keys($roles);
    }
}