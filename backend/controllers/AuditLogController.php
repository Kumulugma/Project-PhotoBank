<?php

namespace backend\controllers;

use Yii;
use common\models\AuditLog;
use common\models\search\AuditLogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AuditLogController handles audit log viewing and management
 */
class AuditLogController extends Controller
{
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
                    'cleanup' => ['POST'],
                    'export' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all audit log entries
     * @return mixed
     */
    public function actionIndex()
    {
        AuditLog::logSystemEvent('Przeglądanie dziennika zdarzeń', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);
        
        $searchModel = new AuditLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Statystyki
        $stats = [
            'total' => AuditLog::find()->count(),
            'today' => AuditLog::find()->where(['>=', 'created_at', strtotime('today')])->count(),
            'week' => AuditLog::find()->where(['>=', 'created_at', strtotime('-7 days')])->count(),
            'month' => AuditLog::find()->where(['>=', 'created_at', strtotime('-30 days')])->count(),
        ];

        // Najczęstsze akcje
        $topActions = AuditLog::find()
            ->select(['action', 'COUNT(*) as count'])
            ->where(['>=', 'created_at', strtotime('-30 days')])
            ->groupBy('action')
            ->orderBy(['count' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'topActions' => $topActions,
        ]);
    }

    /**
     * Displays a single audit log entry
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        AuditLog::logSystemEvent("Podgląd wpisu dziennika ID: {$id}", AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Dashboard with audit log statistics
     * @return mixed
     */
    public function actionDashboard()
    {
        AuditLog::logSystemEvent('Przeglądanie dashboardu dziennika zdarzeń', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        // Statystyki ogólne
        $stats = [
            'total' => AuditLog::find()->count(),
            'today' => AuditLog::find()->where(['>=', 'created_at', strtotime('today')])->count(),
            'yesterday' => AuditLog::find()
                ->where(['>=', 'created_at', strtotime('yesterday')])
                ->andWhere(['<', 'created_at', strtotime('today')])
                ->count(),
            'week' => AuditLog::find()->where(['>=', 'created_at', strtotime('-7 days')])->count(),
            'month' => AuditLog::find()->where(['>=', 'created_at', strtotime('-30 days')])->count(),
        ];

        // Statystyki błędów
        $errorStats = [
            'errors_today' => AuditLog::find()
                ->where(['severity' => AuditLog::SEVERITY_ERROR])
                ->andWhere(['>=', 'created_at', strtotime('today')])
                ->count(),
            'warnings_today' => AuditLog::find()
                ->where(['severity' => AuditLog::SEVERITY_WARNING])
                ->andWhere(['>=', 'created_at', strtotime('today')])
                ->count(),
        ];

        // Najaktywniejsze akcje (ostatnie 30 dni)
        $topActions = AuditLog::find()
            ->select(['action', 'COUNT(*) as count'])
            ->where(['>=', 'created_at', strtotime('-30 days')])
            ->groupBy('action')
            ->orderBy(['count' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Najaktywniejsze użytkownicy (ostatnie 30 dni)
        $topUsers = AuditLog::find()
            ->select(['user_id', 'COUNT(*) as count'])
            ->joinWith('user')
            ->where(['>=', 'audit_log.created_at', strtotime('-30 days')])
            ->andWhere(['is not', 'user_id', null])
            ->groupBy('user_id')
            ->orderBy(['count' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Aktywność w czasie (ostatnie 7 dni)
        $dailyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayStart = strtotime($date . ' 00:00:00');
            $dayEnd = strtotime($date . ' 23:59:59');
            
            $count = AuditLog::find()
                ->where(['>=', 'created_at', $dayStart])
                ->andWhere(['<=', 'created_at', $dayEnd])
                ->count();
            
            $dailyActivity[] = [
                'date' => $date,
                'count' => $count,
                'formatted_date' => date('d.m', strtotime($date))
            ];
        }

        // Ostatnie błędy
        $recentErrors = AuditLog::find()
            ->where(['severity' => [AuditLog::SEVERITY_ERROR, AuditLog::SEVERITY_WARNING]])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('dashboard', [
            'stats' => $stats,
            'errorStats' => $errorStats,
            'topActions' => $topActions,
            'topUsers' => $topUsers,
            'dailyActivity' => $dailyActivity,
            'recentErrors' => $recentErrors,
        ]);
    }

    /**
     * Cleanup old audit log entries
     * @return mixed
     */
    public function actionCleanup()
    {
        $days = (int)Yii::$app->request->post('days', 90);
        
        if ($days < 30) {
            Yii::$app->session->setFlash('error', 'Nie można usunąć wpisów młodszych niż 30 dni.');
            return $this->redirect(['index']);
        }

        try {
            $deleted = AuditLog::cleanup($days);
            
            AuditLog::logSystemEvent("Wyczyszczono dziennik zdarzeń - usunięto {$deleted} wpisów starszych niż {$days} dni", 
                AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SYSTEM);
            
            Yii::$app->session->setFlash('success', "Pomyślnie usunięto {$deleted} starych wpisów dziennika.");
        } catch (\Exception $e) {
            AuditLog::logSystemEvent("Błąd czyszczenia dziennika zdarzeń: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas czyszczenia dziennika: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Export audit log entries
     * @return mixed
     */
    public function actionExport()
    {
        $format = Yii::$app->request->post('format', 'csv');
        $dateFrom = Yii::$app->request->post('date_from');
        $dateTo = Yii::$app->request->post('date_to');
        $quickRange = Yii::$app->request->post('quick_range');
        
        $query = AuditLog::find()->joinWith('user')->orderBy(['created_at' => SORT_DESC]);
        
        // Obsługa szybkich zakresów
        if ($quickRange) {
            switch ($quickRange) {
                case 'today':
                    $query->andFilterWhere(['>=', 'audit_log.created_at', strtotime('today')]);
                    break;
                case 'week':
                    $query->andFilterWhere(['>=', 'audit_log.created_at', strtotime('-7 days')]);
                    break;
                case 'month':
                    $query->andFilterWhere(['>=', 'audit_log.created_at', strtotime('-30 days')]);
                    break;
            }
        } else {
            // Niestandardowy zakres dat
            if ($dateFrom) {
                $query->andFilterWhere(['>=', 'audit_log.created_at', strtotime($dateFrom . ' 00:00:00')]);
            }
            
            if ($dateTo) {
                $query->andFilterWhere(['<=', 'audit_log.created_at', strtotime($dateTo . ' 23:59:59')]);
            }
        }
        
        $logs = $query->all();
        
        AuditLog::logSystemEvent("Eksport dziennika zdarzeń - format: {$format}, wpisów: " . count($logs), 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_EXPORT);

        if ($format === 'json') {
            return $this->exportJson($logs);
        } else {
            return $this->exportCsv($logs);
        }
    }

    /**
     * Export logs as CSV
     */
    private function exportCsv($logs)
    {
        $filename = 'audit_log_' . date('Y-m-d_H-i-s') . '.csv';
        
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        
        $output = fopen('php://output', 'w');
        
        // Nagłówki CSV z BOM dla poprawnego kodowania UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Nagłówki kolumn
        fputcsv($output, [
            'ID', 'Data', 'Akcja', 'Użytkownik', 'Model', 'ID Obiektu', 
            'Adres IP', 'Poziom', 'Wiadomość'
        ], ';');
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                date('Y-m-d H:i:s', $log->created_at),
                $log->getActionLabel(),
                $log->user ? $log->user->username : '-',
                $log->model_class ? basename(str_replace('\\', '/', $log->model_class)) : '-',
                $log->model_id ?: '-',
                $log->user_ip ?: '-',
                $log->getSeverityLabel(),
                $log->message ?: '-'
            ], ';');
        }
        
        fclose($output);
        return Yii::$app->response;
    }

    /**
     * Export logs as JSON
     */
    private function exportJson($logs)
    {
        $filename = 'audit_log_' . date('Y-m-d_H-i-s') . '.json';
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->setDownloadHeaders($filename, 'application/json');
        
        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                'id' => $log->id,
                'created_at' => date('Y-m-d H:i:s', $log->created_at),
                'action' => $log->action,
                'action_label' => $log->getActionLabel(),
                'user_id' => $log->user_id,
                'username' => $log->user ? $log->user->username : null,
                'model_class' => $log->model_class,
                'model_id' => $log->model_id,
                'user_ip' => $log->user_ip,
                'user_agent' => $log->user_agent,
                'severity' => $log->severity,
                'severity_label' => $log->getSeverityLabel(),
                'message' => $log->message,
                'old_values' => $log->getOldValuesArray(),
                'new_values' => $log->getNewValuesArray(),
            ];
        }
        
        return $data;
    }

    /**
     * Finds the AuditLog model based on its primary key value
     * @param integer $id
     * @return AuditLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuditLog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Żądany wpis dziennika nie istnieje.');
    }
}