<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Tag;
use common\models\Category;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PhotoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Poczekalnia zdjęć';
$this->params['breadcrumbs'][] = ['label' => 'Zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get all tags and categories for filter dropdowns
$tags = ArrayHelper::map(Tag::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$categories = ArrayHelper::map(Category::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
?>
<div class="photo-queue">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?=
            Html::a('<i class="fas fa-upload me-2"></i>Prześlij więcej', ['upload'], [
                'class' => 'btn btn-success'
            ])
            ?>
            <?=
            Html::a('<i class="fas fa-file-import me-2"></i>Importuj z FTP', ['import-from-ftp'], [
                'class' => 'btn btn-info',
                'data-method' => 'post',
                'data-confirm' => 'Czy na pewno chcesz zaimportować zdjęcia z domyślnego katalogu FTP?',
            ])
            ?>
<?=
Html::a('<i class="fas fa-file-import me-2"></i>Import zdjęć', ['import'], [
    'class' => 'btn btn-success'
])
?>
            <button type="button" class="btn btn-primary batch-action-btn" style="display: none;" 
                    data-bs-toggle="modal" data-bs-target="#batchApproveModal">
                <i class="fas fa-check me-2"></i>Zatwierdź zaznaczone
            </button>
            <button type="button" class="btn btn-danger batch-action-btn" style="display: none;"
                    data-bs-toggle="modal" data-bs-target="#batchDeleteModal">
                <i class="fas fa-trash me-2"></i>Usuń zaznaczone
            </button>
        </div>
    </div>

        <?php if ($dataProvider->totalCount == 0): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-clock fa-3x mb-3"></i>
            <h4>Brak zdjęć w kolejce</h4>
            <p>Wszystkie zdjęcia zostały już przetworzone lub nie ma żadnych oczekujących.</p>
    <?=
    Html::a('<i class="fas fa-upload me-2"></i>Prześlij nowe zdjęcia', ['upload'], [
        'class' => 'btn btn-primary'
    ])
    ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= $dataProvider->totalCount ?></strong> zdjęć oczekuje na zatwierdzenie.
            Po zatwierdzeniu zostaną przeniesione do głównej galerii.
        </div>

        <?php Pjax::begin(['id' => 'photos-queue-pjax']); ?>

        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($model) {
                        return ['value' => $model->id, 'name' => 'selection[]'];
                    }
                ],
                [
                    'attribute' => 'id',
                    'headerOptions' => ['style' => 'width: 80px;'],
                    'contentOptions' => ['class' => 'fw-bold'],
                ],
                [
                'label' => 'Miniatura',
                'format' => 'raw',
                'value' => function ($model) {
                    $thumbnailUrl = $model->getListThumbnail();
                    
                    if ($thumbnailUrl) {
                        return Html::img($thumbnailUrl, [
                                    'class' => 'img-thumbnail',
                                    'style' => 'max-width: 80px; max-height: 80px; object-fit: cover;',
                                    'alt' => $model->title,
                        ]);
                    } else {
                        return '<div class="text-center p-2" style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>';
                    }
                },
                'filter' => false,
                'headerOptions' => ['style' => 'width: 100px;'],
            ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(Html::encode($model->title), ['view', 'id' => $model->id], [
                            'class' => 'fw-bold text-decoration-none'
                        ]);
                    },
                ],
                [
                    'attribute' => 'file_name',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<code class="small">' . Html::encode($model->file_name) . '</code>';
                    },
                ],
                [
                    'label' => 'Rozmiar',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span class="badge bg-info">' .
                        Yii::$app->formatter->asShortSize($model->file_size, 2) .
                        '</span><br><small class="text-muted">' .
                        $model->width . '×' . $model->height . 'px</small>';
                    },
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 100px;'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span title="' . date('Y-m-d H:i:s', $model->created_at) . '">' .
                        Yii::$app->formatter->asRelativeTime($model->created_at) . '</span>';
                    },
                    'filter' => Html::activeTextInput($searchModel, 'created_at', [
                        'class' => 'form-control',
                        'placeholder' => 'YYYY-MM-DD'
                    ]),
                    'headerOptions' => ['style' => 'width: 140px;'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {approve} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Zobacz',
                                'data-pjax' => 0,
                            ]);
                        },
                        'approve' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-check"></i>', ['approve', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-success',
                                'title' => 'Zatwierdź',
                                'data-method' => 'post',
                                'data-confirm' => 'Czy na pewno zatwierdzić to zdjęcie? Zostanie przeniesione do głównej galerii.',
                                'data-pjax' => 0,
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-trash"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'Usuń',
                                'data-confirm' => 'Czy na pewno usunąć to zdjęcie z kolejki?',
                                'data-method' => 'post',
                                'data-pjax' => 0,
                            ]);
                        },
                    ],
                    'headerOptions' => ['style' => 'width: 130px;'],
                    'contentOptions' => ['class' => 'text-end'],
                ],
            ],
        ]);
        ?>

    <?php Pjax::end(); ?>
            <?php endif; ?>
</div>

<!-- Batch Approve Modal -->
<div class="modal fade" id="batchApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Zatwierdź zaznaczone zdjęcia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
<?php
$form = \yii\bootstrap5\ActiveForm::begin([
            'id' => 'batch-approve-form',
            'action' => ['approve-batch'],
        ]);
?>
            <div class="modal-body">
                <input type="hidden" name="ids" id="approve-photo-ids">

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Wybrane zdjęcia zostaną zatwierdzone i przeniesione do głównej galerii.
                    Ta operacja jest nieodwracalna.
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="auto_publish" value="1" id="auto-publish" checked>
                        <label class="form-check-label" for="auto-publish">
                            Ustaw jako publiczne
                        </label>
                        <div class="form-text">Zatwierdzone zdjęcia będą widoczne dla wszystkich użytkowników</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-success" id="batch-approve-submit">
                    <i class="fas fa-check me-1"></i>Zatwierdź zdjęcia
                </button>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Batch Delete Modal -->
<div class="modal fade" id="batchDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Usuń zaznaczone zdjęcia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
<?php
$form = \yii\bootstrap5\ActiveForm::begin([
            'id' => 'batch-delete-form',
            'action' => ['batch-delete'],
        ]);
?>
            <div class="modal-body">
                <input type="hidden" name="ids" id="delete-photo-ids">

                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Uwaga!</strong> Ta akcja jest nieodwracalna. 
                    Wybrane zdjęcia zostaną trwale usunięte z systemu.
                </div>

                <p>Czy na pewno chcesz usunąć zaznaczone zdjęcia z kolejki?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-danger" id="batch-delete-submit">
                    <i class="fas fa-trash me-1"></i>Usuń zdjęcia
                </button>
            </div>
<?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle batch operations
        const checkboxes = document.querySelectorAll('input[name="selection[]"]');
        const batchButtons = document.querySelectorAll('.batch-action-btn');
        const selectAll = document.querySelector('input[name="selection_all"]');

        function updateBatchButtons() {
            const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
            batchButtons.forEach(btn => {
                btn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
            });
        }

        // Select all functionality
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBatchButtons();
            });
        }

        // Individual checkboxes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBatchButtons);
        });

        // Batch approve form submission
        document.getElementById('batch-approve-submit').addEventListener('click', function () {
            const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            document.getElementById('approve-photo-ids').value = ids.join(',');
            document.getElementById('batch-approve-form').submit();
        });

        // Batch delete form submission
        document.getElementById('batch-delete-submit').addEventListener('click', function () {
            const checkedBoxes = document.querySelectorAll('input[name="selection[]"]:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            document.getElementById('delete-photo-ids').value = ids.join(',');
            document.getElementById('batch-delete-form').submit();
        });
    });
</script>