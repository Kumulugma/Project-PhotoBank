<?php
// api/controllers/AuthController.php
namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use common\models\User;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['login', 'request-password-reset', 'reset-password'];
        return $behaviors;
    }
    
    public function actionLogin()
    {
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');
        
        // Sprawdzenie danych logowania
        $user = User::findByUsername($username);
        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException('Nieprawidłowe dane logowania');
        }
        
        // Generowanie tokenu JWT
        $token = JWT::encode([
            'sub' => $user->id,
            'roles' => $user->getRoles(),
            'exp' => time() + 86400
        ], Yii::$app->params['jwtSecretKey'], 'HS256');
        
        return [
            'token' => $token,
            'user' => $user->getAttributes(['id', 'username', 'email'])
        ];
    }
    
    public function actionLogout()
    {
        // Pobieranie tokenu z nagłówka
        $token = Yii::$app->request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $token);
        
        // Dodanie tokenu do czarnej listy z czasem wygaśnięcia
        Yii::$app->cache->set('jwt_blacklist_' . md5($token), true, 86400);
        
        return ['success' => true];
    }
    
    public function actionRequestPasswordReset()
    {
        $email = Yii::$app->request->post('email');
        
        $user = User::findOne(['email' => $email, 'status' => User::STATUS_ACTIVE]);
        if ($user) {
            $token = Yii::$app->security->generateRandomString() . '_' . time();
            $user->password_reset_token = $token;
            $user->save(false);
            
            $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $token]);
            Yii::$app->mailer->compose()
                ->setTo($email)
                ->setSubject('Reset hasła')
                ->setTextBody('Kliknij link aby zresetować hasło: ' . $resetLink)
                ->send();
        }
        
        return ['success' => true];
    }
    
    public function actionResetPassword()
    {
        $token = Yii::$app->request->post('token');
        $password = Yii::$app->request->post('password');
        
        // Parsowanie tokenu
        list($tokenValue, $timestamp) = explode('_', $token);
        $timestamp = (int) $timestamp;
        
        // Sprawdzenie czy token nie wygasł (1 godzina)
        if ($timestamp + 3600 < time()) {
            throw new BadRequestHttpException('Token wygasł');
        }
        
        $user = User::findOne(['password_reset_token' => $token, 'status' => User::STATUS_ACTIVE]);
        if (!$user) {
            throw new BadRequestHttpException('Nieprawidłowy token');
        }
        
        $user->setPassword($password);
        $user->password_reset_token = null;
        $user->updated_at = time();
        $user->save(false);
        
        return ['success' => true];
    }
}