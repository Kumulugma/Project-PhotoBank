<?php
// backend/views/audit-log/index.php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\search\AuditLogSearch;
use common\models\AuditLog;

\backend\assets\AppAsset::registerControllerCss($this, 'audit-log');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
\backend\assets\AppAsset::registerComponentCss($this, 'modals');

$this->title = 'Dziennik Zdarzeń';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="audit-log-index">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Dashboard', ['dashboard'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('Eksport', '#', [
                'class' => 'btn btn-success', 
                'data-bs-toggle' => 'modal', 
                'data-bs-target' => '#exportModal'
            ]) ?>
            <?= Html::a('Czyszczenie', '#', [
                'class' => 'btn btn-warning', 
                'data-bs-toggle' => 'modal', 
                'data-bs-target' => '#cleanupModal'
            ]) ?>
        </div>
    </div>

    <!-- Statystyki -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($stats['total']) ?></h4>
                            <p class="mb-0">Wszystkich wpisów</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($stats['today']) ?></h4>
                            <p class="mb-0">Dzisiaj</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($stats['week']) ?></h4>
                            <p class="mb-0">Ten tydzień</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($stats['month']) ?></h4>
                            <p class="mb-0">Ten miesiąc</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Najczęstsze akcje -->
    <?php if (!empty($topActions)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Najczęstsze akcje (ostatnie 30 dni)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($topActions as $action): ?>
                        <div class="col-md-2 text-center">
                            <h4 class="text-primary"><?= number_format($action['count']) ?></h4>
                            <small><?= AuditLogSearch::getActionOptions()[$action['action']] ?? $action['action'] ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n{items}\n{pager}",
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Data',
                'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'headerOptions' => ['style' => 'width: 140px'],
                'filter' => false,
            ],
            [
                'attribute' => 'action',
                'label' => 'Akcja',
                'value' => function ($model) {
                    return $model->getActionLabel();
                },
                'filter' => Html::activeDropDownList($searchModel, 'action', 
                    ['' => 'Wszystkie'] + AuditLogSearch::getActionOptions(), 
                    ['class' => 'form-control form-control-sm']
                ),
                'headerOptions' => ['style' => 'width: 120px'],
            ],
            [
                'attribute' => 'username',
                'label' => 'Użytkownik',
                'value' => function ($model) {
                    return $model->user ? $model->user->username : '-';
                },
                'filter' => Html::activeTextInput($searchModel, 'username', 
                    ['class' => 'form-control form-control-sm', 'placeholder' => 'Nazwa użytkownika']
                ),
                'headerOptions' => ['style' => 'width: 120px'],
            ],
            [
                'attribute' => 'severity',
                'label' => 'Poziom',
                'value' => function ($model) {
                    $class = 'badge bg-' . $model->getSeverityClass();
                    return Html::tag('span', $model->getSeverityLabel(), ['class' => $class]);
                },
                'format' => 'raw',
                'filter' => Html::activeDropDownList($searchModel, 'severity', 
                    ['' => 'Wszystkie'] + AuditLogSearch::getSeverityOptions(), 
                    ['class' => 'form-control form-control-sm']
                ),
                'headerOptions' => ['style' => 'width: 100px'],
            ],
            [
                'attribute' => 'message',
                'label' => 'Wiadomość',
                'value' => function ($model) {
                    return $model->message ? 
                        (mb_strlen($model->message) > 80 ? 
                            mb_substr($model->message, 0, 80) . '...' : 
                            $model->message) : 
                        '-';
                },
                'filter' => Html::activeTextInput($searchModel, 'message', 
                    ['class' => 'form-control form-control-sm', 'placeholder' => 'Szukaj w wiadomości']
                ),
            ],
            [
                'attribute' => 'user_ip',
                'label' => 'IP',
                'headerOptions' => ['style' => 'width: 120px'],
                'filter' => Html::activeTextInput($searchModel, 'user_ip', 
                    ['class' => 'form-control form-control-sm', 'placeholder' => 'Adres IP']
                ),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['style' => 'width: 50px'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'Podgląd',
                            'class' => 'btn btn-sm btn-outline-primary',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<!-- Modal czyszczenia -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['cleanup'], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Czyszczenie dziennika zdarzeń</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Usuń wpisy starsze niż:</p>
                <select name="days" class="form-control">
                    <option value="30">30 dni</option>
                    <option value="60">60 dni</option>
                    <option value="90" selected>90 dni</option>
                    <option value="180">180 dni</option>
                    <option value="365">1 rok</option>
                </select>
                <div class="alert alert-warning mt-3">
                    <strong>Uwaga:</strong> Ta operacja jest nieodwracalna!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-warning">Wyczyść</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- Modal eksportu -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= Html::beginForm(['export'], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Eksport dziennika zdarzeń</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Format eksportu:</label>
                    <select name="format" class="form-control">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Data od:</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data do:</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-success">Eksportuj</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>