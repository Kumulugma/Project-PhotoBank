<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Settings model
 *
 * @property integer $id
 * @property string $key
 * @property string $value
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 */
class Settings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%settings}}';
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
            [['key', 'value'], 'required'],
            [['value', 'description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['key'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Klucz',
            'value' => 'Wartość',
            'description' => 'Opis',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }
    
    /**
     * Get a setting value by key
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value or default
     */
    public static function getSetting($key, $default = null)
    {
        $setting = self::findOne(['key' => $key]);
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $description Setting description (optional)
     * @return bool Success
     */
    public static function setSetting($key, $value, $description = null)
    {
        $setting = self::findOne(['key' => $key]);
        
        if (!$setting) {
            $setting = new self();
            $setting->key = $key;
        }
        
        $setting->value = (string) $value;
        
        if ($description !== null) {
            $setting->description = $description;
        }
        
        return $setting->save();
    }
    
    /**
     * Get all settings by category (using dot notation in key)
     * 
     * @param string $category Category name
     * @return array Settings as key => value pairs
     */
    public static function getSettingsByCategory($category)
    {
        $settings = self::find()
            ->where(['like', 'key', $category . '.%', false])
            ->all();
            
        $result = [];
        foreach ($settings as $setting) {
            $key = substr($setting->key, strlen($category) + 1);
            $result[$key] = $setting->value;
        }
        
        return $result;
    }
}