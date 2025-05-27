<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use common\models\AuditLog;
use common\models\Settings;

/**
 * AWS Cost Controller
 */
class AwsCostController extends Controller
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
        ];
    }

    /**
     * AWS Cost dashboard
     */
    public function actionIndex()
    {
        AuditLog::logSystemEvent('Przeglądanie szczegółów kosztów AWS', 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);

        $awsCosts = null;
        $error = null;

        if (Yii::$app->has('awsCost')) {
            try {
                $currentCosts = Yii::$app->awsCost->getCurrentMonthCosts();
                $forecast = Yii::$app->awsCost->getMonthEndForecast();
                $lastMonth = Yii::$app->awsCost->getLastMonthCosts();
                $s3Costs = Yii::$app->awsCost->getS3Costs();

                $awsCosts = [
                    'current' => $currentCosts,
                    'forecast' => $forecast,
                    'lastMonth' => $lastMonth,
                    's3' => $s3Costs
                ];
            } catch (\Exception $e) {
                $error = $e->getMessage();
                AuditLog::logSystemEvent('Błąd pobierania kosztów AWS: ' . $e->getMessage(), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            }
        } else {
            $error = 'Komponent AWS Cost nie jest skonfigurowany';
        }

        // Pobierz ustawienia AWS
        $settings = [
            'enabled' => Settings::getSetting('aws.cost_enabled', '0'),
            'region' => Settings::getSetting('aws.cost_region', 'us-east-1'),
            'cache_duration' => Settings::getSetting('aws.cost_cache_duration', '3600'),
            'monthly_budget' => Settings::getSetting('aws.cost_monthly_budget', '100'),
            'alert_threshold' => Settings::getSetting('aws.cost_alert_threshold', '80'),
        ];

        return $this->render('index', [
            'awsCosts' => $awsCosts,
            'error' => $error,
            'settings' => $settings,
        ]);
    }

    /**
     * Test AWS connection
     */
    public function actionTestConnection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->has('awsCost')) {
            return [
                'success' => false,
                'message' => 'Komponent AWS Cost nie jest skonfigurowany'
            ];
        }

        try {
            $result = Yii::$app->awsCost->testConnection();
            
            if ($result === true) {
                AuditLog::logSystemEvent('Test połączenia AWS Cost Explorer zakończony sukcesem', 
                    AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_SYSTEM);
                
                return [
                    'success' => true,
                    'message' => 'Połączenie z AWS Cost Explorer działa poprawnie'
                ];
            } else {
                AuditLog::logSystemEvent('Test połączenia AWS Cost Explorer nieudany: ' . $result, 
                    AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
                
                return [
                    'success' => false,
                    'message' => $result
                ];
            }
        } catch (\Exception $e) {
            AuditLog::logSystemEvent('Błąd testu połączenia AWS: ' . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            
            return [
                'success' => false,
                'message' => 'Błąd połączenia: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clear AWS costs cache
     */
    public function actionClearCache()
    {
        $cleared = 0;
        $cacheKeys = [
            'aws_current_month_costs_' . date('Y-m'),
            'aws_month_end_forecast_' . date('Y-m'),
            'aws_last_month_costs_' . date('Y-m', strtotime('-1 month')),
            'aws_s3_costs_' . date('Y-m'),
        ];

        foreach ($cacheKeys as $key) {
            if (Yii::$app->cache->delete($key)) {
                $cleared++;
            }
        }

        AuditLog::logSystemEvent("Wyczyszczono cache kosztów AWS - {$cleared} kluczy", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_SYSTEM);

        Yii::$app->session->setFlash('success', "Wyczyszczono {$cleared} kluczy cache");
        return $this->redirect(['index']);
    }

    /**
     * Get costs data as JSON
     */
    public function actionGetCostsData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->has('awsCost')) {
            return ['error' => 'AWS Cost component not configured'];
        }

        try {
            $period = Yii::$app->request->get('period', 'current');
            
            switch ($period) {
                case 'current':
                    return Yii::$app->awsCost->getCurrentMonthCosts();
                case 'forecast':
                    return Yii::$app->awsCost->getMonthEndForecast();
                case 'last':
                    return Yii::$app->awsCost->getLastMonthCosts();
                case 's3':
                    return Yii::$app->awsCost->getS3Costs();
                default:
                    return ['error' => 'Invalid period'];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Export costs data
     */
    public function actionExport()
    {
        $format = Yii::$app->request->get('format', 'json');
        $period = Yii::$app->request->get('period', 'current');

        AuditLog::logSystemEvent("Eksport danych kosztów AWS - format: {$format}, okres: {$period}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_EXPORT);

        if (!Yii::$app->has('awsCost')) {
            throw new \yii\web\ServerErrorHttpException('AWS Cost component not configured');
        }

        try {
            $data = [];
            
            switch ($period) {
                case 'all':
                    $data = [
                        'current' => Yii::$app->awsCost->getCurrentMonthCosts(),
                        'forecast' => Yii::$app->awsCost->getMonthEndForecast(),
                        'last' => Yii::$app->awsCost->getLastMonthCosts(),
                        's3' => Yii::$app->awsCost->getS3Costs(),
                    ];
                    break;
                case 'current':
                    $data = Yii::$app->awsCost->getCurrentMonthCosts();
                    break;
                case 'forecast':
                    $data = Yii::$app->awsCost->getMonthEndForecast();
                    break;
                case 'last':
                    $data = Yii::$app->awsCost->getLastMonthCosts();
                    break;
                case 's3':
                    $data = Yii::$app->awsCost->getS3Costs();
                    break;
            }

            $filename = 'aws_costs_' . $period . '_' . date('Y-m-d_H-i-s');

            if ($format === 'json') {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->setDownloadHeaders($filename . '.json', 'application/json');
                return $data;
            } else {
                // CSV format
                Yii::$app->response->format = Response::FORMAT_RAW;
                Yii::$app->response->setDownloadHeaders($filename . '.csv', 'text/csv');

                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

                if ($period === 'all') {
                    fputcsv($output, ['Okres', 'Typ', 'Wartość', 'Waluta'], ';');
                    foreach ($data as $periodName => $periodData) {
                        if (!isset($periodData['error'])) {
                            fputcsv($output, [$periodName, 'total', $periodData['total'], $periodData['currency']], ';');
                        }
                    }
                } else {
                    if (isset($data['services'])) {
                        fputcsv($output, ['Usługa', 'Koszt', 'Waluta'], ';');
                        foreach ($data['services'] as $service => $serviceData) {
                            fputcsv($output, [$service, $serviceData['amount'], $serviceData['currency']], ';');
                        }
                    } else {
                        fputcsv($output, ['Typ', 'Wartość', 'Waluta'], ';');
                        fputcsv($output, [$period, $data['total'], $data['currency']], ';');
                    }
                }

                fclose($output);
                return Yii::$app->response;
            }
        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException('Export error: ' . $e->getMessage());
        }
    }
}