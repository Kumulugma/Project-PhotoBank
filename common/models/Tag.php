<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Tag model
 *
 * @property integer $id
 * @property string $name
 * @property integer $frequency
 * @property integer $created_at
 * @property integer $updated_at
 */
class Tag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tag}}';
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
            [['name'], 'required'],
            [['frequency', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['frequency'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nazwa',
            'frequency' => 'Częstotliwość użycia',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }

    /**
     * Gets query for all photos tagged with this tag
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::class, ['id' => 'photo_id'])
            ->viaTable('{{%photo_tag}}', ['tag_id' => 'id']);
    }
    
    /**
     * Gets query for all photo-tag relations
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoTags()
    {
        return $this->hasMany(PhotoTag::class, ['tag_id' => 'id']);
    }
    
    /**
     * Gets photo count for this tag
     * 
     * @param bool $onlyActive Count only active photos
     * @param bool $onlyPublic Count only public photos
     * @return int
     */
    public function getPhotoCount($onlyActive = true, $onlyPublic = false)
    {
        $query = $this->getPhotos();
        
        if ($onlyActive) {
            $query->andWhere(['status' => Photo::STATUS_ACTIVE]);
        }
        
        if ($onlyPublic) {
            $query->andWhere(['is_public' => true]);
        }
        
        return $query->count();
    }
    
    /**
     * Update frequency counter
     * 
     * @return int New frequency value
     */
    public function updateFrequency()
    {
        $this->frequency = $this->getPhotoCount();
        $this->save();
        
        return $this->frequency;
    }
}