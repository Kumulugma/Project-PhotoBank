<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;

$this->title = 'Komendy Consolowe';
$this->params['breadcrumbs'][] = $this->title;

// Rejestracja CSS dla lepszego wyglądu
$this->registerCss("
.command-card {
    margin-bottom: 2rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.command-header {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    padding: 1rem 1.5rem;
}

.command-body {
    padding: 1.5rem;
}

.command-item {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    border-radius: 0.25rem;
}

.command-item:last-child {
    margin-bottom: 0;
}

.command-code {
    background: #212529;
    color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    border: 1px solid #495057;
}

.param-list {
    background: #e9ecef;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-top: 0.5rem;
}

.param-list dt {
    font-weight: 600;
    color: #495057;
}

.param-list dd {
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.execute-section {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    margin-top: 2rem;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

.output-box {
    background: #212529;
    color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.85rem;
    white-space: pre-wrap;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #495057;
}

.copy-btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.command-search {
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .command-code {
        font-size: 0.75rem;
        word-break: break-all;
    }
}
");

// JavaScript dla funkcjonalności kopiowania i wyszukiwania
$this->registerJs("
// Funkcja kopiowania do schowka
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Pokazanie powiadomienia
        var alert = $('<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">' +
            '<i class=\"fas fa-check-circle me-2\"></i>Skopiowano do schowka!' +
            '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>' +
            '</div>');
        $('.container-fluid').prepend(alert);
        
        setTimeout(function() {
            alert.alert('close');
        }, 2000);
    });
}

// Wyszukiwanie komend
$('#commandSearch').on('input', function() {
    var searchTerm = $(this).val().toLowerCase();
    
    $('.command-card').each(function() {
        var card = $(this);
        var content = card.text().toLowerCase();
        
        if (searchTerm === '' || content.includes(searchTerm)) {
            card.show();
        } else {
            card.hide();
        }
    });
});

// Dodanie przycisków kopiowania
$('.command-code').each(function() {
    var codeBlock = $(this);
    var command = codeBlock.text().trim();
    
    var copyBtn = $('<button class=\"btn btn-outline-light btn-sm copy-btn float-end\" type=\"button\">' +
        '<i class=\"fas fa-copy me-1\"></i>Kopiuj</button>');
    
    copyBtn.on('click', function(e) {
        e.preventDefault();
        copyToClipboard(command);
    });
    
    codeBlock.append(copyBtn);
});
");
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

    <!-- Output dla wykonanej komendy -->
    <?php if (Yii::$app->session->hasFlash('commandOutput')): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-terminal me-2"></i>Wynik wykonania komendy
            </div>
            <div class="card-body p-0">
                <div class="output-box"><?= Html::encode(Yii::$app->session->getFlash('commandOutput')) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Wyszukiwarka -->
    <div class="command-search">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" id="commandSearch" class="form-control" placeholder="Szukaj komend...">
        </div>
    </div>

    <!-- Lista komend -->
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

    <!-- Sekcja wykonywania komend (tylko w środowisku deweloperskim) -->
    <?php if (!YII_ENV_PROD): ?>
        <div class="execute-section">
            <h4 class="mb-3">
                <i class="fas fa-play me-2"></i>Wykonywanie Komend
                <small class="badge bg-warning text-dark ms-2">TYLKO DEWELOPERSKIE</small>
            </h4>
            
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Uwaga:</strong> Ta funkcja jest dostępna tylko w środowisku deweloperskim i pozwala na wykonywanie wybranych komend bezpiecznie.
            </div>
            
            <?= Html::beginForm(['execute'], 'post', ['class' => 'row g-3']) ?>
                <div class="col-md-8">
                    <select name="command" class="form-select" required>
                        <option value="">Wybierz komendę...</option>
                        <option value="yii migrate/status">yii migrate/status - Status migracji</option>
                        <option value="yii import/info">yii import/info - Info o systemie plików</option>
                        <option value="yii debug/import-jobs">yii debug/import-jobs - Status zadań importu</option>
                        <option value="yii debug/queue-photos">yii debug/queue-photos - Zdjęcia w poczekalni</option>
                        <option value="yii exif/stats">yii exif/stats - Statystyki EXIF</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-light w-100">
                        <i class="fas fa-play me-2"></i>Wykonaj
                    </button>
                </div>
            <?= Html::endForm() ?>
        </div>
    <?php endif; ?>

    <!-- Informacje dodatkowe -->
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
                        <li><i class="fas fa-database me-2 text-info"></i>Komendy migracji zmieniają strukturę bazy danych</li>
                        <li><i class="fas fa-clock me-2 text-success"></i>Długie operacje wykonuj poza godzinami szczytu</li>
                        <li><i class="fas fa-backup me-2 text-danger"></i>Zawsze rób kopię zapasową przed ważnymi operacjami</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>