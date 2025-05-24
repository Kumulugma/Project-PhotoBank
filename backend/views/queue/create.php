<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
\backend\assets\AppAsset::registerControllerCss($this, 'queue');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
\backend\assets\AppAsset::registerComponentCss($this, 'alerts');
/* @var $this yii\web\View */
/* @var $model common\models\QueuedJob */
/* @var $jobTypes array */

$this->title = 'Nowe zadanie';
$this->params['breadcrumbs'][] = ['label' => 'Kolejka zadań', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="queued-job-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista zadań', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Szczegóły zadania
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'needs-validation'],
                    ]); ?>

                    <?= $form->field($model, 'type')->dropDownList($jobTypes, [
                        'class' => 'form-select',
                        'id' => 'job-type',
                        'prompt' => '- Wybierz typ zadania -',
                        'required' => true,
                    ])->label('Typ zadania') ?>

                    <div class="mb-3">
                        <label class="form-label">Parametry (JSON)</label>
                        <?= Html::textarea('QueuedJob[params]', '', [
                            'class' => 'form-control',
                            'id' => 'job-params',
                            'rows' => 8,
                            'placeholder' => "{\n  \"key\": \"value\"\n}"
                        ]) ?>
                        <div class="form-text">Wprowadź parametry zadania w formacie JSON</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="run_now" value="1" id="run-now">
                            <label class="form-check-label" for="run-now">
                                <i class="fas fa-play-circle me-1"></i>Uruchom natychmiast
                            </label>
                            <div class="form-text">Jeśli zaznaczone, zadanie zostanie wykonane od razu, a nie umieszczone w kolejce</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i>Utwórz zadanie', [
                            'class' => 'btn btn-success'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['index'], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szablony parametrów
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="paramTemplates">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSync">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSync" aria-expanded="false" aria-controls="collapseSync">
                                    Synchronizacja S3
                                </button>
                            </h2>
                            <div id="collapseSync" class="accordion-collapse collapse" aria-labelledby="headingSync" data-bs-parent="#paramTemplates">
                                <div class="accordion-body">
                                    <pre><code class="text-muted">{
  "delete_local": false
}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-primary use-template" data-type="s3_sync" data-template='{"delete_local": false}'>
                                        Użyj szablonu
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThumbnails">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThumbnails" aria-expanded="false" aria-controls="collapseThumbnails">
                                    Regeneracja miniatur
                                </button>
                            </h2>
                            <div id="collapseThumbnails" class="accordion-collapse collapse" aria-labelledby="headingThumbnails" data-bs-parent="#paramTemplates">
                                <div class="accordion-body">
                                    <pre><code class="text-muted">{
  "size_id": null,
  "photo_id": null,
  "partial": false
}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-primary use-template" data-type="regenerate_thumbnails" data-template='{"size_id": null, "photo_id": null, "partial": false}'>
                                        Użyj szablonu
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingAnalyze">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnalyze" aria-expanded="false" aria-controls="collapseAnalyze">
                                    Analiza AI zdjęcia
                                </button>
                            </h2>
                            <div id="collapseAnalyze" class="accordion-collapse collapse" aria-labelledby="headingAnalyze" data-bs-parent="#paramTemplates">
                                <div class="accordion-body">
                                    <pre><code class="text-muted">{
  "photo_id": 1,
  "analyze_tags": true,
  "analyze_description": true
}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-primary use-template" data-type="analyze_photo" data-template='{"photo_id": 1, "analyze_tags": true, "analyze_description": true}'>
                                        Użyj szablonu
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingBatch">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBatch" aria-expanded="false" aria-controls="collapseBatch">
                                    Analiza AI wsadowa
                                </button>
                            </h2>
                            <div id="collapseBatch" class="accordion-collapse collapse" aria-labelledby="headingBatch" data-bs-parent="#paramTemplates">
                                <div class="accordion-body">
                                    <pre><code class="text-muted">{
  "photo_ids": [1, 2, 3],
  "analyze_tags": true,
  "analyze_description": true
}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-primary use-template" data-type="analyze_batch" data-template='{"photo_ids": [1, 2, 3], "analyze_tags": true, "analyze_description": true}'>
                                        Użyj szablonu
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingImport">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImport" aria-expanded="false" aria-controls="collapseImport">
                                    Import zdjęć
                                </button>
                            </h2>
                            <div id="collapseImport" class="accordion-collapse collapse" aria-labelledby="headingImport" data-bs-parent="#paramTemplates">
                                <div class="accordion-body">
                                    <pre><code class="text-muted">{
  "directory": "uploads/import",
  "recursive": true
}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-primary use-template" data-type="import_photos" data-template='{"directory": "uploads/import", "recursive": true}'>
                                        Użyj szablonu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>Pomoc
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-cogs me-2"></i>Typy zadań</h6>
                        <ul class="mb-0">
                            <li><strong>Synchronizacja S3:</strong> Przesyła zdjęcia do magazynu S3</li>
                            <li><strong>Regeneracja miniatur:</strong> Tworzy nowe miniatury dla wybranych rozmiarów</li>
                            <li><strong>Analiza AI zdjęcia:</strong> Analizuje pojedyncze zdjęcie</li>
                            <li><strong>Analiza AI wsadowa:</strong> Analizuje wiele zdjęć jednocześnie</li>
                            <li><strong>Import zdjęć:</strong> Importuje zdjęcia z określonego katalogu</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Ważne</h6>
                        <p class="mb-0">Zadania przetwarzane są w tle przez Cron. Czas wykonania może zależeć od obciążenia systemu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>