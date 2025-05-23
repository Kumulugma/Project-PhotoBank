<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form with Polish error messages and audit logging
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;

    public function rules()
    {
        return [
            [['username', 'password'], 'required', 'message' => '{attribute} nie może być puste.'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Nazwa użytkownika',
            'password' => 'Hasło',
            'rememberMe' => 'Zapamiętaj mnie',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                // Loguj nieudaną próbę logowania
                if ($user) {
                    AuditLog::logLogin($user, false);
                } else {
                    AuditLog::log(AuditLog::ACTION_LOGIN, 
                        "Nieudana próba logowania dla nieistniejącego użytkownika: {$this->username}", 
                        ['severity' => AuditLog::SEVERITY_WARNING]
                    );
                }
                
                $this->addError($attribute, 'Nieprawidłowa nazwa użytkownika lub hasło.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $result = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            
            if ($result) {
                $user->updateLastLogin();
                
                // Loguj udane logowanie
                AuditLog::logLogin($user, true);
            }
            
            return $result;
        }
        
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    public function generateAttributeLabel($name)
    {
        $labels = $this->attributeLabels();
        return isset($labels[$name]) ? $labels[$name] : parent::generateAttributeLabel($name);
    }
}