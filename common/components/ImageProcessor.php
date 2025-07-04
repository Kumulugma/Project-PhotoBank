<?php

namespace common\components;

use Yii;
use yii\base\Component;
use Intervention\Image\ImageManagerStatic as Image;
use common\models\Settings;
use common\models\ThumbnailSize;
use common\helpers\PathHelper;

/**
 * Komponent do przetwarzania obrazów
 */
class ImageProcessor extends Component {

    /**
     * Inicjalizacja komponentu
     */
    public function init() {
        parent::init();
        // Konfiguracja biblioteki Intervention Image
        Image::configure(['driver' => 'gd']);
    }

    /**
     * Tworzy miniatury dla wskazanego zdjęcia
     * 
     * @param string $sourcePath Ścieżka do pliku źródłowego
     * @param string $fileName Nazwa pliku
     * @return array Tablica z adresami URL miniatur
     */
    public function createThumbnails($sourcePath, $fileName) {
        $thumbnails = [];
        $thumbnailSizes = ThumbnailSize::find()->all();

        // Upewnij się, że katalog miniatur istnieje
        PathHelper::ensureDirectoryExists('thumbnails');

        foreach ($thumbnailSizes as $size) {
            $thumbnailPath = PathHelper::getThumbnailPath($size->name, $fileName);
            $thumbnailImage = Image::make($sourcePath);

            if ($size->crop) {
                $thumbnailImage->fit($size->width, $size->height);
            } else {
                $thumbnailImage->resize($size->width, $size->height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($size->watermark) {
                $this->addWatermark($thumbnailImage);
            }

            $thumbnailImage->save($thumbnailPath);
            $thumbnails[$size->name] = PathHelper::getThumbnailUrl($size->name, $fileName);
        }

        return $thumbnails;
    }

    /**
     * Dodaje znak wodny do obrazu
     * 
     * @param \Intervention\Image\Image $image Obiekt obrazu
     * @return \Intervention\Image\Image Obraz ze znakiem wodnym
     */
    public function addWatermark($image) {
        $watermarkType = Settings::getSetting('watermark.type', 'text');
        $watermarkPosition = Settings::getSetting('watermark.position', 'bottom-right');
        $watermarkOpacity = (float) Settings::getSetting('watermark.opacity', 0.7);

        $positionMap = [
            'top-left' => 'top-left',
            'top-right' => 'top-right',
            'bottom-left' => 'bottom-left',
            'bottom-right' => 'bottom-right',
            'center' => 'center'
        ];

        $position = $positionMap[$watermarkPosition] ?? 'bottom-right';

        if ($watermarkType === 'text') {
            $watermarkText = Settings::getSetting('watermark.text', 'Zasobnik B');

            if (!empty($watermarkText)) {
                $fontSize = min($image->width(), $image->height()) / 20;

                $image->text($watermarkText, $image->width() - 20, $image->height() - 20, function ($font) use ($fontSize, $watermarkOpacity) {
                    //$font->file(Yii::getAlias('@webroot/fonts/arial.ttf'));
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity * 255]);
                    $font->align('right');
                    $font->valign('bottom');
                });
            }
        } elseif ($watermarkType === 'image') {
            $watermarkImage = Settings::getSetting('watermark.image', '');

            if (!empty($watermarkImage)) {
                $watermarkPath = Yii::getAlias('@uploads/watermark/' . $watermarkImage);

                if (file_exists($watermarkPath)) {
                    $watermark = Image::make($watermarkPath);

                    $maxWidth = $image->width() / 4;
                    $maxHeight = $image->height() / 4;

                    if ($watermark->width() > $maxWidth || $watermark->height() > $maxHeight) {
                        $watermark->resize($maxWidth, $maxHeight, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $watermark->opacity($watermarkOpacity * 100);
                    $image->insert($watermark, $position);
                }
            }
        }

        return $image;
    }

    /**
     * Pobiera informacje o obrazie
     * 
     * @param string $filePath Ścieżka do pliku
     * @return array Informacje o obrazie
     */
    public function getImageInfo($filePath) {
        $image = Image::make($filePath);
        $result = [
            'width' => $image->width(),
            'height' => $image->height(),
            'mime' => $image->mime(),
        ];

        // Pobieranie danych EXIF jeśli dostępne
        if (method_exists($image, 'exif')) {
            $result['exif'] = $image->exif() ?: null;
        }

        return $result;
    }

    /**
     * Regeneruje miniatury dla wskazanego zdjęcia
     * 
     * @param common\models\Photo $photo Model zdjęcia
     * @param bool $forceLocalDownload Czy wymusić pobranie pliku z S3
     * @return array|bool Tablica z adresami URL miniatur lub false w przypadku błędu
     */
    public function regenerateThumbnails($photo, $forceLocalDownload = false) {
        $tempPath = PathHelper::getPhotoPath($photo->file_name, 'temp');
        $fileExists = file_exists($tempPath);
        $tempDownloaded = false;

        if (!$fileExists && !empty($photo->s3_path) && ($forceLocalDownload || $photo->status === 1)) {
            try {
                /** @var \common\components\S3Component $s3 */
                $s3 = Yii::$app->s3;
                $s3Settings = $s3->getSettings();

                // Upewnij się, że katalog temp istnieje
                PathHelper::ensureDirectoryExists('temp');

                $s3->getObject([
                    'Bucket' => $s3Settings['bucket'],
                    'Key' => $photo->s3_path,
                    'SaveAs' => $tempPath
                ]);

                $fileExists = true;
                $tempDownloaded = true;
            } catch (\Exception $e) {
                Yii::error('Błąd pobierania pliku z S3: ' . $e->getMessage());
                return false;
            }
        }

        if (!$fileExists) {
            return false;
        }

        $thumbnails = $this->createThumbnails($tempPath, $photo->file_name);

        if ($tempDownloaded) {
            unlink($tempPath);
        }

        return $thumbnails;
    }

}
