<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "photo".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $file_name
 * @property int $file_size
 * @property string $mime_type
 * @property string|null $s3_path
 * @property string $search_code
 * @property int $status
 * @property int $is_public
 * @property int|null $width
 * @property int|null $height
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 */
class Photo extends ActiveRecord 
{
    const STATUS_QUEUE = 0;    // Zdjęcie w poczekalni
    const STATUS_ACTIVE = 1;   // Zdjęcie aktywne
    const STATUS_DELETED = 2;  // Zdjęcie usunięte

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%photo}}';
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
            [['title', 'file_name', 'file_size', 'mime_type', 'created_by'], 'required'],
            [['description', 's3_path'], 'string'],
            [['file_size', 'status', 'is_public', 'width', 'height', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['title', 'file_name', 'mime_type'], 'string', 'max' => 255],
            [['search_code'], 'string', 'max' => 12],
            [['search_code'], 'unique'],
            [['search_code'], 'match', 'pattern' => '/^[A-Z0-9]{12}$/'],
            [['status'], 'default', 'value' => self::STATUS_QUEUE],
            [['status'], 'in', 'range' => [self::STATUS_QUEUE, self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['is_public'], 'default', 'value' => 0],
            [['is_public'], 'boolean'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Tytuł',
            'description' => 'Opis',
            'file_name' => 'Nazwa pliku',
            'file_size' => 'Rozmiar pliku',
            'mime_type' => 'Typ MIME',
            's3_path' => 'Ścieżka S3',
            'search_code' => 'Kod wyszukiwania',
            'status' => 'Status',
            'is_public' => 'Publiczne',
            'width' => 'Szerokość',
            'height' => 'Wysokość',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
            'created_by' => 'Utworzone przez',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if ($insert && empty($this->search_code)) {
            $this->search_code = $this->generateSearchCode();
        }
        return parent::beforeSave($insert);
    }

    /**
     * Generuje unikalny 12-cyfrowy kod wyszukiwania
     * 
     * @return string
     * @throws \Exception
     */
    public function generateSearchCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $maxAttempts = 100;
        $attempts = 0;
        
        do {
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= $characters[mt_rand(0, $charactersLength - 1)];
            }
            $attempts++;
            
            if ($attempts > $maxAttempts) {
                throw new \Exception('Cannot generate unique search code after ' . $maxAttempts . ' attempts');
            }
            
        } while (self::find()->where(['search_code' => $code])->exists());
        
        return $code;
    }

    /**
     * Znajduje zdjęcie po kodzie wyszukiwania
     * 
     * @param string $code
     * @return Photo|null
     */
    public static function findBySearchCode($code)
    {
        if (empty($code)) {
            return null;
        }
        
        return self::findOne(['search_code' => strtoupper(trim($code))]);
    }

    /**
     * Gets query for all tags assigned to photo
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('{{%photo_tag}}', ['photo_id' => 'id']);
    }

    /**
     * Gets query for all categories assigned to photo
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('{{%photo_category}}', ['photo_id' => 'id']);
    }

    /**
     * Gets query for all photo-tag relations
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoTags()
    {
        return $this->hasMany(PhotoTag::class, ['photo_id' => 'id']);
    }

    /**
     * Gets query for all photo-category relations
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoCategories()
    {
        return $this->hasMany(PhotoCategory::class, ['photo_id' => 'id']);
    }

    /**
     * Gets user who created the photo
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets formatted status name
     * 
     * @return string
     */
    public function getStatusName()
    {
        $statusMap = [
            self::STATUS_QUEUE => 'W poczekalni',
            self::STATUS_ACTIVE => 'Aktywne',
            self::STATUS_DELETED => 'Usunięte',
        ];

        return $statusMap[$this->status] ?? 'Nieznany';
    }

    /**
     * Gets available thumbnail URLs for this photo
     * 
     * @return array
     */
    public function getThumbnails()
    {
        $thumbnails = [];
        $thumbnailSizes = ThumbnailSize::find()->all();

        foreach ($thumbnailSizes as $size) {
            $thumbnails[$size->name] = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $this->file_name);
        }

        return $thumbnails;
    }

    /**
     * Checks if photo has specific status
     * 
     * @param int $status
     * @return bool
     */
    public function hasStatus($status)
    {
        return $this->status === $status;
    }

    /**
     * Checks if photo is in queue
     * 
     * @return bool
     */
    public function isInQueue()
    {
        return $this->hasStatus(self::STATUS_QUEUE);
    }

    /**
     * Checks if photo is active
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->hasStatus(self::STATUS_ACTIVE);
    }

    /**
     * Checks if photo is deleted
     * 
     * @return bool
     */
    public function isDeleted()
    {
        return $this->hasStatus(self::STATUS_DELETED);
    }

    /**
     * Checks if photo is public
     * 
     * @return bool
     */
    public function isPublic()
    {
        return (bool) $this->is_public;
    }
}