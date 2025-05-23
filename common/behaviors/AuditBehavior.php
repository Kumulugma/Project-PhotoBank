<?php

namespace common\behaviors;

use Yii;
use yii\db\BaseActiveRecord;
use yii\base\Behavior;
use common\models\AuditLog;

/**
 * AuditBehavior automatycznie loguje zmiany w modelach
 * 
 * Użycie:
 * public function behaviors()
 * {
 *     return [
 *         'audit' => [
 *             'class' => AuditBehavior::class,
 *             'skipAttributes' => ['updated_at', 'password_hash'],
 *             'logCreate' => true,
 *             'logUpdate' => true,
 *             'logDelete' => true,
 *         ]
 *     ];
 * }
 */
class AuditBehavior extends Behavior
{
    /**
     * @var array Atrybuty do pominięcia podczas logowania
     */
    public $skipAttributes = ['updated_at', 'created_at'];
    
    /**
     * @var bool Czy logować tworzenie rekordów
     */
    public $logCreate = true;
    
    /**
     * @var bool Czy logować aktualizacje rekordów
     */
    public $logUpdate = true;
    
    /**
     * @var bool Czy logować usuwanie rekordów
     */
    public $logDelete = true;
    
    /**
     * @var array Stare wartości atrybutów przed aktualizacją
     */
    private $_oldAttributes = [];

    public function events()
    {
        $events = [];
        
        if ($this->logCreate) {
            $events[BaseActiveRecord::EVENT_AFTER_INSERT] = 'afterInsert';
        }
        
        if ($this->logUpdate) {
            $events[BaseActiveRecord::EVENT_BEFORE_UPDATE] = 'beforeUpdate';
            $events[BaseActiveRecord::EVENT_AFTER_UPDATE] = 'afterUpdate';
        }
        
        if ($this->logDelete) {
            $events[BaseActiveRecord::EVENT_AFTER_DELETE] = 'afterDelete';
        }
        
        return $events;
    }

    public function afterInsert($event)
    {
        try {
            AuditLog::logModelAction($this->owner, AuditLog::ACTION_CREATE);
        } catch (\Exception $e) {
            Yii::error('Błąd logowania utworzenia rekordu: ' . $e->getMessage());
        }
    }

    public function beforeUpdate($event)
    {
        // Zapisz stare wartości przed aktualizacją
        $this->_oldAttributes = [];
        
        foreach ($this->owner->getDirtyAttributes() as $attribute => $newValue) {
            if (in_array($attribute, $this->skipAttributes)) {
                continue;
            }
            
            $oldValue = $this->owner->getOldAttribute($attribute);
            if ($oldValue !== $newValue) {
                $this->_oldAttributes[$attribute] = $oldValue;
            }
        }
    }

    public function afterUpdate($event)
    {
        if (empty($this->_oldAttributes)) {
            return; // Brak istotnych zmian
        }
        
        try {
            AuditLog::logModelAction($this->owner, AuditLog::ACTION_UPDATE, $this->_oldAttributes);
        } catch (\Exception $e) {
            Yii::error('Błąd logowania aktualizacji rekordu: ' . $e->getMessage());
        }
    }

    public function afterDelete($event)
    {
        try {
            AuditLog::logModelAction($this->owner, AuditLog::ACTION_DELETE);
        } catch (\Exception $e) {
            Yii::error('Błąd logowania usunięcia rekordu: ' . $e->getMessage());
        }
    }
}