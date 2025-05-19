<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * QueuedJob model for background tasks
 *
 * @property integer $id
 * @property string $type
 * @property string $params
 * @property integer $status
 * @property string $error_message
 * @property integer $attempts
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $started_at
 * @property integer $finished_at
 */
class QueuedJob extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%queued_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
{
    return [
        [['type', 'params'], 'required'],
        [['params', 'error_message'], 'string'],
        [['status', 'attempts', 'created_at', 'updated_at', 'started_at', 'completed_at'], 'integer'],
        [['type'], 'string', 'max' => 255],
        [['status'], 'default', 'value' => self::STATUS_PENDING],
        [['attempts'], 'default', 'value' => 0],
    ];
}

// Popraw attributeLabels():

public function attributeLabels()
{
    return [
        'id' => 'ID',
        'type' => 'Typ zadania',
        'params' => 'Parametry',
        'status' => 'Status',
        'attempts' => 'Próby',
        'error_message' => 'Komunikat błędu',
        'created_at' => 'Data utworzenia',
        'updated_at' => 'Data aktualizacji',
        'started_at' => 'Data rozpoczęcia',
        'completed_at' => 'Data zakończenia',
    ];
}

    
    /**
     * Gets decoded params
     * 
     * @return mixed Decoded params
     */
    public function getDecodedParams()
    {
        return json_decode($this->params, true);
    }
    
    /**
     * Gets formatted status name
     * 
     * @return string
     */
    public function getStatusName()
    {
        $statusMap = [
            self::STATUS_PENDING => 'Oczekujące',
            self::STATUS_PROCESSING => 'W trakcie',
            self::STATUS_COMPLETED => 'Zakończone',
            self::STATUS_FAILED => 'Błąd',
        ];
        
        return isset($statusMap[$this->status]) ? $statusMap[$this->status] : 'Nieznany';
    }
    
    /**
     * Create a new job
     * 
     * @param string $type Job type
     * @param mixed $params Job params
     * @return QueuedJob|null Created job or null on failure
     */
    public static function createJob($type, $params)
    {
        $job = new self();
        $job->type = $type;
        $job->params = json_encode($params);
        $job->status = self::STATUS_PENDING;
        $job->attempts = 0;
        
        return $job->save() ? $job : null;
    }
    
    /**
     * Mark job as started
     * 
     * @return bool Success
     */
    public function markAsStarted()
    {
        $this->status = self::STATUS_PROCESSING;
        $this->started_at = time();
        $this->attempts += 1;
        
        return $this->save();
    }
    
    /**
     * Mark job as completed
     * 
     * @return bool Success
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->finished_at = time();
        
        return $this->save();
    }
    
    /**
     * Mark job as failed
     * 
     * @param string $error Error message
     * @return bool Success
     */
    public function markAsFailed($error)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $error;
        $this->finished_at = time();
        
        return $this->save();
    }
    
    /**
     * Get next pending job
     * 
     * @param string $type Job type (optional)
     * @return QueuedJob|null Next pending job or null if none
     */
    public static function getNextPendingJob($type = null)
    {
        $query = self::find()
            ->where(['status' => self::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_ASC]);
            
        if ($type !== null) {
            $query->andWhere(['type' => $type]);
        }
        
        return $query->one();
    }
}