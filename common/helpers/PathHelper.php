<?php

namespace common\helpers;

use Yii;
use common\models\Settings;

/**
 * Helper do zarządzania ścieżkami plików
 */
class PathHelper
{
    /**
     * Pobiera ścieżkę do katalogu przechowywania zdjęć
     * @param string $type Typ katalogu: 'temp', 'thumbnails', 'watermark', 'deleted'
     * @return string
     */
    public static function getUploadPath($type = 'temp')
    {
        $frontendMode = Settings::getSetting('upload.frontend_mode', false);
        $frontendPath = Settings::getSetting('upload.frontend_path', '');
        
        if ($frontendMode && !empty($frontendPath)) {
            // Tryb frontendowy - zapisuj do katalogu frontendu
            switch ($type) {
                case 'temp':
                    return $frontendPath . '/uploads/photos';
                case 'thumbnails':
                    return $frontendPath . '/uploads/thumbnails';
                case 'watermark':
                    return $frontendPath . '/uploads/watermark';
                case 'deleted':
                    return $frontendPath . '/uploads/deleted';
                default:
                    return $frontendPath . '/uploads/' . $type;
            }
        } else {
            // Tryb standardowy - zapisuj lokalnie w backendzie
            switch ($type) {
                case 'temp':
                    return Yii::getAlias('@webroot/uploads/temp');
                case 'thumbnails':
                    return Yii::getAlias('@webroot/uploads/thumbnails');
                case 'watermark':
                    return Yii::getAlias('@webroot/uploads/watermark');
                case 'deleted':
                    return Yii::getAlias('@webroot/uploads/deleted');
                default:
                    return Yii::getAlias('@webroot/uploads/' . $type);
            }
        }
    }
    
    /**
     * Pobiera URL do zdjęć dla frontendu
     * @param string $type Typ katalogu
     * @return string
     */
    public static function getUploadUrl($type = 'temp')
    {
        $frontendMode = Settings::getSetting('upload.frontend_mode', false);
        $frontendUrl = Settings::getSetting('upload.frontend_url', '');
        
        if ($frontendMode && !empty($frontendUrl)) {
            // Tryb frontendowy
            switch ($type) {
                case 'temp':
                    return $frontendUrl . '/uploads/photos';
                case 'thumbnails':
                    return $frontendUrl . '/uploads/thumbnails';
                case 'watermark':
                    return $frontendUrl . '/uploads/watermark';
                default:
                    return $frontendUrl . '/uploads/' . $type;
            }
        } else {
            // Tryb standardowy
            switch ($type) {
                case 'temp':
                    return Yii::getAlias('@web/uploads/temp');
                case 'thumbnails':
                    return Yii::getAlias('@web/uploads/thumbnails');
                case 'watermark':
                    return Yii::getAlias('@web/uploads/watermark');
                default:
                    return Yii::getAlias('@web/uploads/' . $type);
            }
        }
    }
    
    /**
     * Sprawdza i tworzy wymagane katalogi
     * @param string $type
     * @return bool
     */
    public static function ensureDirectoryExists($type = 'temp')
    {
        $path = self::getUploadPath($type);
        
        if (!is_dir($path)) {
            return \yii\helpers\FileHelper::createDirectory($path, 0777, true);
        }
        
        return true;
    }
    
    /**
     * Pobiera pełną ścieżkę do pliku zdjęcia
     * @param string $fileName
     * @param string $type
     * @return string
     */
    public static function getPhotoPath($fileName, $type = 'temp')
    {
        return self::getUploadPath($type) . '/' . $fileName;
    }
    
    /**
     * Pobiera URL do pliku zdjęcia
     * @param string $fileName
     * @param string $type
     * @return string
     */
    public static function getPhotoUrl($fileName, $type = 'temp')
    {
        return self::getUploadUrl($type) . '/' . $fileName;
    }
    
    /**
     * Pobiera ścieżkę do miniatury
     * @param string $sizeName
     * @param string $fileName
     * @return string
     */
    public static function getThumbnailPath($sizeName, $fileName)
    {
        return self::getUploadPath('thumbnails') . '/' . $sizeName . '_' . $fileName;
    }
    
    /**
     * Pobiera URL do miniatury - POPRAWIONE dla frontend mode
     * @param string $sizeName
     * @param string $fileName
     * @return string
     */
    public static function getThumbnailUrl($sizeName, $fileName)
    {
        return self::getUploadUrl('thumbnails') . '/' . $sizeName . '_' . $fileName;
    }
    
    /**
     * Sprawdza czy miniatura istnieje - dla backend sprawdza również frontend
     * @param string $sizeName
     * @param string $fileName
     * @return bool
     */
    public static function thumbnailExists($sizeName, $fileName)
    {
        $localPath = self::getThumbnailPath($sizeName, $fileName);
        
        if (file_exists($localPath)) {
            return true;
        }
        
        // Jeśli jesteśmy w trybie frontendowym i lokalnie nie ma, sprawdź frontend
        $frontendMode = Settings::getSetting('upload.frontend_mode', false);
        if ($frontendMode) {
            $frontendPath = Settings::getSetting('upload.frontend_path', '');
            if (!empty($frontendPath)) {
                $frontendThumbnailPath = $frontendPath . '/uploads/thumbnails/' . $sizeName . '_' . $fileName;
                return file_exists($frontendThumbnailPath);
            }
        }
        
        return false;
    }
    
    /**
     * Pobiera dostępną miniaturę - sprawdza lokalne i frontend ścieżki
     * @param string $sizeName
     * @param string $fileName
     * @return array|null ['path' => string, 'url' => string] lub null jeśli nie ma
     */
    public static function getAvailableThumbnail($sizeName, $fileName)
    {
        $frontendMode = Settings::getSetting('upload.frontend_mode', false);
        
        if ($frontendMode) {
            $frontendPath = Settings::getSetting('upload.frontend_path', '');
            $frontendUrl = Settings::getSetting('upload.frontend_url', '');
            
            if (!empty($frontendPath) && !empty($frontendUrl)) {
                $frontendThumbnailPath = $frontendPath . '/uploads/thumbnails/' . $sizeName . '_' . $fileName;
                if (file_exists($frontendThumbnailPath)) {
                    return [
                        'path' => $frontendThumbnailPath,
                        'url' => $frontendUrl . '/uploads/thumbnails/' . $sizeName . '_' . $fileName
                    ];
                }
            }
        }
        
        // Sprawdź lokalną ścieżkę
        $localPath = self::getThumbnailPath($sizeName, $fileName);
        if (file_exists($localPath)) {
            return [
                'path' => $localPath,
                'url' => self::getUploadUrl('thumbnails') . '/' . $sizeName . '_' . $fileName
            ];
        }
        
        return null;
    }
    
    /**
     * Sprawdza czy plik istnieje w danym typie katalogu
     * @param string $fileName
     * @param string $type
     * @return bool
     */
    public static function fileExists($fileName, $type = 'temp')
    {
        return file_exists(self::getPhotoPath($fileName, $type));
    }
    
    /**
     * Usuwa plik z danego typu katalogu
     * @param string $fileName
     * @param string $type
     * @return bool
     */
    public static function deleteFile($fileName, $type = 'temp')
    {
        $filePath = self::getPhotoPath($fileName, $type);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true; // Plik nie istnieje, więc "usunięty"
    }
    
    /**
     * Usuwa wszystkie miniatury dla danego pliku - zarówno lokalne jak i frontend
     * @param string $fileName
     * @return int Liczba usuniętych miniatur
     */
    public static function deleteThumbnails($fileName)
    {
        $deleted = 0;
        $thumbnailSizes = \common\models\ThumbnailSize::find()->all();
        
        foreach ($thumbnailSizes as $size) {
            // Usuń lokalną miniaturę
            $thumbnailPath = self::getThumbnailPath($size->name, $fileName);
            if (file_exists($thumbnailPath) && unlink($thumbnailPath)) {
                $deleted++;
            }
            
            // Usuń miniaturę z frontendu jeśli jest w trybie frontendowym
            $frontendMode = Settings::getSetting('upload.frontend_mode', false);
            if ($frontendMode) {
                $frontendPath = Settings::getSetting('upload.frontend_path', '');
                if (!empty($frontendPath)) {
                    $frontendThumbnailPath = $frontendPath . '/uploads/thumbnails/' . $size->name . '_' . $fileName;
                    if (file_exists($frontendThumbnailPath) && unlink($frontendThumbnailPath)) {
                        $deleted++;
                    }
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * Przenosi plik między katalogami
     * @param string $fileName
     * @param string $fromType
     * @param string $toType
     * @return bool
     */
    public static function moveFile($fileName, $fromType, $toType)
    {
        $sourcePath = self::getPhotoPath($fileName, $fromType);
        $destPath = self::getPhotoPath($fileName, $toType);
        
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // Upewnij się, że katalog docelowy istnieje
        self::ensureDirectoryExists($toType);
        
        return rename($sourcePath, $destPath);
    }
    
    /**
     * Kopiuje plik między katalogami
     * @param string $fileName
     * @param string $fromType
     * @param string $toType
     * @return bool
     */
    public static function copyFile($fileName, $fromType, $toType)
    {
        $sourcePath = self::getPhotoPath($fileName, $fromType);
        $destPath = self::getPhotoPath($fileName, $toType);
        
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // Upewnij się, że katalog docelowy istnieje
        self::ensureDirectoryExists($toType);
        
        return copy($sourcePath, $destPath);
    }
    
    /**
     * Pobiera rozmiar pliku
     * @param string $fileName
     * @param string $type
     * @return int|false Rozmiar pliku w bajtach lub false jeśli plik nie istnieje
     */
    public static function getFileSize($fileName, $type = 'temp')
    {
        $filePath = self::getPhotoPath($fileName, $type);
        
        if (file_exists($filePath)) {
            return filesize($filePath);
        }
        
        return false;
    }
    
    /**
     * Pobiera typ MIME pliku
     * @param string $fileName
     * @param string $type
     * @return string|false Typ MIME lub false jeśli plik nie istnieje
     */
    public static function getMimeType($fileName, $type = 'temp')
    {
        $filePath = self::getPhotoPath($fileName, $type);
        
        if (file_exists($filePath)) {
            return \yii\helpers\FileHelper::getMimeType($filePath);
        }
        
        return false;
    }
    
    /**
     * Sprawdza czy tryb frontendowy jest aktywny
     * @return bool
     */
    public static function isFrontendMode()
    {
        return (bool)Settings::getSetting('upload.frontend_mode', false);
    }
    
    /**
     * Pobiera konfigurację ścieżek
     * @return array
     */
    public static function getConfiguration()
    {
        return [
            'frontend_mode' => self::isFrontendMode(),
            'frontend_path' => Settings::getSetting('upload.frontend_path', ''),
            'frontend_url' => Settings::getSetting('upload.frontend_url', ''),
            'preserve_names' => (bool)Settings::getSetting('upload.preserve_original_names', '1'),
        ];
    }
    
    /**
     * Generuje nazwę pliku z zachowaniem oryginalnej nazwy i hashem
     * @param string $originalName Oryginalna nazwa pliku (bez rozszerzenia)
     * @param string $extension Rozszerzenie pliku
     * @param int $hashLength Długość hasza (domyślnie 8)
     * @return string
     */
    public static function generateFileName($originalName, $extension, $hashLength = 8)
    {
        $preserveNames = Settings::getSetting('upload.preserve_original_names', '1');
        
        if ($preserveNames == '1') {
            // Oczyść oryginalną nazwę z niebezpiecznych znaków
            $cleanName = self::sanitizeFileName($originalName);
            $hash = substr(Yii::$app->security->generateRandomString(12), 0, $hashLength);
            return $cleanName . '_' . $hash . '.' . $extension;
        } else {
            // Generuj losową nazwę
            return Yii::$app->security->generateRandomString(16) . '.' . $extension;
        }
    }
    
    /**
     * Czyści nazwę pliku z niebezpiecznych znaków
     * @param string $fileName
     * @return string
     */
    public static function sanitizeFileName($fileName)
    {
        // Usuń niebezpieczne znaki i zostaw tylko alfanumeryczne, myślniki i podkreślenia
        $clean = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fileName);
        
        // Usuń wielokrotne podkreślenia
        $clean = preg_replace('/_{2,}/', '_', $clean);
        
        // Usuń podkreślenia z początku i końca
        $clean = trim($clean, '_');
        
        // Jeśli nazwa jest pusta po oczyszczeniu, użyj domyślnej
        if (empty($clean)) {
            $clean = 'photo';
        }
        
        return $clean;
    }
    
    /**
     * Pobiera oryginalną nazwę pliku z nazwy zawierającej hash
     * @param string $fileName Nazwa pliku z hashem
     * @return string Oryginalna nazwa bez rozszerzenia
     */
    public static function getOriginalName($fileName)
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Sprawdź czy nazwa zawiera hash (format: nazwa_hash)
        if (preg_match('/^(.+)_[A-Za-z0-9]{8}$/', $baseName, $matches)) {
            return $matches[1];
        }
        
        return $baseName;
    }
    
    /**
     * Sprawdza dostępność katalogów i uprawnień
     * @return array Status każdego katalogu
     */
    public static function checkDirectoryStatus()
    {
        $types = ['temp', 'thumbnails', 'watermark', 'deleted'];
        $status = [];
        
        foreach ($types as $type) {
            $path = self::getUploadPath($type);
            $status[$type] = [
                'path' => $path,
                'exists' => is_dir($path),
                'writable' => is_dir($path) && is_writable($path),
                'readable' => is_dir($path) && is_readable($path),
            ];
        }
        
        return $status;
    }
    
    /**
     * Tworzy wszystkie wymagane katalogi
     * @return array Wyniki tworzenia katalogów
     */
    public static function createAllDirectories()
    {
        $types = ['temp', 'thumbnails', 'watermark', 'deleted'];
        $results = [];
        
        foreach ($types as $type) {
            $results[$type] = self::ensureDirectoryExists($type);
        }
        
        return $results;
    }
}
?>