<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\Photo;

/**
 * UploadForm is the model behind the photo upload form.
 */
class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['imageFile'], 'required'],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => Yii::$app->params['maxUploadSize']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'imageFile' => 'Plik zdjęcia',
        ];
    }

    /**
     * Uploads the image file.
     * @return bool whether the file is uploaded successfully
     */
    public function upload()
    {
        if (!$this->validate()) {
            return false;
        }
        
        // Generowanie unikalnej nazwy pliku
        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $this->imageFile->extension;
        $filePath = Yii::getAlias('@uploads/temp/' . $fileName);
        
        // Zapisywanie pliku
        if (!$this->imageFile->saveAs($filePath)) {
            $this->addError('imageFile', 'Błąd zapisu pliku.');
            return false;
        }
        
        // Odczytywanie wymiarów i metadanych
        $image = Yii::$app->imageProcessor->getImageInfo($filePath);
        
        // Tworzenie rekordu w bazie
        $photo = new Photo();
        $photo->title = pathinfo($this->imageFile->name, PATHINFO_FILENAME);
        $photo->file_name = $fileName;
        $photo->file_size = $this->imageFile->size;
        $photo->mime_type = $this->imageFile->type;
        $photo->width = $image['width'];
        $photo->height = $image['height'];
        $photo->status = Photo::STATUS_QUEUE;
        $photo->is_public = false;
        $photo->created_at = time();
        $photo->updated_at = time();
        $photo->created_by = Yii::$app->user->id;
        
        if (!$photo->save()) {
            unlink($filePath);
            $this->addError('imageFile', 'Błąd zapisu danych: ' . json_encode($photo->errors));
            return false;
        }
        
        // Generowanie miniatur
        Yii::$app->imageProcessor->createThumbnails($filePath, $fileName);
        
        return true;
    }
}