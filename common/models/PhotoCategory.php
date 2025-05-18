<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * PhotoCategory model
 *
 * @property integer $photo_id
 * @property integer $category_id
 */
class PhotoCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%photo_category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo_id', 'category_id'], 'required'],
            [['photo_id', 'category_id'], 'integer'],
            [['photo_id', 'category_id'], 'unique', 'targetAttribute' => ['photo_id', 'category_id']],
            [['photo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Photo::class, 'targetAttribute' => ['photo_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
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
     * Gets related category
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}