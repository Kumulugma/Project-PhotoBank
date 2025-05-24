<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\AuditLog;

\backend\assets\AppAsset::registerControllerCss($this, 'audit-log');

$this->title = 'Szczegóły wpisu dziennika #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Dziennik Zdarzeń', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="audit-log-view">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Powrót do listy', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('Dashboard', ['dashboard'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('Usuń wpis', '#', [
                'class' => 'btn btn-danger',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#deleteModal'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informacje podstawowe</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data i czas',
                                'format' => ['datetime', 'php:d.m.Y H:i:s'],
                            ],
                            [
                                'attribute' => 'action',
                                'label' => 'Akcja',
                                'value' => $model->getActionLabel(),
                            ],
                            [
                                'attribute' => 'severity',
                                'label' => 'Poziom ważności',
                                'format' => 'raw',
                                'value' => Html::tag('span', $model->getSeverityLabel(), [
                                    'class' => 'badge bg-' . $model->getSeverityClass()
                                ]),
                            ],
                            [
                                'attribute' => 'user_id',
                                'label' => 'Użytkownik',
                                'value' => $model->user ? $model->user->username : 'System',
                            ],
                            'user_ip:text:Adres IP',
                            'user_agent:ntext:User Agent',
                        ],
                    ]) ?>
                </div>
            </div>

            <?php if ($model->message): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Wiadomość</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light">
                        <?= Html::encode($model->message) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($model->model_class || $model->model_id): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Obiekt</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'model_class',
                                'label' => 'Klasa modelu',
                                'value' => $model->model_class ? basename(str_replace('\\', '/', $model->model_class)) : '-',
                            ],
                            'model_id:text:ID obiektu',
                        ],
                    ]) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Podsumowanie</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-<?= $model->getSeverityClass() ?> fs-6">
                            <?= $model->getSeverityLabel() ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Czas:</strong><br>
                        <span class="text-muted">
                            <?= date('d.m.Y H:i:s', $model->created_at) ?>
                        </span>
                    </div>

                    <?php if ($model->user): ?>
                    <div class="mb-3">
                        <strong>Wykonawca:</strong><br>
                        <span class="text-primary">
                            <?= Html::encode($model->user->username) ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ($model->user_ip): ?>
                    <div class="mb-3">
                        <strong>Adres IP:</strong><br>
                        <code><?= Html::encode($model->user_ip) ?></code>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Akcja:</strong><br>
                        <span class="badge bg-info">
                            <?= $model->getActionLabel() ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php 
            $oldValues = $model->getOldValuesArray();
            $newValues = $model->getNewValuesArray();
            if (!empty($oldValues) || !empty($newValues)): 
            ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Zmiany danych</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($oldValues)): ?>
                    <div class="mb-3">
                        <h6 class="text-danger">Wartości poprzednie:</h6>
                        <div class="bg-light p-2 rounded">
                            <pre class="mb-0"><?= Html::encode(json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($newValues)): ?>
                    <div class="mb-3">
                        <h6 class="text-success">Wartości nowe:</h6>
                        <div class="bg-light p-2 rounded">
                            <pre class="mb-0"><?= Html::encode(json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Akcje</h5>
                </div>
                <div class="card-body">
                    <?= Html::a('<i class="fas fa-list me-2"></i>Wszystkie wpisy', ['index'], 
                        ['class' => 'btn btn-outline-primary btn-sm d-block mb-2']) ?>
                    
                    <?php if ($model->user): ?>
                    <?= Html::a('<i class="fas fa-user me-2"></i>Wpisy użytkownika', 
                        ['index', 'AuditLogSearch[username]' => $model->user->username], 
                        ['class' => 'btn btn-outline-info btn-sm d-block mb-2']) ?>
                    <?php endif; ?>

                    <?= Html::a('<i class="fas fa-filter me-2"></i>Podobne akcje', 
                        ['index', 'AuditLogSearch[action]' => $model->action], 
                        ['class' => 'btn btn-outline-secondary btn-sm d-block mb-2']) ?>

                    <?php if ($model->user_ip): ?>
                    <?= Html::a('<i class="fas fa-globe me-2"></i>Z tego IP', 
                        ['index', 'AuditLogSearch[user_ip]' => $model->user_ip], 
                        ['class' => 'btn btn-outline-warning btn-sm d-block mb-2']) ?>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń wpis', '#', [
                        'class' => 'btn btn-danger btn-sm d-block',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#deleteModal'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Potwierdzenie usunięcia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Czy na pewno chcesz usunąć ten wpis dziennika?</p>
                <div class="alert alert-info">
                    <strong>Wpis:</strong> <?= Html::encode($model->getActionLabel()) ?><br>
                    <strong>Data:</strong> <?= date('d.m.Y H:i:s', $model->created_at) ?><br>
                    <?php if ($model->user): ?>
                    <strong>Użytkownik:</strong> <?= Html::encode($model->user->username) ?><br>
                    <?php endif; ?>
                </div>
                <div class="alert alert-warning">
                    <strong>Uwaga:</strong> Ta operacja jest nieodwracalna!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <?= Html::beginForm(['delete'], 'post') ?>
                <?= Html::hiddenInput('id', $model->id) ?>
                <button type="submit" class="btn btn-danger">Usuń wpis</button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>