<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;

\backend\assets\AppAsset::registerControllerCss($this, 'tags');

$this->title = 'Zarządzanie tagami';
$this->params['breadcrumbs'][] = $this->title;

// Podstawowe statystyki
$totalTags = Tag::find()->count();
$activeTags = Tag::find()->where(['>', 'frequency', 0])->count();
$popularTags = Tag::find()->orderBy(['frequency' => SORT_DESC])->limit(10)->all();
?>

<div class="tag-index">
    <!-- Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-hashtag me-3"></i>
                        Zarządzanie tagami
                    </h1>
                    <p class="subtitle mb-0">
                        Organizuj swoje zdjęcia za pomocą tagów
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj tag', ['create'], [
                        'class' => 'btn btn-light'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Statystyki -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value"><?= $totalTags ?></div>
                <div class="stat-label">
                    <i class="fas fa-hashtag me-2"></i>
                    Wszystkich tagów
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $activeTags ?></div>
                <div class="stat-label">
                    <i class="fas fa-fire me-2"></i>
                    Aktywnych tagów
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalTags > 0 ? round(($activeTags / $totalTags) * 100) : 0 ?>%</div>
                <div class="stat-label">
                    <i class="fas fa-chart-line me-2"></i>
                    Wykorzystanych
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tabela tagów -->
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-list me-2"></i>
                            Lista tagów
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <?php Pjax::begin(); ?>

                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'summary' => 'Wyświetlono <b>{begin}-{end}</b> z <b>{totalCount}</b> wpisów',
                            'tableOptions' => ['class' => 'table table-hover mb-0'],
                            'columns' => [
                                [
                                    'class' => 'yii\grid\SerialColumn',
                                    'headerOptions' => ['style' => 'width: 60px'],
                                ],
                                [
                                    'attribute' => 'name',
                                    'label' => 'Tag',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        return '<span class="badge bg-info">#' . Html::encode($model->name) . '</span>';
                                    },
                                ],
                                [
                                    'attribute' => 'frequency',
                                    'label' => 'Użyć',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $frequency = $model->frequency;
                                        if ($frequency == 0) {
                                            return '<span class="badge bg-light text-dark">' . $frequency . '</span>';
                                        } elseif ($frequency < 5) {
                                            return '<span class="badge bg-warning">' . $frequency . '</span>';
                                        } elseif ($frequency < 20) {
                                            return '<span class="badge bg-primary">' . $frequency . '</span>';
                                        } else {
                                            return '<span class="badge bg-success">' . $frequency . '</span>';
                                        }
                                    },
                                    'headerOptions' => ['style' => 'width: 100px'],
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'label' => 'Utworzono',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $date = date('Y-m-d', $model->created_at);
                                        return '<small>' . $date . '</small>';
                                    },
                                    'headerOptions' => ['style' => 'width: 120px'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => 'Akcje',
                                    'headerOptions' => ['style' => 'width: 140px'],
                                    'contentOptions' => ['style' => 'white-space: nowrap;'],
                                    'template' => '<div class="btn-group btn-group-sm" role="group">{view}{update}{delete}</div>',
                                    'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                                'class' => 'btn btn-outline-primary',
                                                'title' => 'Podgląd'
                                            ]);
                                        },
                                        'update' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                                'class' => 'btn btn-outline-secondary',
                                                'title' => 'Edytuj'
                                            ]);
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            $confirmMessage = $model->frequency > 0 
                                                ? "Ten tag jest używany w {$model->frequency} zdjęciach. Czy na pewno chcesz go usunąć?"
                                                : 'Czy na pewno chcesz usunąć ten tag?';
                                                
                                            return Html::a('<i class="fas fa-trash"></i>', $url, [
                                                'class' => 'btn btn-outline-danger',
                                                'title' => 'Usuń',
                                                'data-confirm' => $confirmMessage,
                                                'data-method' => 'post',
                                            ]);
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>

                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Popularne tagi -->
                <?php if (!empty($popularTags)): ?>
                <div class="tags-cloud">
                    <h3>
                        <i class="fas fa-fire me-2"></i>
                        Popularne tagi
                    </h3>
                    <div>
                        <?php foreach ($popularTags as $tag): 
                            $sizeClass = 'size-md';
                            if ($tag->frequency >= 50) $sizeClass = 'size-xl';
                            elseif ($tag->frequency >= 20) $sizeClass = 'size-lg';
                            elseif ($tag->frequency >= 10) $sizeClass = 'size-md';
                            elseif ($tag->frequency >= 5) $sizeClass = 'size-sm';
                            else $sizeClass = 'size-xs';
                        ?>
                            <a href="<?= yii\helpers\Url::to(['view', 'id' => $tag->id]) ?>" 
                               class="tag-item <?= $sizeClass ?>"
                               title="<?= Html::encode($tag->name) ?> (<?= $tag->frequency ?> użyć)">
                                #<?= Html::encode($tag->name) ?>
                                <span class="badge bg-white text-dark ms-1"><?= $tag->frequency ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Szybkie akcje -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-bolt me-2"></i>
                        Szybkie akcje
                    </h5>
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-plus me-2"></i>Dodaj tag', ['create'], [
                            'class' => 'btn btn-success'
                        ]) ?>
                        
                        <?php if ($totalTags - $activeTags > 0): ?>
                        <?= Html::a('<i class="fas fa-broom me-2"></i>Usuń nieużywane (' . ($totalTags - $activeTags) . ')', '#', [
                            'class' => 'btn btn-warning',
                            'onclick' => 'cleanupUnusedTags(); return false;'
                        ]) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Wskazówki -->
                <div class="sidebar-card">
                    <h5>
                        <i class="fas fa-info-circle me-2"></i>
                        Wskazówki
                    </h5>
                    <div class="alert alert-info">
                        <h6>Najlepsze praktyki</h6>
                        <ul class="mb-0">
                            <li>Używaj krótkich nazw</li>
                            <li>Zastępuj spacje myślnikami</li>
                            <li>Sprawdź duplikaty</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6>Oznaczenia popularności</h6>
                        <div class="small">
                            <div><span class="badge bg-light text-dark">0</span> Nieużywany</div>
                            <div><span class="badge bg-warning">1-4</span> Rzadki</div>
                            <div><span class="badge bg-primary">5-19</span> Popularny</div>
                            <div><span class="badge bg-success">20+</span> Bardzo popularny</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Proste funkcje bez jQuery
function cleanupUnusedTags() {
    if (confirm('Czy na pewno chcesz usunąć wszystkie nieużywane tagi?')) {
        // Tutaj możesz dodać AJAX call do usuwania
        showSimpleToast('Funkcja w przygotowaniu', 'warning');
    }
}

// Animacja pasków popularności
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const bars = document.querySelectorAll('.popularity-fill');
        bars.forEach(function(bar) {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function() {
                bar.style.width = width;
            }, 100);
        });
    }, 500);
});
</script>