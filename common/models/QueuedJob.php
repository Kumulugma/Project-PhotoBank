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
 * @property string $data
 * @property string $params  // Alias dla data
 * @property integer $status
 * @property string $error
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $started_at
 * @property integer $finished_at
 * @property integer $attempts
 * @property string $error_message
 * @property string $results
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
            [['type'], 'required'],
            [['data', 'params', 'error', 'error_message', 'results'], 'string'],
            [['status', 'attempts', 'created_at', 'updated_at', 'started_at', 'finished_at'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['attempts'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Typ zadania',
            'data' => 'Dane',
            'params' => 'Parametry',
            'status' => 'Status',
            'error' => 'Błąd',
            'attempts' => 'Próby',
            'error_message' => 'Komunikat błędu',
            'results' => 'Wyniki przetwarzania',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
            'started_at' => 'Data rozpoczęcia',
            'finished_at' => 'Data zakończenia',
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            'id', 'type', 'data', 'params', 'status', 'error', 
            'created_at', 'updated_at', 'started_at', 'finished_at',
            'attempts', 'error_message', 'results'
        ];
    }

    /**
     * Getter dla params - alias dla data
     */
    public function getParams()
    {
        return $this->data;
    }
    
    /**
     * Setter dla params - zapisuje do data
     */
    public function setParams($value)
    {
        $this->data = $value;
    }

    /**
     * Gets decoded data
     * 
     * @return mixed Decoded data
     */
    public function getDecodedData()
    {
        return json_decode($this->data, true) ?: [];
    }
    
    /**
     * Gets decoded params (alias for getDecodedData)
     * 
     * @return mixed Decoded params
     */
    public function getDecodedParams()
    {
        return $this->getDecodedData();
    }
    
    /**
     * Gets decoded results
     * 
     * @return mixed Decoded results
     */
    public function getDecodedResults()
    {
        try {
            if (!$this->hasAttribute('results') || $this->results === null) {
                return [];
            }
            return json_decode($this->results, true) ?: [];
        } catch (\Exception $e) {
            Yii::warning('Błąd pobierania wyników: ' . $e->getMessage());
            return [];
        }
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
     * Gets formatted job type name
     * 
     * @return string
     */
    public function getTypeName()
    {
        $typeMap = [
            's3_sync' => 'Synchronizacja S3',
            'regenerate_thumbnails' => 'Regeneracja miniatur',
            'analyze_photo' => 'Analiza zdjęcia',
            'analyze_batch' => 'Analiza wsadowa',
            'import_photos' => 'Import zdjęć',
        ];
        
        return isset($typeMap[$this->type]) ? $typeMap[$this->type] : $this->type;
    }
    
    /**
     * Create a new job
     * 
     * @param string $type Job type
     * @param mixed $data Job data
     * @return QueuedJob|null Created job or null on failure
     */
    public static function createJob($type, $data = [])
    {
        $job = new self();
        $job->type = $type;
        $job->data = json_encode($data);
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
     * Mark job as finished (completed)
     * 
     * @param array $results Optional job results
     * @return bool Success
     */
    public function markAsFinished($results = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->finished_at = time();
        
        if ($results !== null) {
            $this->results = is_string($results) ? $results : json_encode($results);
        }
        
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
        $this->error = $error;
        $this->error_message = $error;
        
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
    
    /**
     * Get job duration in seconds
     * 
     * @return int|null Duration in seconds or null if job not completed
     */
    public function getDuration()
    {
        if ($this->started_at && $this->finished_at) {
            return $this->finished_at - $this->started_at;
        }
        
        if ($this->started_at && $this->status === self::STATUS_PROCESSING) {
            return time() - $this->started_at;
        }
        
        return null;
    }
    
    /**
     * Get formatted duration
     * 
     * @return string Formatted duration
     */
    public function getFormattedDuration()
    {
        $duration = $this->getDuration();
        
        if ($duration === null) {
            return '-';
        }
        
        if ($duration < 60) {
            return $duration . ' s';
        }
        
        if ($duration < 3600) {
            return round($duration / 60, 1) . ' min';
        }
        
        return round($duration / 3600, 1) . ' godz';
    }
    
    /**
     * Alias for finished_at to maintain compatibility
     * @return int|null
     */
    public function getCompleted_at()
    {
        return $this->finished_at;
    }

    /**
     * Alias for setting finished_at through completed_at
     * @param int $value
     */
    public function setCompleted_at($value)
    {
        $this->finished_at = $value;
    }
}