<?php
// api/controllers/PhotosController.php
namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use common\models\Photo;
use common\models\PhotoTag;
use common\models\PhotoCategory;
use common\models\Tag;
use common\models\Category;
use common\models\ThumbnailSize;

class PhotosController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['random'];
        return $behaviors;
    }
    
    public function actionUpload()
    {
        // Sprawdzenie uprawnień
        if (!Yii::$app->user->can('managePhotos')) {
            throw new ForbiddenHttpException('Brak uprawnień do wgrywania zdjęć');
        }
        
        // Pobieranie pliku
        $uploadedFile = UploadedFile::getInstanceByName('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('Brak pliku');
        }
        
        // Walidacja typu MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile->type, $allowedTypes)) {
            throw new BadRequestHttpException('Nieprawidłowy typ pliku');
        }
        
        // Generowanie unikalnej nazwy pliku
        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $uploadedFile->extension;
        $filePath = Yii::getAlias('@webroot/uploads/temp/' . $fileName);
        
        // Zapisywanie pliku
        if (!$uploadedFile->saveAs($filePath)) {
            throw new ServerErrorHttpException('Błąd zapisu pliku');
        }
        
        // Odczytywanie wymiarów i metadanych
        $imageInfo = Yii::$app->imageProcessor->getImageInfo($filePath);
        
        // Tworzenie rekordu w bazie
        $photo = new Photo();
        $photo->title = pathinfo($uploadedFile->name, PATHINFO_FILENAME); // Domyślny tytuł to nazwa pliku
        $photo->file_name = $fileName;
        $photo->file_size = $uploadedFile->size;
        $photo->mime_type = $uploadedFile->type;
        $photo->width = $imageInfo['width'];
        $photo->height = $imageInfo['height'];
        $photo->status = Photo::STATUS_QUEUE; // W poczekalni
        $photo->is_public = false;
        $photo->created_at = time();
        $photo->updated_at = time();
        $photo->created_by = Yii::$app->user->id;
        
        if (!$photo->save()) {
            unlink($filePath); // Usuwanie pliku jeśli zapis do bazy się nie powiedzie
            throw new ServerErrorHttpException('Błąd zapisu danych: ' . json_encode($photo->errors));
        }
        
        // Generowanie miniatur
        $thumbnails = Yii::$app->imageProcessor->createThumbnails($filePath, $fileName);
        
        return [
            'success' => true,
            'photo' => array_merge(
                $photo->getAttributes(),
                ['thumbnails' => $thumbnails]
            )
        ];
    }
    
    public function actionQueue()
    {
        // Sprawdzenie uprawnień
        if (!Yii::$app->user->can('managePhotos')) {
            throw new ForbiddenHttpException('Brak uprawnień do przeglądania poczekalni');
        }
        
        $query = Photo::find()->where(['status' => Photo::STATUS_QUEUE]);
        
        // Sortowanie (domyślnie od najnowszych)
        $sort = Yii::$app->request->get('sort', '-created_at');
        if ($sort[0] === '-') {
            $query->orderBy([substr($sort, 1) => SORT_DESC]);
        } else {
            $query->orderBy([$sort => SORT_ASC]);
        }
        
        // Filtrowanie po dacie
        $dateFrom = Yii::$app->request->get('date_from');
        if ($dateFrom) {
            $query->andWhere(['>=', 'created_at', strtotime($dateFrom)]);
        }
        
        $dateTo = Yii::$app->request->get('date_to');
        if ($dateTo) {
            $query->andWhere(['<=', 'created_at', strtotime($dateTo . ' 23:59:59')]);
        }
        
        // Paginacja
        $perPage = (int)Yii::$app->request->get('per-page', 20);
        $page = (int)Yii::$app->request->get('page', 1);
        
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        
        $photos = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();
        
        $items = [];
        foreach ($photos as $photo) {
            // Pobieranie dostępnych miniatur
            $thumbnails = [];
            $thumbnailSizes = ThumbnailSize::find()->all();
            
            foreach ($thumbnailSizes as $size) {
                $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);
                $thumbnails[$size->name] = $thumbnailUrl;
            }
            
            $items[] = array_merge(
                $photo->getAttributes(),
                ['thumbnails' => $thumbnails]
            );
        }
        
        return [
            'photos' => $items,
            'pagination' => [
                'total' => $totalCount,
                'perPage' => $perPage,
                'currentPage' => $page,
                'lastPage' => ceil($totalCount / $perPage)
            ]
        ];
    }
    
    public function actionView($id)
    {
        $photo = Photo::findOne($id);
        if (!$photo) {
            throw new NotFoundHttpException('Zdjęcie nie istnieje');
        }
        
        // Sprawdzenie uprawnień dostępu
        $isAdmin = Yii::$app->user->can('managePhotos');
        $isOwner = $photo->created_by === Yii::$app->user->id;
        
        if (!$isAdmin && !$isOwner && !$photo->is_public) {
            throw new ForbiddenHttpException('Brak dostępu do tego zdjęcia');
        }
        
        // Pobieranie adresów miniatur
        $thumbnails = [];
        $thumbnailSizes = ThumbnailSize::find()->all();
        
        foreach ($thumbnailSizes as $size) {
            $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);
            $thumbnails[$size->name] = $thumbnailUrl;
        }
        
        // Pobieranie powiązanych tagów
        $tags = $photo->getTags()->asArray()->all();
        
        // Pobieranie powiązanych kategorii
        $categories = $photo->getCategories()->asArray()->all();
        
        // Pobieranie dodatkowych metadanych - tylko dla admina i właściciela
        $metadata = null;
        if ($isAdmin || $isOwner) {
            $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
            if (file_exists($tempPath)) {
                $imageInfo = Yii::$app->imageProcessor->getImageInfo($tempPath);
                $metadata = [
                    'exif' => $imageInfo['exif'] ?: null,
                    'dimensions' => [
                        'width' => $photo->width,
                        'height' => $photo->height,
                        'ratio' => $photo->width / $photo->height
                    ],
                    'fileSize' => $photo->file_size,
                    's3Path' => $photo->s3_path
                ];
            }
        }
        
        return [
            'photo' => array_merge(
                $photo->getAttributes(),
                [
                    'thumbnails' => $thumbnails,
                    'tags' => $tags,
                    'categories' => $categories,
                    'metadata' => $metadata
               ]
           )
       ];
   }
   
   public function actionUpdate($id)
   {
       // Sprawdzenie uprawnień
       if (!Yii::$app->user->can('managePhotos')) {
           throw new ForbiddenHttpException('Brak uprawnień do edycji zdjęć');
       }
       
       $photo = Photo::findOne($id);
       if (!$photo) {
           throw new NotFoundHttpException('Zdjęcie nie istnieje');
       }
       
       // Aktualizacja podstawowych danych
       $request = Yii::$app->request->bodyParams;
       
       if (isset($request['title'])) {
           $photo->title = $request['title'];
       }
       if (isset($request['description'])) {
           $photo->description = $request['description'];
       }
       if (isset($request['is_public'])) {
           $photo->is_public = (bool)$request['is_public'];
       }
       if (isset($request['status'])) {
           $photo->status = (int)$request['status'];
       }
       
       $photo->updated_at = time();
       
       // Rozpoczęcie transakcji
       $transaction = Yii::$app->db->beginTransaction();
       try {
           if (!$photo->save()) {
               throw new BadRequestHttpException('Błąd walidacji: ' . json_encode($photo->errors));
           }
           
           // Aktualizacja tagów jeśli podane
           if (isset($request['tags'])) {
               // Usuwanie wszystkich obecnych powiązań
               PhotoTag::deleteAll(['photo_id' => $id]);
               
               // Dodawanie nowych powiązań
               foreach ($request['tags'] as $tagId) {
                   $tag = Tag::findOne($tagId);
                   if ($tag) {
                       $photoTag = new PhotoTag();
                       $photoTag->photo_id = $id;
                       $photoTag->tag_id = $tagId;
                       if (!$photoTag->save()) {
                           throw new ServerErrorHttpException('Błąd podczas zapisywania powiązania z tagiem');
                       }
                       
                       // Aktualizacja licznika użyć tagu
                       $tag->frequency += 1;
                       $tag->save();
                   }
               }
           }
           
           // Aktualizacja kategorii jeśli podane
           if (isset($request['categories'])) {
               // Usuwanie wszystkich obecnych powiązań
               PhotoCategory::deleteAll(['photo_id' => $id]);
               
               // Dodawanie nowych powiązań
               foreach ($request['categories'] as $categoryId) {
                   $category = Category::findOne($categoryId);
                   if ($category) {
                       $photoCategory = new PhotoCategory();
                       $photoCategory->photo_id = $id;
                       $photoCategory->category_id = $categoryId;
                       if (!$photoCategory->save()) {
                           throw new ServerErrorHttpException('Błąd podczas zapisywania powiązania z kategorią');
                       }
                   }
               }
           }
           
           $transaction->commit();
       } catch (\Exception $e) {
           $transaction->rollBack();
           throw $e;
       }
       
       // Pobieranie zaktualizowanych danych
       $updatedPhoto = Photo::findOne($id);
       $thumbnails = [];
       $thumbnailSizes = ThumbnailSize::find()->all();
       
       foreach ($thumbnailSizes as $size) {
           $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $updatedPhoto->file_name);
           $thumbnails[$size->name] = $thumbnailUrl;
       }
       
       $updatedTags = $updatedPhoto->getTags()->asArray()->all();
       $updatedCategories = $updatedPhoto->getCategories()->asArray()->all();
       
       return [
           'success' => true,
           'photo' => array_merge(
               $updatedPhoto->getAttributes(),
               [
                   'thumbnails' => $thumbnails,
                   'tags' => $updatedTags,
                   'categories' => $updatedCategories
               ]
           )
       ];
   }
   
   public function actionDelete($id)
   {
       // Sprawdzenie uprawnień
       if (!Yii::$app->user->can('managePhotos')) {
           throw new ForbiddenHttpException('Brak uprawnień do usuwania zdjęć');
       }
       
       $photo = Photo::findOne($id);
       if (!$photo) {
           throw new NotFoundHttpException('Zdjęcie nie istnieje');
       }
       
       // Rozpoczęcie transakcji
       $transaction = Yii::$app->db->beginTransaction();
       try {
           // Usuwanie powiązań z tagami
           PhotoTag::deleteAll(['photo_id' => $id]);
           
           // Usuwanie powiązań z kategoriami
           PhotoCategory::deleteAll(['photo_id' => $id]);
           
           // Przenoszenie oryginału do katalogu "deleted" na S3 (jeśli istnieje)
           if ($photo->s3_path) {
               $s3Component = Yii::$app->s3;
               $s3Settings = $s3Component->getSettings();
               
               // Pobieranie obecnej ścieżki na S3
               $currentKey = $photo->s3_path;
               
               // Nowa ścieżka w katalogu "deleted"
               $newKey = str_replace(
                   $s3Settings['directory'], 
                   $s3Settings['deleted_directory'], 
                   $currentKey
               );
               
               // Kopiowanie obiektu do nowej lokalizacji
               $s3Component->copyObject([
                   'Bucket' => $s3Settings['bucket'],
                   'CopySource' => $s3Settings['bucket'] . '/' . $currentKey,
                   'Key' => $newKey
               ]);
               
               // Usuwanie oryginalnego obiektu
               $s3Component->deleteObject([
                   'Bucket' => $s3Settings['bucket'],
                   'Key' => $currentKey
               ]);
           }
           
           // Usuwanie miniatur z lokalnego serwera
           $thumbnailSizes = ThumbnailSize::find()->all();
           foreach ($thumbnailSizes as $size) {
               $thumbnailPath = Yii::getAlias('@webroot/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);
               if (file_exists($thumbnailPath)) {
                   unlink($thumbnailPath);
               }
           }
           
           // Usuwanie pliku tymczasowego jeśli istnieje
           $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
           if (file_exists($tempPath)) {
               unlink($tempPath);
           }
           
           // Aktualizacja statusu zdjęcia na "usunięte" (miękkie usuwanie)
           $photo->status = Photo::STATUS_DELETED;
           $photo->updated_at = time();
           
           if (!$photo->save()) {
               throw new ServerErrorHttpException('Błąd podczas usuwania zdjęcia');
           }
           
           $transaction->commit();
       } catch (\Exception $e) {
           $transaction->rollBack();
           throw $e;
       }
       
       return ['success' => true];
   }
   
   public function actionApprove($id)
   {
       // Sprawdzenie uprawnień
       if (!Yii::$app->user->can('managePhotos')) {
           throw new ForbiddenHttpException('Brak uprawnień do zatwierdzania zdjęć');
       }
       
       $photo = Photo::findOne($id);
       if (!$photo) {
           throw new NotFoundHttpException('Zdjęcie nie istnieje');
       }
       
       if ($photo->status !== Photo::STATUS_QUEUE) {
           throw new BadRequestHttpException('Zdjęcie nie jest w poczekalni');
       }
       
       // Pobranie ścieżki do pliku tymczasowego
       $tempPath = Yii::getAlias('@webroot/uploads/temp/' . $photo->file_name);
       if (!file_exists($tempPath)) {
           throw new ServerErrorHttpException('Plik zdjęcia nie istnieje');
       }
       
       // Pobranie ustawień S3
       $s3Component = Yii::$app->s3;
       $s3Settings = $s3Component->getSettings();
       
       // Generowanie ścieżki na S3
       $s3Key = $s3Settings['directory'] . '/' . date('Y/m/d') . '/' . $photo->file_name;
       
       // Wrzucanie pliku na S3
       try {
           $result = $s3Component->putObject([
               'Bucket' => $s3Settings['bucket'],
               'Key' => $s3Key,
               'SourceFile' => $tempPath,
               'ContentType' => $photo->mime_type
           ]);
           
           // Aktualizacja rekordu w bazie
           $photo->s3_path = $s3Key;
           $photo->status = Photo::STATUS_ACTIVE;
           $photo->updated_at = time();
           
           if (!$photo->save()) {
               throw new ServerErrorHttpException('Błąd podczas aktualizacji danych zdjęcia');
           }
           
           // Usuwanie pliku tymczasowego po potwierdzeniu przesłania na S3
           unlink($tempPath);
           
           // Pobieranie zaktualizowanych danych zdjęcia
           $thumbnails = [];
           $thumbnailSizes = ThumbnailSize::find()->all();
           
           foreach ($thumbnailSizes as $size) {
               $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $size->name . '_' . $photo->file_name);
               $thumbnails[$size->name] = $thumbnailUrl;
           }
           
           $tags = $photo->getTags()->asArray()->all();
           $categories = $photo->getCategories()->asArray()->all();
           
           return [
               'success' => true,
               'photo' => array_merge(
                   $photo->getAttributes(),
                   [
                       'thumbnails' => $thumbnails,
                       'tags' => $tags,
                       'categories' => $categories
                   ]
               )
           ];
       } catch (\Exception $e) {
           throw new ServerErrorHttpException('Błąd podczas przesyłania pliku na S3: ' . $e->getMessage());
       }
   }
   
   public function actionRandom()
   {
       $limit = (int)Yii::$app->request->get('limit', 10);
       // Ograniczenie maksymalnej liczby zdjęć
       $limit = min($limit, 50);
       
       // Pobieranie losowych publicznych zdjęć
       $photos = Photo::find()
           ->where(['status' => Photo::STATUS_ACTIVE, 'is_public' => true])
           ->orderBy('RAND()')
           ->limit($limit)
           ->all();
       
       // Przygotowanie wyników
       $items = [];
       foreach ($photos as $photo) {
           // Pobieranie tylko podstawowej miniatury ze znakiem wodnym
           $thumbnailSize = ThumbnailSize::findOne(['watermark' => true]);
           $thumbnailUrl = '';
           
           if ($thumbnailSize) {
               $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $thumbnailSize->name . '_' . $photo->file_name);
           } else {
               // Jeśli nie ma miniatury ze znakiem wodnym, pobieramy pierwszą dostępną
               $thumbnailSize = ThumbnailSize::find()->one();
               if ($thumbnailSize) {
                   $thumbnailUrl = Yii::getAlias('@web/uploads/thumbnails/' . $thumbnailSize->name . '_' . $photo->file_name);
               }
           }
           
           $items[] = [
               'id' => $photo->id,
               'title' => $photo->title,
               'description' => $photo->description,
               'thumbnail' => $thumbnailUrl,
               'width' => $photo->width,
               'height' => $photo->height,
               'created_at' => $photo->created_at
           ];
       }
       
       return ['photos' => $items];
   }
}