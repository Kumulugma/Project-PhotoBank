<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * AuditLog model - Dziennik zdarzeń systemowych
 *
 * @property integer $id
 * @property string $action
 * @property string $model_class
 * @property integer $model_id
 * @property string $user_id
 * @property string $user_ip
 * @property string $user_agent
 * @property string $old_values
 * @property string $new_values
 * @property string $message
 * @property string $severity
 * @property integer $created_at
 */
class AuditLog extends ActiveRecord
{
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_SUCCESS = 'success';
    
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_UPLOAD = 'upload';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_IMPORT = 'import';
    const ACTION_EXPORT = 'export';
    const ACTION_SYNC = 'sync';
    const ACTION_ACCESS = 'access';
    const ACTION_SETTINGS = 'settings';
    const ACTION_SYSTEM = 'system';

    public static function tableName()
    {
        return '{{%audit_log}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ]
        ];
    }

    public function rules()
    {
        return [
            [['action'], 'required'],
            [['model_id', 'user_id', 'created_at'], 'integer'],
            [['old_values', 'new_values', 'message'], 'string'],
            [['action', 'model_class', 'user_ip', 'user_agent'], 'string', 'max' => 255],
            [['severity'], 'string', 'max' => 20],
            [['severity'], 'in', 'range' => [
                self::SEVERITY_INFO, 
                self::SEVERITY_WARNING, 
                self::SEVERITY_ERROR, 
                self::SEVERITY_SUCCESS
            ]],
            [['severity'], 'default', 'value' => self::SEVERITY_INFO],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'action' => 'Akcja',
            'model_class' => 'Model',
            'model_id' => 'ID Obiektu',
            'user_id' => 'Użytkownik',
            'user_ip' => 'Adres IP',
            'user_agent' => 'Przeglądarka',
            'old_values' => 'Stare wartości',
            'new_values' => 'Nowe wartości',
            'message' => 'Wiadomość',
            'severity' => 'Poziom',
            'created_at' => 'Data utworzenia',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function log($action, $message = null, $options = [])
    {
        $log = new self();
        $log->action = $action;
        $log->message = $message;
        $log->severity = $options['severity'] ?? self::SEVERITY_INFO;
        $log->model_class = $options['model_class'] ?? null;
        $log->model_id = $options['model_id'] ?? null;
        $log->old_values = isset($options['old_values']) ? json_encode($options['old_values']) : null;
        $log->new_values = isset($options['new_values']) ? json_encode($options['new_values']) : null;
        
        // Informacje o użytkowniku
        if (!Yii::$app->user->isGuest) {
            $log->user_id = Yii::$app->user->id;
        }
        
        // Informacje o żądaniu
        if (!Yii::$app instanceof \yii\console\Application) {
            $log->user_ip = Yii::$app->request->userIP;
            $log->user_agent = Yii::$app->request->userAgent;
        }
        
        $log->created_at = time();
        
        try {
            $log->save();
        } catch (\Exception $e) {
            Yii::error('Błąd zapisu dziennika zdarzeń: ' . $e->getMessage());
        }
        
        return $log;
    }

    public static function logModelAction($model, $action, $oldAttributes = null)
    {
        $modelClass = get_class($model);
        $modelId = $model->primaryKey;
        
        $message = self::generateModelMessage($model, $action);
        
        $options = [
            'model_class' => $modelClass,
            'model_id' => $modelId,
            'severity' => self::SEVERITY_INFO
        ];
        
        if ($oldAttributes !== null && $action === self::ACTION_UPDATE) {
            $options['old_values'] = $oldAttributes;
            $options['new_values'] = $model->attributes;
        }
        
        return self::log($action, $message, $options);
    }

    public static function logLogin($user, $success = true)
    {
        $message = $success 
            ? "Użytkownik {$user->username} zalogował się do systemu"
            : "Nieudana próba logowania dla użytkownika {$user->username}";
        
        $severity = $success ? self::SEVERITY_SUCCESS : self::SEVERITY_WARNING;
        
        return self::log(self::ACTION_LOGIN, $message, [
            'severity' => $severity,
            'model_class' => get_class($user),
            'model_id' => $user->id
        ]);
    }

    public static function logLogout($user)
    {
        $message = "Użytkownik {$user->username} wylogował się z systemu";
        
        return self::log(self::ACTION_LOGOUT, $message, [
            'severity' => self::SEVERITY_INFO,
            'model_class' => get_class($user),
            'model_id' => $user->id
        ]);
    }

    public static function logFileUpload($fileName, $fileSize, $success = true)
    {
        $message = $success 
            ? "Przesłano plik: {$fileName} (" . Yii::$app->formatter->asShortSize($fileSize) . ")"
            : "Błąd przesyłania pliku: {$fileName}";
        
        $severity = $success ? self::SEVERITY_SUCCESS : self::SEVERITY_ERROR;
        
        return self::log(self::ACTION_UPLOAD, $message, ['severity' => $severity]);
    }

    public static function logPhotoApproval($photo, $approved = true)
    {
        $action = $approved ? self::ACTION_APPROVE : self::ACTION_REJECT;
        $message = $approved 
            ? "Zatwierdzono zdjęcie: {$photo->title} (kod: {$photo->search_code})"
            : "Odrzucono zdjęcie: {$photo->title} (kod: {$photo->search_code})";
        
        return self::log($action, $message, [
            'severity' => self::SEVERITY_SUCCESS,
            'model_class' => get_class($photo),
            'model_id' => $photo->id
        ]);
    }

    public static function logSystemEvent($message, $severity = self::SEVERITY_INFO, $action = self::ACTION_SYSTEM)
    {
        return self::log($action, $message, ['severity' => $severity]);
    }

    public static function logSettingsChange($key, $oldValue, $newValue)
    {
        $message = "Zmieniono ustawienie: {$key}";
        
        return self::log(self::ACTION_SETTINGS, $message, [
            'severity' => self::SEVERITY_INFO,
            'old_values' => [$key => $oldValue],
            'new_values' => [$key => $newValue]
        ]);
    }

    private static function generateModelMessage($model, $action)
    {
        $modelName = self::getModelDisplayName(get_class($model));
        $identifier = self::getModelIdentifier($model);
        
        switch ($action) {
            case self::ACTION_CREATE:
                return "Utworzono {$modelName}: {$identifier}";
            case self::ACTION_UPDATE:
                return "Zaktualizowano {$modelName}: {$identifier}";
            case self::ACTION_DELETE:
                return "Usunięto {$modelName}: {$identifier}";
            default:
                return "Akcja {$action} dla {$modelName}: {$identifier}";
        }
    }

    private static function getModelDisplayName($className)
    {
        $classMap = [
            'common\models\Photo' => 'zdjęcie',
            'common\models\User' => 'użytkownika',
            'common\models\Category' => 'kategorię',
            'common\models\Tag' => 'tag',
            'common\models\Settings' => 'ustawienie',
            'common\models\ThumbnailSize' => 'rozmiar miniatury',
            'common\models\QueuedJob' => 'zadanie'
        ];
        
        return $classMap[$className] ?? basename(str_replace('\\', '/', $className));
    }

    private static function getModelIdentifier($model)
    {
        if (isset($model->title)) return $model->title;
        if (isset($model->name)) return $model->name;
        if (isset($model->username)) return $model->username;
        if (isset($model->search_code)) return $model->search_code;
        
        return "ID: {$model->primaryKey}";
    }

    public function getOldValuesArray()
    {
        return $this->old_values ? json_decode($this->old_values, true) : [];
    }

    public function getNewValuesArray()
    {
        return $this->new_values ? json_decode($this->new_values, true) : [];
    }

    public function getSeverityLabel()
    {
        $labels = [
            self::SEVERITY_INFO => 'Informacja',
            self::SEVERITY_WARNING => 'Ostrzeżenie',
            self::SEVERITY_ERROR => 'Błąd',
            self::SEVERITY_SUCCESS => 'Sukces'
        ];
        
        return $labels[$this->severity] ?? $this->severity;
    }

    public function getSeverityClass()
    {
        $classes = [
            self::SEVERITY_INFO => 'info',
            self::SEVERITY_WARNING => 'warning',
            self::SEVERITY_ERROR => 'danger',
            self::SEVERITY_SUCCESS => 'success'
        ];
        
        return $classes[$this->severity] ?? 'secondary';
    }

    public function getActionLabel()
    {
        $labels = [
            self::ACTION_CREATE => 'Utworzenie',
            self::ACTION_UPDATE => 'Aktualizacja',
            self::ACTION_DELETE => 'Usunięcie',
            self::ACTION_LOGIN => 'Logowanie',
            self::ACTION_LOGOUT => 'Wylogowanie',
            self::ACTION_UPLOAD => 'Przesłanie',
            self::ACTION_APPROVE => 'Zatwierdzenie',
            self::ACTION_REJECT => 'Odrzucenie',
            self::ACTION_IMPORT => 'Import',
            self::ACTION_EXPORT => 'Eksport',
            self::ACTION_SYNC => 'Synchronizacja',
            self::ACTION_ACCESS => 'Dostęp',
            self::ACTION_SETTINGS => 'Ustawienia',
            self::ACTION_SYSTEM => 'System'
        ];
        
        return $labels[$this->action] ?? $this->action;
    }

    public static function cleanup($days = 90)
    {
        $timestamp = time() - ($days * 24 * 60 * 60);
        return self::deleteAll(['<', 'created_at', $timestamp]);
    }
}