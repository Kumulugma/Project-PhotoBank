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
 * @property string|null $series
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
    const STATUS_QUEUE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public static function tableName()
    {
        return '{{%photo}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['title', 'file_name', 'file_size', 'mime_type', 'created_by'], 'required'],
            [['description', 's3_path'], 'string'],
            [['series'], 'string', 'max' => 50],
            [['series'], 'trim'],
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

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Tytuł',
            'description' => 'Opis',
            'series' => 'Seria',
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

    public function beforeSave($insert)
    {
        if ($insert && empty($this->search_code)) {
            $this->search_code = $this->generateSearchCode();
        }
        return parent::beforeSave($insert);
    }

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

    public static function findBySearchCode($code)
    {
        if (empty($code)) {
            return null;
        }
        
        return self::findOne(['search_code' => strtoupper(trim($code))]);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('{{%photo_tag}}', ['photo_id' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('{{%photo_category}}', ['photo_id' => 'id']);
    }

    public function getPhotoTags()
    {
        return $this->hasMany(PhotoTag::class, ['photo_id' => 'id']);
    }

    public function getPhotoCategories()
    {
        return $this->hasMany(PhotoCategory::class, ['photo_id' => 'id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getStatusName()
    {
        $statusMap = [
            self::STATUS_QUEUE => 'W poczekalni',
            self::STATUS_ACTIVE => 'Aktywne',
            self::STATUS_DELETED => 'Usunięte',
        ];

        return $statusMap[$this->status] ?? 'Nieznany';
    }

    public function getThumbnails()
    {
        $thumbnails = [];
        $thumbnailSizes = ThumbnailSize::find()->all();

        foreach ($thumbnailSizes as $size) {
            $thumbnails[$size->name] = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $this->file_name);
        }

        return $thumbnails;
    }

    public function hasStatus($status)
    {
        return $this->status === $status;
    }

    public function isInQueue()
    {
        return $this->hasStatus(self::STATUS_QUEUE);
    }

    public function isActive()
    {
        return $this->hasStatus(self::STATUS_ACTIVE);
    }

    public function isDeleted()
    {
        return $this->hasStatus(self::STATUS_DELETED);
    }

    public function isPublic()
    {
        return (bool) $this->is_public;
    }

    /**
     * Pobiera wszystkie unikalne serie z bazy danych
     * @return array
     */
    public static function getAllSeries()
    {
        return self::find()
            ->select('series')
            ->where(['is not', 'series', null])
            ->andWhere(['!=', 'series', ''])
            ->distinct()
            ->orderBy('series ASC')
            ->column();
    }

    /**
     * Sprawdza czy zdjęcie ma przypisaną serię
     * @return bool
     */
    public function hasSeries()
    {
        return !empty($this->series);
    }
}