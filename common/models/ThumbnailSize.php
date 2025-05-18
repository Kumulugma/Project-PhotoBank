<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * ThumbnailSize model
 *
 * @property integer $id
 * @property string $name
 * @property integer $width
 * @property integer $height
 * @property boolean $crop
 * @property boolean $watermark
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThumbnailSize extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%thumbnail_size}}';
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
            [['name', 'width', 'height'], 'required'],
            [['width', 'height', 'created_at', 'updated_at'], 'integer'],
            [['crop', 'watermark'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['width', 'height'], 'integer', 'min' => 1],
            [['crop', 'watermark'], 'default', 'value' => false],
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
            'width' => 'Szerokość',
            'height' => 'Wysokość',
            'crop' => 'Przycinanie',
            'watermark' => 'Znak wodny',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }
    
    /**
     * Gets thumbnail path for a given photo file name
     * 
     * @param string $fileName Photo file name
     * @return string Thumbnail path
     */
    public function getThumbnailPath($fileName)
    {
        return Yii::getAlias('@webroot/uploads/thumbnails/' . $this->name . '_' . $fileName);
    }
    
    /**
     * Gets thumbnail URL for a given photo file name
     * 
     * @param string $fileName Photo file name
     * @return string Thumbnail URL
     */
    public function getThumbnailUrl($fileName)
    {
        return Yii::getAlias('@web/uploads/thumbnails/' . $this->name . '_' . $fileName);
    }
}