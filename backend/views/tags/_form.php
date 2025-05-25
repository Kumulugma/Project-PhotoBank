<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

\backend\assets\AppAsset::registerControllerAssets($this, 'tags');

$isUpdate = !$model->isNewRecord;
?>

<div class="tag-form">
    <!-- Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-<?= $isUpdate ? 'edit' : 'plus' ?> me-3"></i>
                        <?= $isUpdate ? 'Edytuj tag' : 'Dodaj nowy tag' ?>
                    </h1>
                    <p class="subtitle mb-0">
                        <?= $isUpdate ? 'Modyfikuj istniejący tag' : 'Utwórz nowy tag do kategoryzacji zdjęć' ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Powrót', ['index'], [
                        'class' => 'btn btn-light'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Główny formularz -->
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-hashtag me-2"></i>
                            Dane tagu
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'tag-form',
                        ]); ?>

                        <?= $form->field($model, 'name')->textInput([
                            'maxlength' => true,
                            'class' => 'form-control',
                            'required' => true,
                            'placeholder' => 'np. krajobrazy, portret, natura...',
                            'id' => 'tag-name-input'
                        ])->label('Nazwa tagu')->hint('Wprowadź nazwę bez znaku # (zostanie dodany automatycznie)') ?>

                        <?php if ($isUpdate): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6>Statystyki</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <strong><?= $model->frequency ?></strong><br>
                                            <small>Użyć</small>
                                        </div>
                                        <div class="col-6">
                                            <strong><?= date('Y-m-d', $model->created_at) ?></strong><br>
                                            <small>Utworzono</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if ($model->frequency > 0): ?>
                                <div class="alert alert-warning">
                                    <h6>Uwaga</h6>
                                    <p class="mb-0">Tag używany w <?= $model->frequency ?> zdjęciach.</p>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <h6>Bezpieczna edycja</h6>
                                    <p class="mb-0">Tag nie jest jeszcze używany.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 justify-content-between">
                            <div>
                                <?= Html::submitButton($isUpdate ? '<i class="fas fa-save me-2"></i>Zapisz' : '<i class="fas fa-plus me-2"></i>Utwórz', [
                                    'class' => 'btn btn-success',
                                    'id' => 'submit-btn'
                                ]) ?>
                                
                                <?= Html::a('<i class="fas fa-times me-2"></i>Anuluj', ['index'], [
                                    'class' => 'btn btn-secondary'
                                ]) ?>
                            </div>
                            
                            <?php if ($isUpdate && $model->frequency == 0): ?>
                            <div>
                                <?= Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data-confirm' => 'Czy na pewno chcesz usunąć ten tag?',
                                    'data-method' => 'post',
                                ]) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Podgląd -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-eye me-2"></i>
                        Podgląd
                    </h5>
                    <div class="tag-preview">
                        <div class="mb-3">
                            <span class="badge bg-info" id="preview-badge">
                                #<span id="preview-text"><?= $isUpdate ? Html::encode($model->name) : 'wprowadź-nazwę' ?></span>
                            </span>
                        </div>
                        <small class="text-muted">Tak będzie wyglądał tag</small>
                    </div>
                </div>

                <!-- Wskazówki -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-lightbulb me-2"></i>
                        Najlepsze praktyki
                    </h5>
                    <div class="alert alert-success">
                        <h6>Zalecane</h6>
                        <ul class="mb-0">
                            <li>Używaj małych liter</li>
                            <li>Zastępuj spacje myślnikami</li>
                            <li>Bądź opisowy ale zwięzły</li>
                            <li>Sprawdź czy tag nie istnieje</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6>Unikaj</h6>
                        <ul class="mb-0">
                            <li>Długich nazw (>20 znaków)</li>
                            <li>Znaków specjalnych</li>
                            <li>Duplikowania</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <h6>Przykłady</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-secondary example-tag" data-name="krajobrazy">#krajobrazy</span>
                            <span class="badge bg-secondary example-tag" data-name="portret">#portret</span>
                            <span class="badge bg-secondary example-tag" data-name="natura">#natura</span>
                            <span class="badge bg-secondary example-tag" data-name="architektura">#architektura</span>
                        </div>
                    </div>
                </div>

                <!-- Popularne tagi -->
                <?php 
                $popularTags = \common\models\Tag::find()
                    ->orderBy(['frequency' => SORT_DESC])
                    ->limit(6)
                    ->all();
                
                if (!empty($popularTags)): ?>
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-fire me-2"></i>
                        Popularne tagi
                    </h5>
                    <p class="text-muted small">Sprawdź popularne tagi:</p>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($popularTags as $tag): ?>
                            <a href="<?= yii\helpers\Url::to(['view', 'id' => $tag->id]) ?>" 
                               class="badge bg-outline-secondary text-decoration-none">
                                #<?= Html::encode($tag->name) ?> (<?= $tag->frequency ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Proste funkcje bez jQuery
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus
    const nameInput = document.getElementById('tag-name-input');
    if (nameInput) {
        nameInput.focus();
    }
    
    // Przykładowe tagi
    const exampleTags = document.querySelectorAll('.example-tag');
    exampleTags.forEach(function(tag) {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.getAttribute('data-name');
            if (nameInput) {
                nameInput.value = name;
                nameInput.dispatchEvent(new Event('input'));
                nameInput.focus();
            }
        });
    });
});

// Skrót klawiszowy Ctrl+S
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) {
            submitBtn.click();
        }
    }
});
</script>