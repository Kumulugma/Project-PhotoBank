<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Photo model
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $file_name
 * @property integer $file_size
 * @property string $mime_type
 * @property string $s3_path
 * @property integer $status
 * @property boolean $is_public
 * @property integer $width
 * @property integer $height
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 */
class Photo extends ActiveRecord {

    const STATUS_QUEUE = 0;    // Zdjęcie w poczekalni
    const STATUS_ACTIVE = 1;   // Zdjęcie aktywne
    const STATUS_DELETED = 2;  // Zdjęcie usunięte

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%photo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['title', 'file_name', 'file_size', 'mime_type', 'created_by'], 'required'],
            [['description'], 'string'],
            [['file_size', 'width', 'height', 'status', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['is_public'], 'boolean'],
            [['title', 'file_name', 'mime_type', 's3_path'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_QUEUE],
            [['is_public'], 'default', 'value' => false],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
        'id' => 'ID',
        'title' => 'Tytuł',
        'description' => 'Opis',
        'file_name' => 'Nazwa pliku',
        'file_size' => 'Rozmiar pliku',
        'mime_type' => 'Typ MIME',
        's3_path' => 'Ścieżka S3',
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
public function getCategories() {
    return $this->hasMany(Category::class, ['id' => 'category_id'])
                    ->viaTable('{{%photo_category}}', ['photo_id' => 'id']);
}

/**
 * Gets query for all photo-tag relations
 * 
 * @return \yii\db\ActiveQuery
 */
public function getPhotoTags() {
    return $this->hasMany(PhotoTag::class, ['photo_id' => 'id']);
}

/**
 * Gets query for all photo-category relations
 * 
 * @return \yii\db\ActiveQuery
 */
public function getPhotoCategories() {
    return $this->hasMany(PhotoCategory::class, ['photo_id' => 'id']);
}

/**
 * Gets user who created the photo
 * 
 * @return \yii\db\ActiveQuery
 */
public function getCreatedBy() {
    return $this->hasOne(User::class, ['id' => 'created_by']);
}

/**
 * Gets formatted status name
 * 
 * @return string
 */
public function getStatusName() {
    $statusMap = [
        self::STATUS_QUEUE => 'W poczekalni',
        self::STATUS_ACTIVE => 'Aktywne',
        self::STATUS_DELETED => 'Usunięte',
    ];

    return isset($statusMap[$this->status]) ? $statusMap[$this->status] : 'Nieznany';
}

/**
 * Gets available thumbnail URLs for this photo
 * 
 * @return array
 */
public function getThumbnails() {
    $thumbnails = [];
    $thumbnailSizes = ThumbnailSize::find()->all();

    foreach ($thumbnailSizes as $size) {
        $thumbnails[$size->name] = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $this->file_name);
    }

    return $thumbnails;
}

}
