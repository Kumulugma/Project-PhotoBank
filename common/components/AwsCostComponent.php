<?php

namespace common\components;

use Yii;
use yii\base\Component;
use common\models\AuditLog;

/**
 * AWS Cost Explorer Component
 * Pobiera informacje o kosztach AWS
 */
class AwsCostComponent extends Component {

    public $accessKeyId;
    public $secretAccessKey;
    public $region = 'us-east-1';
    public $cacheDuration = 3600; // 1 godzina cache
    private $client;

    public function init() {
        parent::init();

        if (!$this->accessKeyId || !$this->secretAccessKey) {
            // Pobierz z ustawień
            $this->accessKeyId = \common\models\Settings::getSetting('aws.cost_access_key_id');
            $this->secretAccessKey = \common\models\Settings::getSetting('aws.cost_secret_access_key');
            $this->region = \common\models\Settings::getSetting('aws.cost_region', 'us-east-1');
        }
    }

    /**
     * Inicjalizuje klienta AWS
     */
    private function initClient() {
        if ($this->client === null) {
            if (!$this->accessKeyId || !$this->secretAccessKey) {
                throw new \Exception('AWS Cost Explorer credentials not configured');
            }

            // Użyj SDK AWS lub własną implementację
            $this->client = new \Aws\CostExplorer\CostExplorerClient([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => $this->accessKeyId,
                    'secret' => $this->secretAccessKey,
                ]
            ]);
        }

        return $this->client;
    }

    /**
     * Pobiera koszty za bieżący miesiąc
     * 
     * @return array
     */
    public function getCurrentMonthCosts() {
        $cacheKey = 'aws_current_month_costs_' . date('Y-m');
        $cached = Yii::$app->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $client = $this->initClient();

            $startDate = date('Y-m-01'); // Pierwszy dzień miesiąca
            $endDate = date('Y-m-d'); // Dzisiaj

            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => $startDate,
                    'End' => $endDate,
                ],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['BlendedCost', 'UnblendedCost'],
                'GroupBy' => [
                    [
                        'Type' => 'DIMENSION',
                        'Key' => 'SERVICE'
                    ]
                ]
            ]);

            $costs = $this->parseCurrentCosts($result);

            // Cache na 1 godzinę
            Yii::$app->cache->set($cacheKey, $costs, $this->cacheDuration);

            AuditLog::logSystemEvent('Pobrano aktualne koszty AWS',
                    AuditLog::SEVERITY_INFO, AuditLog::ACTION_SYSTEM);

            return $costs;
        } catch (\Exception $e) {
            AuditLog::logSystemEvent('Błąd pobierania kosztów AWS: ' . $e->getMessage(),
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);

            return [
                'error' => true,
                'message' => 'Nie udało się pobrać kosztów AWS',
                'total' => 0,
                'services' => []
            ];
        }
    }

    /**
     * Pobiera prognozę na podstawie danych historycznych
     * 
     * @return array
     */
    private function getForecastFromHistoricalData()
    {
        $client = $this->initClient();
        
        // Pobierz dane z ostatnich 3 miesięcy
        $endDate = date('Y-m-01'); // Pierwszy dzień tego miesiąca
        $startDate = date('Y-m-01', strtotime('-3 months')); // 3 miesiące temu
        
        $result = $client->getCostAndUsage([
            'TimePeriod' => [
                'Start' => $startDate,
                'End' => $endDate,
            ],
            'Granularity' => 'MONTHLY',
            'Metrics' => ['BlendedCost'],
        ]);
        
        if (!isset($result['ResultsByTime']) || count($result['ResultsByTime']) < 2) {
            throw new \Exception('Insufficient historical data for forecast');
        }
        
        // Oblicz średnią z ostatnich miesięcy
        $monthlyCosts = [];
        foreach ($result['ResultsByTime'] as $timeResult) {
            if (isset($timeResult['Total']['BlendedCost']['Amount'])) {
                $monthlyCosts[] = (float) $timeResult['Total']['BlendedCost']['Amount'];
            }
        }
        
        if (empty($monthlyCosts)) {
            throw new \Exception('No valid cost data found');
        }
        
        // Prosta prognoza na podstawie średniej
        $averageMonthlyCost = array_sum($monthlyCosts) / count($monthlyCosts);
        
        // Dodaj trend (jeśli ostatni miesiąc był droższy/tańszy)
        $trendMultiplier = 1.0;
        if (count($monthlyCosts) >= 2) {
            $lastMonth = end($monthlyCosts);
            $previousMonth = $monthlyCosts[count($monthlyCosts) - 2];
            if ($previousMonth > 0) {
                $trendMultiplier = $lastMonth / $previousMonth;
                // Ogranicz trend do rozsądnych wartości
                $trendMultiplier = max(0.5, min(2.0, $trendMultiplier));
            }
        }
        
        $projectedCost = $averageMonthlyCost * $trendMultiplier;
        
        return [
            'total' => round($projectedCost, 2),
            'currency' => 'USD',
            'confidence' => 'MEDIUM',
            'method' => 'historical_data',
            'includes_current' => true, // Już zawiera pełny miesiąc
            'period' => [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-t')
            ]
        ];
    }
    
    /**
     * Pobiera prognozę kosztów na koniec miesiąca
     * 
     * @return array
     */
    public function getMonthEndForecast()
    {
        $cacheKey = 'aws_month_end_forecast_' . date('Y-m');
        $cached = Yii::$app->cache->get($cacheKey);
        
        if ($cached !== false) {
            return $cached;
        }
        
        try {
            $client = $this->initClient();
            
            $startDate = date('Y-m-d'); // Dzisiaj
            $endDate = date('Y-m-t'); // Ostatni dzień miesiąca
            
            // Jeśli jesteśmy już na końcu miesiąca, zwróć aktualne koszty
            if ($startDate === $endDate) {
                return $this->getCurrentMonthCosts();
            }
            
            // Próbuj różne metody API
            $forecast = null;
            
            // Metoda 1: GetCostAndUsageForecast (jeśli istnieje)
            try {
                $result = $client->getCostAndUsageForecast([
                    'TimePeriod' => [
                        'Start' => $startDate,
                        'End' => $endDate,
                    ],
                    'Metric' => 'BLENDED_COST',
                    'Granularity' => 'MONTHLY'
                ]);
                $forecast = $this->parseForecast($result);
            } catch (\Exception $e1) {
                // Metoda 2: GetUsageForecast z UNBLENDED_COST
                try {
                    $result = $client->getUsageForecast([
                        'TimePeriod' => [
                            'Start' => $startDate,
                            'End' => $endDate,
                        ],
                        'Metric' => 'UNBLENDED_COST',
                        'Granularity' => 'MONTHLY'
                    ]);
                    $forecast = $this->parseForecast($result);
                } catch (\Exception $e2) {
                    // Metoda 3: Użyj GetCostAndUsage dla poprzednich miesięcy i ekstrapoluj
                    try {
                        $forecast = $this->getForecastFromHistoricalData();
                    } catch (\Exception $e3) {
                        // Fallback do prostej kalkulacji
                        throw new \Exception("All forecast methods failed. Last error: " . $e3->getMessage());
                    }
                }
            }
            
            if ($forecast) {
                // Dodaj aktualne koszty do prognozy (jeśli to prognoza tylko na pozostałe dni)
                $currentCosts = $this->getCurrentMonthCosts();
                if (!isset($currentCosts['error']) && !isset($forecast['includes_current'])) {
                    $forecast['total'] += $currentCosts['total'];
                }
                
                // Cache na 1 godzinę
                Yii::$app->cache->set($cacheKey, $forecast, $this->cacheDuration);
                
                AuditLog::logSystemEvent('Pobrano prognozę kosztów AWS', 
                    AuditLog::SEVERITY_INFO, AuditLog::ACTION_SYSTEM);
                
                return $forecast;
            }
            
        } catch (\Exception $e) {
            // Fallback do prostej kalkulacji
            return $this->getSimpleForecast($e);
        }
        
        return $this->getSimpleForecast();
    }
    
    /**
     * Prosta prognoza na podstawie obecnych kosztów
     * 
     * @param \Exception $originalError
     * @return array
     */
    private function getSimpleForecast($originalError = null)
    {
        try {
            $currentCosts = $this->getCurrentMonthCosts();
            
            if (isset($currentCosts['error'])) {
                return [
                    'error' => true,
                    'message' => 'Nie udało się pobrać prognozy kosztów AWS',
                    'total' => 0,
                    'confidence' => 'LOW'
                ];
            }
            
            // Prosta kalkulacja: obecne koszty / dzień miesiąca * dni w miesiącu
            $currentDay = (int) date('j');
            $daysInMonth = (int) date('t');
            
            if ($currentDay === 0) {
                $currentDay = 1; // Zabezpieczenie
            }
            
            $dailyAverage = $currentCosts['total'] / $currentDay;
            $projectedTotal = $dailyAverage * $daysInMonth;
            
            // Dodaj trochę bufora na wzrost kosztów pod koniec miesiąca
            $projectedTotal *= 1.05; // 5% buffer
            
            $errorMsg = $originalError ? $originalError->getMessage() : 'API forecast niedostępne';
            AuditLog::logSystemEvent('Użyto prostej prognozy kosztów AWS (fallback): ' . $errorMsg, 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_SYSTEM);
            
            return [
                'total' => round($projectedTotal, 2),
                'currency' => 'USD',
                'confidence' => 'LOW',
                'method' => 'simple_calculation',
                'includes_current' => true, // Już zawiera obecne koszty
                'period' => [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-t')
                ]
            ];
            
        } catch (\Exception $e) {
            AuditLog::logSystemEvent('Błąd prostej prognozy kosztów AWS: ' . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_SYSTEM);
            
            return [
                'error' => true,
                'message' => 'Nie udało się pobrać prognozy kosztów AWS',
                'total' => 0,
                'confidence' => 'LOW'
            ];
        }
    }

    /**
     * Pobiera koszty za poprzedni miesiąc
     * 
     * @return array
     */
    public function getLastMonthCosts() {
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $cacheKey = 'aws_last_month_costs_' . $lastMonth;
        $cached = Yii::$app->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $client = $this->initClient();

            $startDate = date('Y-m-01', strtotime('-1 month'));
            $endDate = date('Y-m-t', strtotime('-1 month'));

            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => $startDate,
                    'End' => $endDate,
                ],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['BlendedCost'],
            ]);

            $costs = $this->parseCurrentCosts($result);

            // Cache na 24 godziny (dane historyczne się nie zmieniają)
            Yii::$app->cache->set($cacheKey, $costs, 86400);

            return $costs;
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Nie udało się pobrać kosztów z poprzedniego miesiąca',
                'total' => 0
            ];
        }
    }

    /**
     * Pobiera szczegółowe koszty S3
     * 
     * @return array
     */
    public function getS3Costs() {
        $cacheKey = 'aws_s3_costs_' . date('Y-m');
        $cached = Yii::$app->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $client = $this->initClient();

            $startDate = date('Y-m-01');
            $endDate = date('Y-m-d');

            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => $startDate,
                    'End' => $endDate,
                ],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['BlendedCost', 'UsageQuantity'],
                'GroupBy' => [
                    [
                        'Type' => 'DIMENSION',
                        'Key' => 'USAGE_TYPE'
                    ]
                ],
                'Filter' => [
                    'Dimensions' => [
                        'Key' => 'SERVICE',
                        'Values' => ['Amazon Simple Storage Service']
                    ]
                ]
            ]);

            $s3Costs = $this->parseS3Costs($result);

            Yii::$app->cache->set($cacheKey, $s3Costs, $this->cacheDuration);

            return $s3Costs;
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Nie udało się pobrać kosztów S3',
                'storage' => 0,
                'requests' => 0,
                'transfer' => 0
            ];
        }
    }

    /**
     * Przetwarza odpowiedź z current costs
     */
    private function parseCurrentCosts($result) {
        $total = 0;
        $services = [];

        if (isset($result['ResultsByTime'][0]['Groups'])) {
            foreach ($result['ResultsByTime'][0]['Groups'] as $group) {
                $serviceName = $group['Keys'][0];
                $amount = (float) $group['Metrics']['BlendedCost']['Amount'];

                $services[$serviceName] = [
                    'amount' => $amount,
                    'currency' => $group['Metrics']['BlendedCost']['Unit']
                ];

                $total += $amount;
            }
        } elseif (isset($result['ResultsByTime'][0]['Total'])) {
            $total = (float) $result['ResultsByTime'][0]['Total']['BlendedCost']['Amount'];
        }

        return [
            'total' => round($total, 2),
            'currency' => 'USD',
            'services' => $services,
            'period' => [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d')
            ]
        ];
    }

    /**
     * Przetwarza odpowiedź z forecast
     */
    private function parseForecast($result) {
        $total = 0;
        $confidence = 'MEDIUM';

        // Sprawdź różne możliwe struktury odpowiedzi
        if (isset($result['ForecastResultsByTime'][0]['MeanValue'])) {
            $total = (float) $result['ForecastResultsByTime'][0]['MeanValue'];
        } elseif (isset($result['Total']['Amount'])) {
            $total = (float) $result['Total']['Amount'];
        } elseif (isset($result['ResultsByTime'][0]['Total']['BlendedCost']['Amount'])) {
            $total = (float) $result['ResultsByTime'][0]['Total']['BlendedCost']['Amount'];
        }

        // Sprawdź confidence
        if (isset($result['ForecastResultsByTime'][0]['PredictionIntervalLowerBound'])) {
            $lowerBound = (float) $result['ForecastResultsByTime'][0]['PredictionIntervalLowerBound'];
            $upperBound = (float) $result['ForecastResultsByTime'][0]['PredictionIntervalUpperBound'];

            // Oceń pewność prognozy na podstawie zakresu
            $range = $upperBound - $lowerBound;
            $confidence = $range <= ($total * 0.1) ? 'HIGH' : ($range <= ($total * 0.25) ? 'MEDIUM' : 'LOW');
        }

        return [
            'total' => round($total, 2),
            'currency' => 'USD',
            'confidence' => $confidence,
            'period' => [
                'start' => date('Y-m-d'),
                'end' => date('Y-m-t')
            ]
        ];
    }

    /**
     * Przetwarza koszty S3
     */
    private function parseS3Costs($result) {
        $storage = 0;
        $requests = 0;
        $transfer = 0;

        if (isset($result['ResultsByTime'][0]['Groups'])) {
            foreach ($result['ResultsByTime'][0]['Groups'] as $group) {
                $usageType = $group['Keys'][0];
                $amount = (float) $group['Metrics']['BlendedCost']['Amount'];

                if (strpos($usageType, 'StorageClass') !== false) {
                    $storage += $amount;
                } elseif (strpos($usageType, 'Requests') !== false) {
                    $requests += $amount;
                } elseif (strpos($usageType, 'DataTransfer') !== false) {
                    $transfer += $amount;
                }
            }
        }

        return [
            'storage' => round($storage, 2),
            'requests' => round($requests, 2),
            'transfer' => round($transfer, 2),
            'total' => round($storage + $requests + $transfer, 2),
            'currency' => 'USD'
        ];
    }

    /**
     * Test połączenia z AWS Cost Explorer
     * 
     * @return bool|string
     */
    public function testConnection() {
        try {
            $client = $this->initClient();

            // Prosty test - pobierz koszty za ostatni miesiąc
            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => date('Y-m-01', strtotime('-1 month')),
                    'End' => date('Y-m-t', strtotime('-1 month')),
                ],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['BlendedCost'],
            ]);

            return true;
        } catch (\Exception $e) {
            return 'Błąd połączenia z AWS Cost Explorer: ' . $e->getMessage();
        }
    }

}
