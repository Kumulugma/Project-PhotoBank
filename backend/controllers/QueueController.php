<?php

namespace backend\controllers;

use Yii;
use common\models\QueuedJob;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\search\QueuedJobSearch;

/**
 * QueueController implements the CRUD actions for QueuedJob model.
 */
class QueueController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'retry' => ['POST'],
                    'process' => ['POST'],
                    'clear-completed' => ['POST'],
                    'clear-failed' => ['POST'],
                    'clear-processing' => ['POST'],
                    'run-processor' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all QueuedJob models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new QueuedJobSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Get statistics
        $stats = [
            'total' => QueuedJob::find()->count(),
            'pending' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_PENDING])->count(),
            'processing' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_PROCESSING])->count(),
            'completed' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_COMPLETED])->count(),
            'failed' => QueuedJob::find()->where(['status' => QueuedJob::STATUS_FAILED])->count(),
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Displays a single QueuedJob model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing QueuedJob model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Job deleted successfully.'));

        return $this->redirect(['index']);
    }

    /**
     * Retries a failed job.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionRetry($id)
    {
        $model = $this->findModel($id);
        
        if ($model->status !== QueuedJob::STATUS_FAILED) {
            Yii::$app->session->setFlash('error', 'Only failed jobs can be retried.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        // Reset job status
        $model->status = QueuedJob::STATUS_PENDING;
        $model->attempts += 1;
        $model->error_message = null;
        $model->updated_at = time();
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Job has been queued for retry.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error resetting job: {error}', ['error' => json_encode($model->errors)]));
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Manually processes a job immediately.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionProcess($id)
    {
        $model = $this->findModel($id);
        
        if ($model->status === QueuedJob::STATUS_PROCESSING) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'This job is already being processed.'));
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if ($model->status === QueuedJob::STATUS_COMPLETED) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'This job has already been completed.'));
            return $this->redirect(['view', 'id' => $id]);
        }
        
        // Mark job as processing
        $model->status = QueuedJob::STATUS_PROCESSING;
        $model->started_at = time();
        $model->updated_at = time();
        
        if (!$model->save()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error updating job status: {error}', ['error' => json_encode($model->errors)]));
            return $this->redirect(['view', 'id' => $id]);
        }
        
        try {
            // Process job based on type
            $jobProcessor = new \common\components\JobProcessor();
            $result = $jobProcessor->processJob($model);
            
            if ($result) {
                $model->status = QueuedJob::STATUS_COMPLETED;
                $model->completed_at = time();
                $model->error_message = null;
            } else {
                $model->status = QueuedJob::STATUS_FAILED;
                $model->error_message = 'Job processing failed.';
            }
            
            $model->updated_at = time();
            $model->save();
            
            Yii::$app->session->setFlash('success', Yii::t('app', 'Job processed successfully.'));
        } catch (\Exception $e) {
            $model->status = QueuedJob::STATUS_FAILED;
            $model->error_message = $e->getMessage();
            $model->updated_at = time();
            $model->save();
            
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error processing job: {error}', ['error' => $e->getMessage()]));
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Clear all completed jobs.
     * @return mixed
     */
    public function actionClearCompleted()
    {
        $count = QueuedJob::deleteAll(['status' => QueuedJob::STATUS_COMPLETED]);
        Yii::$app->session->setFlash('success', Yii::t('app', '{count} completed jobs cleared successfully.', ['count' => $count]));
        
        return $this->redirect(['index']);
    }

    /**
     * Clear all failed jobs.
     * @return mixed
     */
    public function actionClearFailed()
    {
        $count = QueuedJob::deleteAll(['status' => QueuedJob::STATUS_FAILED]);
        Yii::$app->session->setFlash('success', Yii::t('app', '{count} failed jobs cleared successfully.', ['count' => $count]));
        
        return $this->redirect(['index']);
    }

    /**
     * Clear all processing jobs.
     * @return mixed
     */
    public function actionClearProcessing()
    {
        $count = QueuedJob::deleteAll(['status' => QueuedJob::STATUS_PROCESSING]);
        Yii::$app->session->setFlash('success', Yii::t('app', '{count} przetwarzanych zadań zostało usuniętych.', ['count' => $count]));
        
        return $this->redirect(['index']);
    }

    /**
     * Run the queue processor to execute pending jobs.
     * @return mixed
     */
    public function actionRunProcessor()
    {
        $limit = (int)Yii::$app->request->get('limit', 5);
        
        // Find pending jobs
        $jobs = QueuedJob::find()
            ->where(['status' => QueuedJob::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit($limit)
            ->all();
        
        $processed = 0;
        $successful = 0;
        $failed = 0;
        
        // Process jobs
        foreach ($jobs as $job) {
            $processed++;
            
            // Mark job as processing
            $job->status = QueuedJob::STATUS_PROCESSING;
            $job->started_at = time();
            $job->updated_at = time();
            $job->save();
            
            try {
                // Process job
                $jobProcessor = new \common\components\JobProcessor();
                $result = $jobProcessor->processJob($job);
                
                if ($result) {
                    $job->status = QueuedJob::STATUS_COMPLETED;
                    $job->completed_at = time();
                    $job->error_message = null;
                    $successful++;
                } else {
                    $job->status = QueuedJob::STATUS_FAILED;
                    $job->error_message = 'Job processing failed.';
                    $failed++;
                }
            } catch (\Exception $e) {
                $job->status = QueuedJob::STATUS_FAILED;
                $job->error_message = $e->getMessage();
                $failed++;
            }
            
            $job->updated_at = time();
            $job->save();
        }
        
        Yii::$app->session->setFlash('success', Yii::t('app', 'Queue processor ran successfully. Processed {processed} jobs: {successful} succeeded, {failed} failed.', [
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed
        ]));
        
        return $this->redirect(['index']);
    }

    /**
     * Creates a new QueuedJob model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QueuedJob();
        $model->status = QueuedJob::STATUS_PENDING;
        
        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = time();
            $model->updated_at = time();
            
            // Sprawdź czy parametry są w formacie JSON
            if (!empty($model->params)) {
                if (!json_decode($model->params)) {
                    $model->addError('params', 'Parametry muszą być w formacie JSON.');
                    
                    // Job type options
                    $jobTypes = [
                        's3_sync' => 'Synchronizacja S3',
                        'regenerate_thumbnails' => 'Regeneracja Miniatur',
                        'analyze_photo' => 'Analiza Zdjęcia',
                        'analyze_batch' => 'Analiza Wsadowa',
                        'import_photos' => 'Import Zdjęć',
                    ];
                    
                    return $this->render('create', [
                        'model' => $model,
                        'jobTypes' => $jobTypes,
                    ]);
                }
            }
            
            if ($model->save()) {
                // Sprawdź czy należy uruchomić zadanie natychmiast
                $runNow = Yii::$app->request->post('run_now', false);
                
                if ($runNow) {
                    try {
                        // Oznacz zadanie jako przetwarzane
                        $model->status = QueuedJob::STATUS_PROCESSING;
                        $model->started_at = time();
                        $model->updated_at = time();
                        $model->save();
                        
                        // Przetwórz zadanie
                        $jobProcessor = new \common\components\JobProcessor();
                        $result = $jobProcessor->processJob($model);
                        
                        if ($result) {
                            $model->status = QueuedJob::STATUS_COMPLETED;
                            $model->completed_at = time();
                            $model->error_message = null;
                            $model->save();
                            
                            Yii::$app->session->setFlash('success', 'Zadanie zostało pomyślnie utworzone i wykonane.');
                        } else {
                            $model->status = QueuedJob::STATUS_FAILED;
                            $model->error_message = 'Zadanie nie zostało wykonane pomyślnie.';
                            $model->save();
                            
                            Yii::$app->session->setFlash('error', 'Zadanie zostało utworzone, ale jego wykonanie nie powiodło się.');
                        }
                    } catch (\Exception $e) {
                        $model->status = QueuedJob::STATUS_FAILED;
                        $model->error_message = $e->getMessage();
                        $model->save();
                        
                        Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas wykonywania zadania: ' . $e->getMessage());
                    }
                } else {
                    Yii::$app->session->setFlash('success', 'Zadanie zostało pomyślnie dodane do kolejki.');
                }
                
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        // Job type options
        $jobTypes = [
            's3_sync' => 'Synchronizacja S3',
            'regenerate_thumbnails' => 'Regeneracja Miniatur',
            'analyze_photo' => 'Analiza Zdjęcia',
            'analyze_batch' => 'Analiza Wsadowa',
            'import_photos' => 'Import Zdjęć',
        ];
        
        return $this->render('create', [
            'model' => $model,
            'jobTypes' => $jobTypes,
        ]);
    }

    /**
     * Finds the QueuedJob model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QueuedJob the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QueuedJob::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested job does not exist.');
    }
}