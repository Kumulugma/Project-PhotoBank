<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * PhotoTag model
 *
 * @property integer $photo_id
 * @property integer $tag_id
 */
class PhotoTag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%photo_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo_id', 'tag_id'], 'required'],
            [['photo_id', 'tag_id'], 'integer'],
            [['photo_id', 'tag_id'], 'unique', 'targetAttribute' => ['photo_id', 'tag_id']],
            [['photo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Photo::class, 'targetAttribute' => ['photo_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * Gets related photo
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhoto()
    {
        return $this->hasOne(Photo::class, ['id' => 'photo_id']);
    }

    /**
     * Gets related tag
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }
    
    /**
     * After save, update tag frequency
     * 
     * @param bool $insert Whether this is an insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        if ($insert) {
            $tag = $this->tag;
            $tag->frequency += 1;
            $tag->save();
        }
    }
    
    /**
     * After delete, update tag frequency
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        $tag = Tag::findOne($this->tag_id);
        if ($tag && $tag->frequency > 0) {
            $tag->frequency -= 1;
            $tag->save();
        }
    }
}