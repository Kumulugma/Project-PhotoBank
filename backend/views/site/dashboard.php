<?php

use yii\helpers\Html;
use yii\helpers\Url;
use dosamigos\chartjs\ChartJs;

/* @var $this yii\web\View */
/* @var $totalPhotos int */
/* @var $queuedPhotos int */
/* @var $totalCategories int */
/* @var $totalTags int */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-picture" style="font-size: 5em;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 2.5em;"><?= $totalPhotos ?></div>
                            <div>Active Photos</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['photos/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-chevron-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-yellow">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-time" style="font-size: 5em;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 2.5em;"><?= $queuedPhotos ?></div>
                            <div>Photos in Queue</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['photos/queue']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-chevron-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-folder-open" style="font-size: 5em;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 2.5em;"><?= $totalCategories ?></div>
                            <div>Categories</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['categories/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-chevron-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-tags" style="font-size: 5em;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 2.5em;"><?= $totalTags ?></div>
                            <div>Tags</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['tags/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-chevron-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Quick Actions</h3>
                </div>
                <div class="panel-body">
                    <div class="list-group">
                        <a href="<?= Url::to(['photos/upload']) ?>" class="list-group-item">
                            <i class="glyphicon glyphicon-upload"></i> Upload New Photos
                        </a>
                        <a href="<?= Url::to(['photos/queue']) ?>" class="list-group-item">
                            <i class="glyphicon glyphicon-time"></i> Process Photo Queue
                            <?php if ($queuedPhotos > 0): ?>
                                <span class="badge"><?= $queuedPhotos ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= Url::to(['s3/index']) ?>" class="list-group-item">
                            <i class="glyphicon glyphicon-cloud-upload"></i> Synchronize with S3
                        </a>
                        <a href="<?= Url::to(['thumbnails/index']) ?>" class="list-group-item">
                            <i class="glyphicon glyphicon-refresh"></i> Regenerate Thumbnails
                        </a>
                        <a href="<?= Url::to(['categories/create']) ?>" class="list-group-item">
                            <i class="glyphicon glyphicon-plus"></i> Add New Category
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">System Status</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tr>
                            <th>PHP Version</th>
                            <td><?= PHP_VERSION ?></td>
                        </tr>
                        <tr>
                            <th>Yii Version</th>
                            <td><?= Yii::getVersion() ?></td>
                        </tr>
                        <tr>
                            <th>Server</th>
                            <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                        </tr>
                        <tr>
                            <th>Memory Limit</th>
                            <td><?= ini_get('memory_limit') ?></td>
                        </tr>
                        <tr>
                            <th>Upload Max Size</th>
                            <td><?= ini_get('upload_max_filesize') ?></td>
                        </tr>
                        <tr>
                            <th>Post Max Size</th>
                            <td><?= ini_get('post_max_size') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Get monthly photo statistics
    $months = [];
    $photoStats = [];
    
    $command = Yii::$app->db->createCommand('
        SELECT FROM_UNIXTIME(created_at, "%Y-%m") as month, COUNT(*) as count
        FROM photo
        WHERE status = :status
        GROUP BY FROM_UNIXTIME(created_at, "%Y-%m")
        ORDER BY month DESC
        LIMIT 12
    ')->bindValue(':status', \common\models\Photo::STATUS_ACTIVE);
    
    $result = $command->queryAll();
    
    // Reverse to show chronological order
    $result = array_reverse($result);
    
    foreach ($result as $row) {
        $months[] = date('M Y', strtotime($row['month'] . '-01'));
        $photoStats[] = (int)$row['count'];
    }
    ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Photo Upload Statistics</h3>
                </div>
                <div class="panel-body">
                    <?= ChartJs::widget([
                        'type' => 'line',
                        'options' => [
                            'height' => 300,
                        ],
                        'data' => [
                            'labels' => $months,
                            'datasets' => [
                                [
                                    'label' => 'Photos Uploaded',
                                    'backgroundColor' => "rgba(151,187,205,0.2)",
                                    'borderColor' => "rgba(151,187,205,1)",
                                    'pointBackgroundColor' => "rgba(151,187,205,1)",
                                    'pointBorderColor' => "#fff",
                                    'data' => $photoStats,
                                ],
                            ],
                        ],
                        'clientOptions' => [
                            'scales' => [
                                'yAxes' => [
                                    [
                                        'ticks' => [
                                            'beginAtZero' => true,
                                            'stepSize' => 1,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>