<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use common\behaviors\AuditBehavior;

/**
 * Category model
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'audit' => [
                'class' => AuditBehavior::class,
                'skipAttributes' => ['updated_at', 'created_at'],
                'logCreate' => true,
                'logUpdate' => true,
                'logDelete' => true,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
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
            'slug' => 'Slug',
            'description' => 'Opis',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
        ];
    }

    /**
     * Gets query for all photos assigned to category
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::class, ['id' => 'photo_id'])
            ->viaTable('{{%photo_category}}', ['category_id' => 'id']);
    }
    
    /**
     * Gets query for all photo-category relations
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoCategories()
    {
        return $this->hasMany(PhotoCategory::class, ['category_id' => 'id']);
    }
    
    /**
     * Gets photo count for this category
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
     * Generate slug from name
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert || $this->isAttributeChanged('name')) {
                $this->slug = Inflector::slug($this->name);
            }
            return true;
        }
        return false;
    }
}