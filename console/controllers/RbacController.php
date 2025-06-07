<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\rbac\ManagerInterface;

/**
 * RBAC management console controller
 */
class RbacController extends Controller
{
    /**
     * Initialize RBAC system with basic roles and permissions
     * 
     * @return int Exit code
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        
        // Remove all existing data
        $auth->removeAll();
        
        // Create permissions
        $managePhotos = $auth->createPermission('managePhotos');
        $managePhotos->description = 'Manage photos';
        $auth->add($managePhotos);
        
        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $auth->add($manageUsers);
        
        $manageSettings = $auth->createPermission('manageSettings');
        $manageSettings->description = 'Manage system settings';
        $auth->add($manageSettings);
        
        $viewReports = $auth->createPermission('viewReports');
        $viewReports->description = 'View reports and analytics';
        $auth->add($viewReports);
        
        // Create roles
        $user = $auth->createRole('user');
        $user->description = 'Regular user';
        $auth->add($user);
        
        $moderator = $auth->createRole('moderator');
        $moderator->description = 'Photo moderator';
        $auth->add($moderator);
        $auth->addChild($moderator, $managePhotos);
        $auth->addChild($moderator, $viewReports);
        
        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator';
        $auth->add($admin);
        $auth->addChild($admin, $managePhotos);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $manageSettings);
        $auth->addChild($admin, $viewReports);
        
        $this->stdout("RBAC system initialized successfully.\n", \yii\helpers\Console::FG_GREEN);
        
        return ExitCode::OK;
    }
    
    /**
     * Assign role to user
     * 
     * @param string $role Role name
     * @param int $userId User ID
     * @return int Exit code
     */
    public function actionAssign($role, $userId)
    {
        $auth = Yii::$app->authManager;
        
        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            $this->stdout("Role '$role' not found.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }
        
        $user = \common\models\User::findOne($userId);
        if (!$user) {
            $this->stdout("User with ID '$userId' not found.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }
        
        $auth->assign($roleObject, $userId);
        
        $this->stdout("Role '$role' assigned to user '{$user->username}' (ID: $userId).\n", \yii\helpers\Console::FG_GREEN);
        
        return ExitCode::OK;
    }
    
    /**
     * List all roles and permissions
     * 
     * @return int Exit code
     */
    public function actionList()
    {
        $auth = Yii::$app->authManager;
        
        $this->stdout("=== ROLES ===\n", \yii\helpers\Console::BOLD);
        foreach ($auth->getRoles() as $role) {
            $this->stdout("- {$role->name}: {$role->description}\n");
        }
        
        $this->stdout("\n=== PERMISSIONS ===\n", \yii\helpers\Console::BOLD);
        foreach ($auth->getPermissions() as $permission) {
            $this->stdout("- {$permission->name}: {$permission->description}\n");
        }
        
        return ExitCode::OK;
    }
}