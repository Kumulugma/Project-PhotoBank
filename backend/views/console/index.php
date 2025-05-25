<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;

$this->title = 'Komendy Consolowe';
$this->params['breadcrumbs'][] = $this->title;

\backend\assets\AppAsset::registerControllerAssets($this, 'console');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
?>

<div class="console-commands">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-terminal me-3"></i><?= Html::encode($this->title) ?></h1>
        <div class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            <?= count($commands) ?> kategorii komend
        </div>
    </div>

    <?= Alert::widget() ?>

    <div class="command-search">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" id="commandSearch" class="form-control" placeholder="Szukaj komend... (Ctrl+K)">
        </div>
    </div>

    <?php foreach ($commands as $category => $categoryData): ?>
        <div class="command-card">
            <div class="command-header">
                <h3 class="mb-1">
                    <i class="fas fa-folder-open me-2"></i>
                    <?= Html::encode($categoryData['title']) ?>
                </h3>
                <p class="mb-0 opacity-75"><?= Html::encode($categoryData['description']) ?></p>
            </div>
            
            <div class="command-body">
                <?php foreach ($categoryData['commands'] as $command): ?>
                    <div class="command-item">
                        <h5 class="text-primary mb-2">
                            <i class="fas fa-play-circle me-2"></i>
                            <?= Html::encode($command['command']) ?>
                        </h5>
                        
                        <p class="text-muted mb-3"><?= Html::encode($command['description']) ?></p>
                        
                        <div class="command-code">
                            <?= Html::encode($command['example']) ?>
                        </div>
                        
                        <?php if (!empty($command['params'])): ?>
                            <div class="param-list">
                                <h6 class="text-dark mb-2">
                                    <i class="fas fa-cog me-1"></i>Parametry:
                                </h6>
                                <dl class="row mb-0">
                                    <?php foreach ($command['params'] as $param => $description): ?>
                                        <dt class="col-sm-3 col-md-2">--<?= Html::encode($param) ?></dt>
                                        <dd class="col-sm-9 col-md-10"><?= Html::encode($description) ?></dd>
                                    <?php endforeach; ?>
                                </dl>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-lightbulb me-2"></i>Przydatne Informacje
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-terminal me-2"></i>Jak używać komend:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-arrow-right me-2 text-primary"></i>Przejdź do katalogu głównego projektu</li>
                        <li><i class="fas fa-arrow-right me-2 text-primary"></i>Użyj: <code>php yii [komenda]</code></li>
                        <li><i class="fas fa-arrow-right me-2 text-primary"></i>Parametry: <code>--param=wartość</code></li>
                        <li><i class="fas fa-arrow-right me-2 text-primary"></i>Pomoc: <code>php yii help [komenda]</code></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne uwagi:</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-shield-alt me-2 text-warning"></i>Niektóre komendy wymagają uprawnień administratora</li>
                        <li><i class="fas fa-clock me-2 text-success"></i>Długie operacje wykonuj poza godzinami szczytu</li>
                        <li><i class="fas fa-backup me-2 text-danger"></i>Zawsze rób kopię zapasową przed ważnymi operacjami</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>