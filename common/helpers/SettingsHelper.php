<?php

namespace common\helpers;

use Yii;
use common\models\Settings;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Helper class for working with application settings
 */
class SettingsHelper
{
    /**
     * Cache tag for settings
     */
    const CACHE_TAG = 'settings';
    
    /**
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour
    
    /**
     * Get a setting value by key with optional default value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @param bool $useCache Whether to use cache
     * @return mixed Setting value or default
     */
    public static function get($key, $default = null, $useCache = true)
    {
        if ($useCache) {
            $cacheKey = 'setting_' . $key;
            $value = Yii::$app->cache->get($cacheKey);
            
            if ($value !== false) {
                return $value;
            }
        }
        
        $setting = Settings::findOne(['key' => $key]);
        $value = $setting ? $setting->value : $default;
        
        if ($useCache) {
            Yii::$app->cache->set(
                $cacheKey, 
                $value, 
                self::CACHE_DURATION, 
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        }
        
        return $value;
    }
    
    /**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $description Setting description (optional)
     * @return bool Success
     */
    public static function set($key, $value, $description = null)
    {
        $setting = Settings::findOne(['key' => $key]);
        
        if (!$setting) {
            $setting = new Settings();
            $setting->key = $key;
            $setting->created_at = time();
        }
        
        $setting->value = (string)$value;
        $setting->updated_at = time();
        
        if ($description !== null) {
            $setting->description = $description;
        }
        
        $result = $setting->save();
        
        if ($result) {
            // Invalidate cache
            TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
            
            // Update specific cache key
            $cacheKey = 'setting_' . $key;
            Yii::$app->cache->set(
                $cacheKey, 
                $value, 
                self::CACHE_DURATION, 
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        }
        
        return $result;
    }
    
    /**
     * Get all settings in a specific category
     * 
     * @param string $category Category prefix (e.g. 'email')
     * @param bool $useCache Whether to use cache
     * @return array Settings as key => value pairs
     */
    public static function getCategory($category, $useCache = true)
    {
        if ($useCache) {
            $cacheKey = 'settings_category_' . $category;
            $result = Yii::$app->cache->get($cacheKey);
            
            if ($result !== false) {
                return $result;
            }
        }
        
        $settings = Settings::find()
            ->where(['like', 'key', $category . '.%', false])
            ->all();
            
        $result = [];
        foreach ($settings as $setting) {
            $key = substr($setting->key, strlen($category) + 1);
            $result[$key] = $setting->value;
        }
        
        if ($useCache) {
            Yii::$app->cache->set(
                $cacheKey, 
                $result, 
                self::CACHE_DURATION, 
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        }
        
        return $result;
    }
    
    /**
     * Get all settings grouped by category
     * 
     * @param bool $useCache Whether to use cache
     * @return array Settings grouped by category
     */
    public static function getAll($useCache = true)
    {
        if ($useCache) {
            $cacheKey = 'settings_all';
            $result = Yii::$app->cache->get($cacheKey);
            
            if ($result !== false) {
                return $result;
            }
        }
        
        $settings = Settings::find()->orderBy(['key' => SORT_ASC])->all();
        $result = [];
        
        foreach ($settings as $setting) {
            $parts = explode('.', $setting->key);
            
            if (count($parts) >= 2) {
                $category = $parts[0];
                $name = $parts[1];
                
                if (!isset($result[$category])) {
                    $result[$category] = [];
                }
                
                $result[$category][$name] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            } else {
                // For settings without category
                if (!isset($result['general'])) {
                    $result['general'] = [];
                }
                
                $result['general'][$setting->key] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            }
        }
        
        if ($useCache) {
            Yii::$app->cache->set(
                $cacheKey, 
                $result, 
                self::CACHE_DURATION, 
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        }
        
        return $result;
    }
    
    /**
     * Update multiple settings at once
     * 
     * @param array $settings Associative array of key => value pairs
     * @return bool Success
     */
    public static function batchUpdate($settings)
    {
        if (empty($settings)) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                // Skip masked values
                if ($value === '********') {
                    continue;
                }
                
                // Find or create setting
                $setting = Settings::findOne(['key' => $key]);
                if (!$setting) {
                    $setting = new Settings();
                    $setting->key = $key;
                    $setting->created_at = time();
                }
                
                $setting->value = $value;
                $setting->updated_at = time();
                
                if (!$setting->save()) {
                    throw new \Exception('Error saving setting ' . $key . ': ' . json_encode($setting->errors));
                }
            }
            
            $transaction->commit();
            
            // Invalidate cache
            TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
            
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error updating settings: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a setting
     * 
     * @param string $key Setting key
     * @return bool Success
     */
    public static function remove($key)
    {
        $setting = Settings::findOne(['key' => $key]);
        
        if (!$setting) {
            return false;
        }
        
        $result = $setting->delete();
        
        if ($result) {
            // Invalidate cache
            TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
            
            // Remove specific cache key
            $cacheKey = 'setting_' . $key;
            Yii::$app->cache->delete($cacheKey);
        }
        
        return $result;
    }
    
    /**
     * Check if a setting exists
     * 
     * @param string $key Setting key
     * @return bool Whether setting exists
     */
    public static function exists($key)
    {
        return Settings::find()->where(['key' => $key])->exists();
    }
    
    /**
     * Get boolean value for a setting
     * 
     * @param string $key Setting key
     * @param bool $default Default value if setting not found
     * @return bool Setting value as boolean
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get integer value for a setting
     * 
     * @param string $key Setting key
     * @param int $default Default value if setting not found
     * @return int Setting value as integer
     */
    public static function getInt($key, $default = 0)
    {
        $value = self::get($key, $default);
        return (int)$value;
    }
    
    /**
     * Get float value for a setting
     * 
     * @param string $key Setting key
     * @param float $default Default value if setting not found
     * @return float Setting value as float
     */
    public static function getFloat($key, $default = 0.0)
    {
        $value = self::get($key, $default);
        return (float)$value;
    }
    
    /**
     * Get array value for a setting (comma-separated)
     * 
     * @param string $key Setting key
     * @param array $default Default value if setting not found
     * @return array Setting value as array
     */
    public static function getArray($key, $default = [])
    {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }
        
        if (empty($value)) {
            return [];
        }
        
        return explode(',', $value);
    }
    
    /**
     * Clear all settings cache
     * 
     * @return void
     */
    public static function clearCache()
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
    }
}