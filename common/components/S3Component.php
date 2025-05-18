<?php
namespace common\components;

use Aws\S3\S3Client;
use yii\base\Component;
use common\models\Settings;

/**
 * Komponent do integracji z AWS S3
 */
class S3Component extends Component
{
    /**
     * @var S3Client Klient AWS S3
     */
    private $_client;
    
    /**
     * Inicjalizacja komponentu
     */
    public function init()
    {
        parent::init();
    }
    
    /**
     * Pobiera klienta S3
     * 
     * @return S3Client
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $settings = $this->getSettings();
            
            $this->_client = new S3Client([
                'version' => 'latest',
                'region' => $settings['region'],
                'credentials' => [
                    'key' => $settings['access_key'],
                    'secret' => $settings['secret_key'],
                ],
            ]);
        }
        
        return $this->_client;
    }
    
    /**
     * Pobiera ustawienia S3
     * 
     * @return array
     */
    public function getSettings()
    {
        return [
            'bucket' => Settings::getSetting('s3.bucket', ''),
            'region' => Settings::getSetting('s3.region', 'eu-central-1'),
            'access_key' => Settings::getSetting('s3.access_key', ''),
            'secret_key' => Settings::getSetting('s3.secret_key', ''),
            'directory' => Settings::getSetting('s3.directory', 'photos'),
            'deleted_directory' => Settings::getSetting('s3.deleted_directory', 'deleted'),
        ];
    }
    
    /**
     * Wysyła obiekt do S3
     * 
     * @param array $params Parametry
     * @return \Aws\Result
     */
    public function putObject($params)
    {
        return $this->getClient()->putObject($params);
    }
    
    /**
     * Pobiera obiekt z S3
     * 
     * @param array $params Parametry
     * @return \Aws\Result
     */
    public function getObject($params)
    {
        return $this->getClient()->getObject($params);
    }
    
    /**
     * Kopiuje obiekt w S3
     * 
     * @param array $params Parametry
     * @return \Aws\Result
     */
    public function copyObject($params)
    {
        return $this->getClient()->copyObject($params);
    }
    
    /**
     * Usuwa obiekt z S3
     * 
     * @param array $params Parametry
     * @return \Aws\Result
     */
    public function deleteObject($params)
    {
        return $this->getClient()->deleteObject($params);
    }
    
    /**
     * Listuje obiekty w S3
     * 
     * @param array $params Parametry
     * @return \Aws\Result
     */
    public function listObjects($params)
    {
        return $this->getClient()->listObjects($params);
    }
    
    /**
     * Testuje połączenie z S3
     * 
     * @return bool|string Sukces lub komunikat błędu
     */
    public function testConnection()
    {
        $settings = $this->getSettings();
        
        // Walidacja wymaganych ustawień
        if (empty($settings['bucket']) || empty($settings['region']) || 
            empty($settings['access_key']) || empty($settings['secret_key'])) {
            return 'Brakujące wymagane ustawienia S3';
        }
        
        try {
            // Testowanie połączenia poprzez listowanie zawartości bucketu z limitem 1
            $result = $this->listObjects([
                'Bucket' => $settings['bucket'],
                'MaxKeys' => 1
            ]);
            
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * Synchronizuje lokalne pliki z S3
     * 
     * @param bool $deleteLocal Czy usunąć lokalne pliki po synchronizacji
     * @return array Wynik synchronizacji
     */
    public function syncFiles($deleteLocal = false)
    {
        $settings = $this->getSettings();
        $result = [
            'success' => false,
            'synced' => 0,
            'errors' => []
        ];
        
        // Walidacja wymaganych ustawień
        if (empty($settings['bucket']) || empty($settings['region']) || 
            empty($settings['access_key']) || empty($settings['secret_key'])) {
            $result['errors'][] = 'Brakujące wymagane ustawienia S3';
            return $result;
        }
        
        // Pobieranie zdjęć do synchronizacji
        $photos = \common\models\Photo::find()
            ->where(['status' => \common\models\Photo::STATUS_ACTIVE])
            ->andWhere(['OR', ['s3_path' => null], ['s3_path' => '']])
            ->all();
        
        foreach ($photos as $photo) {
            $tempPath = \Yii::getAlias('@uploads/temp/' . $photo->file_name);
            
            // Sprawdzenie czy plik istnieje lokalnie
            if (!file_exists($tempPath)) {
                $result['errors'][] = 'Plik nie istnieje: ' . $photo->file_name;
                continue;
            }
            
            // Generowanie ścieżki na S3
            $s3Key = $settings['directory'] . '/' . date('Y/m/d', $photo->created_at) . '/' . $photo->file_name;
            
            try {
                // Wrzucanie pliku na S3
                $this->putObject([
                    'Bucket' => $settings['bucket'],
                    'Key' => $s3Key,
                    'SourceFile' => $tempPath,
                    'ContentType' => $photo->mime_type
                ]);
                
                // Aktualizacja rekordu w bazie
                $photo->s3_path = $s3Key;
                $photo->updated_at = time();
                
                if ($photo->save()) {
                    $result['synced']++;
                    
                    // Usuwanie lokalnej kopii jeśli wymagane
                    if ($deleteLocal) {
                        unlink($tempPath);
                    }
                } else {
                    $result['errors'][] = 'Błąd zapisu do bazy danych: ' . json_encode($photo->errors);
                }
            } catch (\Exception $e) {
                $result['errors'][] = 'Błąd S3: ' . $e->getMessage();
            }
        }
        
        $result['success'] = true;
        
        return $result;
    }
}