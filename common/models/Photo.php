<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\helpers\PathHelper;

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
 * @property string|null $exif_data
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 */
class Photo extends ActiveRecord {

    const STATUS_QUEUE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public static function tableName() {
        return '{{%photo}}';
    }

    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules() {
        return [
            [['title', 'file_name', 'file_size', 'mime_type', 'created_by'], 'required'],
            [['description', 's3_path', 'exif_data'], 'string'],
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
            [['is_public'], 'integer', 'min' => 0, 'max' => 1],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    public function attributeLabels() {
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
            'exif_data' => 'Dane EXIF',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
            'created_by' => 'Utworzone przez',
        ];
    }

    public function beforeSave($insert) {
        if ($insert && empty($this->search_code)) {
            $this->search_code = $this->generateSearchCode();
        }
        return parent::beforeSave($insert);
    }

    public function generateSearchCode() {
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

    public static function findBySearchCode($code) {
        if (empty($code)) {
            return null;
        }

        return self::findOne(['search_code' => strtoupper(trim($code))]);
    }

    public function getTags() {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
                        ->viaTable('{{%photo_tag}}', ['photo_id' => 'id']);
    }

    public function getCategories() {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
                        ->viaTable('{{%photo_category}}', ['photo_id' => 'id']);
    }

    public function getPhotoTags() {
        return $this->hasMany(PhotoTag::class, ['photo_id' => 'id']);
    }

    public function getPhotoCategories() {
        return $this->hasMany(PhotoCategory::class, ['photo_id' => 'id']);
    }

    public function getCreatedBy() {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getStatusName() {
        $statusMap = [
            self::STATUS_QUEUE => 'W poczekalni',
            self::STATUS_ACTIVE => 'Aktywne',
            self::STATUS_DELETED => 'Usunięte',
        ];

        return $statusMap[$this->status] ?? 'Nieznany';
    }

    public function getThumbnails() {
        $thumbnails = [];
        $thumbnailSizes = ThumbnailSize::find()->all();

        foreach ($thumbnailSizes as $size) {
            $thumbnails[$size->name] = PathHelper::getThumbnailUrl($size->name, $this->file_name);
        }

        return $thumbnails;
    }

    public function getOriginalName() {
        // Usuń hash z nazwy pliku, jeśli istnieje
        $fileName = pathinfo($this->file_name, PATHINFO_FILENAME);

        // Sprawdź czy nazwa zawiera hash (format: nazwa_hash)
        if (preg_match('/^(.+)_[A-Za-z0-9]{8}$/', $fileName, $matches)) {
            return $matches[1];
        }

        return $fileName;
    }

    public function hasStatus($status) {
        return $this->status === $status;
    }

    public function isInQueue() {
        return $this->hasStatus(self::STATUS_QUEUE);
    }

    public function isActive() {
        return $this->hasStatus(self::STATUS_ACTIVE);
    }

    public function isDeleted() {
        return $this->hasStatus(self::STATUS_DELETED);
    }

    public function isPublic() {
        return (bool) $this->is_public;
    }

    /**
     * Pobiera wszystkie unikalne serie z bazy danych
     * @return array
     */
    public static function getAllSeries() {
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
    public function hasSeries() {
        return !empty($this->series);
    }

    /**
     * Odczytuje i zapisuje dane EXIF ze zdjęcia
     */
    public function extractAndSaveExif() {
        $filePath = PathHelper::getPhotoPath($this->file_name, 'temp');

        if (!file_exists($filePath)) {
            return false;
        }

        if (!function_exists('exif_read_data')) {
            Yii::warning('EXIF extension is not installed');
            return false;
        }

        try {
            $exifData = exif_read_data($filePath, 0, true);

            if ($exifData !== false) {
                $this->exif_data = json_encode($exifData, JSON_UNESCAPED_UNICODE);
                return $this->save(false, ['exif_data']);
            }
        } catch (\Exception $e) {
            Yii::error('Error reading EXIF data: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Pobiera dane EXIF jako tablicę
     */
    public function getExifArray() {
        if (empty($this->exif_data)) {
            return [];
        }

        return json_decode($this->exif_data, true) ?: [];
    }

    /**
     * Pobiera sformatowane dane EXIF do wyświetlenia
     */
    public function getFormattedExif() {
        $exif = $this->getExifArray();
        if (empty($exif)) {
            return [];
        }

        $formatted = [];

        // Prawa autorskie - PRIORYTET
        if (Settings::getSetting('gallery.exif_show_copyright', '1') == '1') {
            if (isset($exif['IFD0']['Copyright'])) {
                $formatted['Prawa autorskie'] = $exif['IFD0']['Copyright'];
            }
            if (isset($exif['IFD0']['Artist'])) {
                $formatted['Autor'] = $exif['IFD0']['Artist'];
            }
            if (isset($exif['EXIF']['UserComment'])) {
                $userComment = $this->cleanUserComment($exif['EXIF']['UserComment']);
                if (!empty($userComment)) {
                    $formatted['Komentarz autora'] = $userComment;
                }
            }
        }

        // Dodatkowe informacje o autorskich
        if (Settings::getSetting('gallery.exif_show_author_info', '1') == '1') {
            if (isset($exif['IFD0']['ImageDescription'])) {
                $formatted['Opis obrazu'] = $exif['IFD0']['ImageDescription'];
            }
            if (isset($exif['EXIF']['ImageUniqueID'])) {
                $formatted['Unikatowy ID obrazu'] = $exif['EXIF']['ImageUniqueID'];
            }
            if (isset($exif['IFD0']['DocumentName'])) {
                $formatted['Nazwa dokumentu'] = $exif['IFD0']['DocumentName'];
            }
        }

        // Aparat i obiektyw
        if (Settings::getSetting('gallery.exif_show_camera', '1') == '1') {
            if (isset($exif['IFD0']['Make'])) {
                $formatted['Marka aparatu'] = $exif['IFD0']['Make'];
            }
            if (isset($exif['IFD0']['Model'])) {
                $formatted['Model aparatu'] = $exif['IFD0']['Model'];
            }
        }

        if (Settings::getSetting('gallery.exif_show_lens', '1') == '1') {
            if (isset($exif['EXIF']['LensModel'])) {
                $formatted['Model obiektywu'] = $exif['EXIF']['LensModel'];
            }
            if (isset($exif['EXIF']['LensMake'])) {
                $formatted['Marka obiektywu'] = $exif['EXIF']['LensMake'];
            }
        }

        // Ustawienia ekspozycji
        if (Settings::getSetting('gallery.exif_show_exposure', '1') == '1') {
            if (isset($exif['EXIF']['ISOSpeedRatings'])) {
                $formatted['ISO'] = $exif['EXIF']['ISOSpeedRatings'];
            }
            if (isset($exif['EXIF']['FNumber'])) {
                $fNumber = $this->parseExifFraction($exif['EXIF']['FNumber']);
                $formatted['Przysłona'] = 'f/' . number_format($fNumber, 1);
            }
            if (isset($exif['EXIF']['ExposureTime'])) {
                $exposureTime = $this->parseExifFraction($exif['EXIF']['ExposureTime']);
                if ($exposureTime < 1) {
                    $formatted['Czas ekspozycji'] = '1/' . round(1 / $exposureTime) . 's';
                } else {
                    $formatted['Czas ekspozycji'] = number_format($exposureTime, 1) . 's';
                }
            }
            if (isset($exif['EXIF']['FocalLength'])) {
                $focalLength = $this->parseExifFraction($exif['EXIF']['FocalLength']);
                $formatted['Ogniskowa'] = number_format($focalLength, 0) . 'mm';
            }
        }

        // Data i czas
        if (Settings::getSetting('gallery.exif_show_datetime', '1') == '1') {
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $formatted['Data wykonania'] = date('d.m.Y H:i:s', strtotime($exif['EXIF']['DateTimeOriginal']));
            } elseif (isset($exif['IFD0']['DateTime'])) {
                $formatted['Data modyfikacji'] = date('d.m.Y H:i:s', strtotime($exif['IFD0']['DateTime']));
            }
        }

        // Flash
        if (Settings::getSetting('gallery.exif_show_flash', '1') == '1') {
            if (isset($exif['EXIF']['Flash'])) {
                $flashValue = $exif['EXIF']['Flash'];
                $flashText = $this->getFlashDescription($flashValue);
                $formatted['Flesz'] = $flashText;
            }
        }

        // Wymiary oryginalne
        if (Settings::getSetting('gallery.exif_show_dimensions', '1') == '1') {
            if (isset($exif['EXIF']['ExifImageWidth']) && isset($exif['EXIF']['ExifImageLength'])) {
                $formatted['Wymiary EXIF'] = $exif['EXIF']['ExifImageWidth'] . ' × ' . $exif['EXIF']['ExifImageLength'] . ' px';
            }
        }

        // GPS
        if (Settings::getSetting('gallery.exif_show_gps', '0') == '1') {
            if (isset($exif['GPS']['GPSLatitude']) && isset($exif['GPS']['GPSLongitude'])) {
                $lat = $this->getGpsCoordinate($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
                $lon = $this->getGpsCoordinate($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
                $formatted['Lokalizacja GPS'] = number_format($lat, 6) . ', ' . number_format($lon, 6);
            }
        }

        // Oprogramowanie
        if (Settings::getSetting('gallery.exif_show_software', '0') == '1') {
            if (isset($exif['IFD0']['Software'])) {
                $formatted['Oprogramowanie'] = $exif['IFD0']['Software'];
            }
        }

        // Zaawansowane dane techniczne
        if (Settings::getSetting('gallery.exif_show_technical', '0') == '1') {
            if (isset($exif['EXIF']['WhiteBalance'])) {
                $formatted['Balans bieli'] = $exif['EXIF']['WhiteBalance'] == 0 ? 'Automatyczny' : 'Manualny';
            }
            if (isset($exif['EXIF']['MeteringMode'])) {
                $meteringModes = [
                    0 => 'Nieznany',
                    1 => 'Średni',
                    2 => 'Centralnie ważony',
                    3 => 'Punktowy',
                    4 => 'Wielopunktowy',
                    5 => 'Wzór',
                    6 => 'Częściowy',
                ];
                $formatted['Sposób pomiaru'] = $meteringModes[$exif['EXIF']['MeteringMode']] ?? 'Nieznany';
            }
            if (isset($exif['EXIF']['ExposureMode'])) {
                $exposureModes = [
                    0 => 'Automatyczny',
                    1 => 'Manualny',
                    2 => 'Auto bracket'
                ];
                $formatted['Tryb ekspozycji'] = $exposureModes[$exif['EXIF']['ExposureMode']] ?? 'Nieznany';
            }
        }

        return $formatted;
    }

    /**
     * Pobiera dane praw autorskich z EXIF
     */
    public function getCopyrightInfo() {
        $exif = $this->getExifArray();
        if (empty($exif)) {
            return [];
        }

        $copyright = [];

        if (isset($exif['IFD0']['Copyright'])) {
            $copyright['copyright'] = $exif['IFD0']['Copyright'];
        }

        if (isset($exif['IFD0']['Artist'])) {
            $copyright['artist'] = $exif['IFD0']['Artist'];
        }

        if (isset($exif['EXIF']['UserComment'])) {
            $userComment = $this->cleanUserComment($exif['EXIF']['UserComment']);
            if (!empty($userComment)) {
                $copyright['user_comment'] = $userComment;
            }
        }

        if (isset($exif['IFD0']['ImageDescription'])) {
            $copyright['description'] = $exif['IFD0']['ImageDescription'];
        }

        return $copyright;
    }

    /**
     * Sprawdza czy zdjęcie ma informacje o prawach autorskich
     */
    public function hasCopyrightInfo() {
        $copyrightInfo = $this->getCopyrightInfo();
        return !empty($copyrightInfo);
    }

    /**
     * Parsuje ułamek EXIF do liczby dziesiętnej
     */
    private function parseExifFraction($fraction) {
        if (is_numeric($fraction)) {
            return (float) $fraction;
        }

        if (strpos($fraction, '/') !== false) {
            $parts = explode('/', $fraction);
            if (count($parts) == 2 && $parts[1] != 0) {
                return (float) $parts[0] / (float) $parts[1];
            }
        }

        return 0;
    }

    /**
     * Konwertuje współrzędne GPS na format dziesiętny
     */
    private function getGpsCoordinate($coordinate, $hemisphere) {
        if (!is_array($coordinate) || count($coordinate) < 3) {
            return 0;
        }

        $degrees = $this->parseExifFraction($coordinate[0]);
        $minutes = $this->parseExifFraction($coordinate[1]);
        $seconds = $this->parseExifFraction($coordinate[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($hemisphere == 'S' || $hemisphere == 'W') {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * Opisuje ustawienia flesza
     */
    private function getFlashDescription($flashValue) {
        $flashDescriptions = [
            0 => 'Flesz nie został użyty',
            1 => 'Flesz został użyty',
            5 => 'Flesz został użyty, brak światła zwrotnego',
            7 => 'Flesz został użyty, światło zwrotne wykryte',
            9 => 'Flesz został użyty, tryb obowiązkowy',
            13 => 'Flesz został użyty, tryb obowiązkowy, brak światła zwrotnego',
            15 => 'Flesz został użyty, tryb obowiązkowy, światło zwrotne wykryte',
            16 => 'Flesz nie został użyty, tryb obowiązkowy',
            24 => 'Flesz nie został użyty, tryb automatyczny',
            25 => 'Flesz został użyty, tryb automatyczny',
            29 => 'Flesz został użyty, tryb automatyczny, brak światła zwrotnego',
            31 => 'Flesz został użyty, tryb automatyczny, światło zwrotne wykryte',
            32 => 'Brak funkcji flesza',
            65 => 'Flesz został użyty, redukcja czerwonych oczu',
            69 => 'Flesz został użyty, redukcja czerwonych oczu, brak światła zwrotnego',
            71 => 'Flesz został użyty, redukcja czerwonych oczu, światło zwrotne wykryte',
            73 => 'Flesz został użyty, obowiązkowy, redukcja czerwonych oczu',
            77 => 'Flesz został użyty, obowiązkowy, redukcja czerwonych oczu, brak światła zwrotnego',
            79 => 'Flesz został użyty, obowiązkowy, redukcja czerwonych oczu, światło zwrotne wykryte',
            89 => 'Flesz został użyty, automatyczny, redukcja czerwonych oczu',
            93 => 'Flesz został użyty, automatyczny, redukcja czerwonych oczu, brak światła zwrotnego',
            95 => 'Flesz został użyty, automatyczny, redukcja czerwonych oczu, światło zwrotne wykryte'
        ];

        return $flashDescriptions[$flashValue] ?? 'Nieznane ustawienie flesza (' . $flashValue . ')';
    }

    /**
     * Czyści komentarz użytkownika z niepotrzebnych znaków kontrolnych
     */
    private function cleanUserComment($userComment) {
        if (empty($userComment)) {
            return '';
        }

        // Usuń znaki kontrolne i prefiks UNICODE
        $cleaned = preg_replace('/^(UNICODE\x00|ASCII\x00\x00\x00)/', '', $userComment);
        $cleaned = trim($cleaned, "\x00\x20\t\n\r\0\x0B");

        // Sprawdź czy to czytelny tekst
        if (mb_check_encoding($cleaned, 'UTF-8')) {
            return $cleaned;
        }

        return '';
    }

}
