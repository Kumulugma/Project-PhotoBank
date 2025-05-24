<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

\backend\assets\AppAsset::registerControllerCss($this, 'photos');
\backend\assets\AppAsset::registerComponentCss($this, 'modals');
$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Zdjęcia', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statusOptions = [
    \common\models\Photo::STATUS_QUEUE => 'W kolejce',
    \common\models\Photo::STATUS_ACTIVE => 'Aktywne',
    \common\models\Photo::STATUS_DELETED => 'Usunięte',
];
?>
<div class="photo-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?=
            Html::a('<i class="fas fa-list me-2"></i>Lista zdjęć', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ])
            ?>
        </div>
    </div>

    <!-- Search Code Display -->
    <div class="alert alert-info mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <strong><i class="fas fa-search me-2"></i>Kod wyszukiwania:</strong>
                <code class="fs-5 ms-2"><?= Html::encode($model->search_code) ?></code>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="copySearchCode()">
                    <i class="fas fa-copy me-1"></i>Kopiuj kod
                </button>
            </div>
        </div>
    </div>

    <!-- Stock and AI Status Display -->
<?php if ($model->isUploadedToAnyStock() || $model->isUsedInPrivateProject() || $model->isAiGenerated()): ?>
        <div class="row mb-4">
    <?php if ($model->isUploadedToAnyStock() || $model->isUsedInPrivateProject()): ?>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-store me-2"></i>Status stockowy</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if ($model->isUploadedToShutterstock()): ?>
                                <span class="badge bg-success"><i class="fas fa-camera me-1"></i>Shutterstock</span>
                            <?php endif; ?>
                            <?php if ($model->isUploadedToAdobeStock()): ?>
                                <span class="badge bg-primary"><i class="fab fa-adobe me-1"></i>Adobe Stock</span>
                            <?php endif; ?>
        <?php if ($model->isUsedInPrivateProject()): ?>
                                <span class="badge bg-info"><i class="fas fa-briefcase me-1"></i>Prywatny projekt</span>
                <?php endif; ?>
                        </div>
                    </div>
                </div>
    <?php endif; ?>

    <?php if ($model->isAiGenerated()): ?>
                <div class="col-md-6">
                    <div class="alert alert-warning border-warning">
                        <h6><i class="fas fa-robot me-2"></i>Zdjęcie AI</h6>
                        <p class="mb-2"><strong>Wygenerowane przez sztuczną inteligencję</strong></p>
                        <?php if ($model->hasAiPrompt()): ?>
                            <small class="d-block"><strong>Prompt:</strong> <?= Html::encode(mb_substr($model->ai_prompt, 0, 100)) ?><?= mb_strlen($model->ai_prompt) > 100 ? '...' : '' ?></small>
        <?php endif; ?>
        <?php if ($model->hasAiGeneratorUrl()): ?>
                            <small class="d-block mt-1">
                                <a href="<?= Html::encode($model->ai_generator_url) ?>" target="_blank" class="alert-link">
                                    <i class="fas fa-external-link-alt me-1"></i>Zobacz w generatorze
                                </a>
                            </small>
                <?php endif; ?>
                    </div>
                </div>
    <?php endif; ?>
        </div>
<?php endif; ?>

    <!-- Action buttons -->
    <div class="mb-4">
        <div class="btn-group me-2">
            <?=
            Html::a('<i class="fas fa-edit me-2"></i>Edytuj', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ])
            ?>

            <?php if ($model->status === \common\models\Photo::STATUS_QUEUE): ?>
                <?=
                Html::a('<i class="fas fa-check me-2"></i>Zatwierdź', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'Czy na pewno chcesz zatwierdzić to zdjęcie? Zostanie przeniesione do magazynu S3.',
                    'data-method' => 'post',
                ])
                ?>
<?php endif; ?>

            <?=
            Html::a('<i class="fas fa-robot me-2"></i>Analiza AI', ['/ai/analyze-photo', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data-method' => 'post',
                'title' => 'Uruchom analizę AI tego zdjęcia',
            ])
            ?>
        </div>

        <div class="btn-group">
<?=
Html::a('<i class="fas fa-trash me-2"></i>Usuń', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data-confirm' => 'Czy na pewno chcesz usunąć to zdjęcie?',
    'data-method' => 'post',
])
?>
        </div>
    </div>

    <div class="row">
        <!-- Main content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Szczegóły zdjęcia
                    </h5>
                </div>
                <div class="card-body">
                    <?=
                    DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
                            ],
                            [
                                'attribute' => 'search_code',
                                'label' => 'Kod wyszukiwania',
                                'format' => 'raw',
                                'value' => '<code class="badge bg-secondary fs-6">' . Html::encode($model->search_code) . '</code>',
                            ],
                            [
                                'attribute' => 'title',
                                'label' => 'Tytuł',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'description',
                                'label' => 'Opis',
                                'format' => 'ntext',
                                'value' => $model->description ?: 'Brak opisu',
                            ],
                            [
                                'attribute' => 'english_description',
                                'label' => 'Opis w języku angielskim',
                                'format' => 'ntext',
                                'value' => $model->english_description ?: 'Brak opisu w języku angielskim',
                                'visible' => !empty($model->english_description),
                            ],
                            [
                                'attribute' => 'series',
                                'label' => 'Seria',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (empty($model->series)) {
                                        return '<span class="text-muted">Nie przypisano</span>';
                                    }
                                    return '<span class="badge bg-info fs-6"><i class="fas fa-layer-group me-1"></i>' . Html::encode($model->series) . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'file_name',
                                'label' => 'Nazwa pliku',
                                'format' => 'text',
                            ],
                            [
                                'label' => 'Wymiary',
                                'value' => $model->width . ' × ' . $model->height . ' px',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'file_size',
                                'label' => 'Rozmiar pliku',
                                'value' => Yii::$app->formatter->asShortSize($model->file_size, 2),
                            ],
                            [
                                'attribute' => 'mime_type',
                                'label' => 'Typ MIME',
                                'format' => 'text',
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Status',
                                'format' => 'raw',
                                'value' => function ($model) use ($statusOptions) {
                                    $status = $statusOptions[$model->status] ?? 'Nieznany';
                                    $badgeClass = match ($model->status) {
                                        \common\models\Photo::STATUS_QUEUE => 'bg-warning',
                                        \common\models\Photo::STATUS_ACTIVE => 'bg-success',
                                        \common\models\Photo::STATUS_DELETED => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'is_public',
                                'label' => 'Widoczność',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $class = $model->is_public ? 'bg-success' : 'bg-secondary';
                                    $text = $model->is_public ? 'Publiczne' : 'Prywatne';
                                    $icon = $model->is_public ? 'fa-eye' : 'fa-eye-slash';
                                    return '<span class="badge ' . $class . '"><i class="fas ' . $icon . ' me-1"></i>' . $text . '</span>';
                                },
                            ],
                            [
                                'label' => 'Platformy stockowe',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $platforms = [];
                                    if ($model->isUploadedToShutterstock()) {
                                        $platforms[] = '<span class="badge bg-success me-1"><i class="fas fa-camera me-1"></i>Shutterstock</span>';
                                    }
                                    if ($model->isUploadedToAdobeStock()) {
                                        $platforms[] = '<span class="badge bg-primary me-1"><i class="fab fa-adobe me-1"></i>Adobe Stock</span>';
                                    }
                                    if ($model->isUsedInPrivateProject()) {
                                        $platforms[] = '<span class="badge bg-info me-1"><i class="fas fa-briefcase me-1"></i>Prywatny projekt</span>';
                                    }

                                    if (empty($platforms)) {
                                        return '<span class="text-muted">Nieużywane</span>';
                                    }

                                    return implode(' ', $platforms);
                                },
                            ],
                            [
                                'label' => 'Generowane przez AI',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->isAiGenerated()) {
                                        return '<span class="badge bg-warning text-dark"><i class="fas fa-robot me-1"></i>Tak</span>';
                                    }
                                    return '<span class="badge bg-secondary">Nie</span>';
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data utworzenia',
                                'value' => date('Y-m-d H:i:s', $model->created_at),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Data modyfikacji',
                                'value' => date('Y-m-d H:i:s', $model->updated_at),
                            ],
                            [
                                'attribute' => 'created_by',
                                'label' => 'Utworzone przez',
                                'value' => function ($model) {
                                    $user = \common\models\User::findOne($model->created_by);
                                    return $user ? $user->username : 'Nieznany użytkownik';
                                },
                            ],
                            [
                                'attribute' => 's3_path',
                                'label' => 'Ścieżka S3',
                                'format' => 'ntext',
                                'visible' => !empty($model->s3_path),
                                'value' => $model->s3_path ?: 'Nie przesłano do S3',
                            ],
                        ],
                    ])
                    ?>
                </div>
            </div>

            <!-- AI Information Card -->
<?php if ($model->isAiGenerated()): ?>
                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-robot me-2"></i>Informacje AI
                        </h5>
                    </div>
                    <div class="card-body">
    <?php if ($model->hasAiPrompt()): ?>
                            <div class="mb-3">
                                <h6><i class="fas fa-terminal me-2"></i>Prompt AI:</h6>
                                <div class="bg-light p-3 rounded">
                                    <code><?= Html::encode($model->ai_prompt) ?></code>
                                </div>
                            </div>
    <?php endif; ?>

    <?php if ($model->hasAiGeneratorUrl()): ?>
                            <div class="mb-3">
                                <h6><i class="fas fa-link me-2"></i>Generator:</h6>
                                <a href="<?= Html::encode($model->ai_generator_url) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>Otwórz w generatorze
                                </a>
                            </div>
    <?php endif; ?>

                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small><strong>Uwaga:</strong> To zdjęcie zostało wygenerowane przez sztuczną inteligencję. 
                                Należy przestrzegać odpowiednich regulacji dotyczących AI przy jego wykorzystaniu.</small>
                        </div>
                    </div>
                </div>
                    <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>Podgląd
                    </h5>
                </div>
                <div class="card-body text-center">
                            <?php $previewUrl = $model->getPreviewThumbnail(); ?>

                            <?php if ($previewUrl): ?>
                        <img src="<?= $previewUrl ?>" alt="<?= Html::encode($model->title) ?>" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px; cursor: pointer;"
                             data-bs-toggle="modal" data-bs-target="#imageModal">
<?php else: ?>
                        <div class="text-muted p-5">
                            <i class="fas fa-image fa-4x mb-3"></i>
                            <p>Podgląd niedostępny</p>
                            <small class="text-info">
                <?php if (\common\helpers\PathHelper::isFrontendMode()): ?>
                                    Tryb frontend - sprawdź ścieżki do miniatur
    <?php else: ?>
                                    Miniatury nie zostały wygenerowane
    <?php endif; ?>
                            </small>
                        </div>
<?php endif; ?>
                </div>
            </div>

            <!-- Copyright Info (if exists) -->
<?php $copyrightInfo = $model->getCopyrightInfo(); ?>
<?php if (!empty($copyrightInfo)): ?>
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-copyright me-2"></i>Prawa autorskie
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
    <?php if (isset($copyrightInfo['copyright'])): ?>
                                    <tr>
                                        <td class="fw-bold text-danger" style="width: 40%;">Copyright:</td>
                                        <td class="fw-bold"><?= Html::encode($copyrightInfo['copyright']) ?></td>
                                    </tr>
                                <?php endif; ?>
    <?php if (isset($copyrightInfo['artist'])): ?>
                                    <tr>
                                        <td class="fw-bold text-danger">Autor:</td>
                                        <td class="fw-bold"><?= Html::encode($copyrightInfo['artist']) ?></td>
                                    </tr>
    <?php endif; ?>
    <?php if (isset($copyrightInfo['description'])): ?>
                                    <tr>
                                        <td class="fw-bold text-muted">Opis:</td>
                                        <td><?= Html::encode($copyrightInfo['description']) ?></td>
                                    </tr>
    <?php endif; ?>
    <?php if (isset($copyrightInfo['user_comment'])): ?>
                                    <tr>
                                        <td class="fw-bold text-muted">Komentarz:</td>
                                        <td><?= Html::encode($copyrightInfo['user_comment']) ?></td>
                                    </tr>
    <?php endif; ?>
                            </table>
                        </div>

                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Uwaga:</strong> To zdjęcie zawiera informacje o prawach autorskich. 
                            Należy przestrzegać praw właściciela przed jakimkolwiek użyciem.
                        </div>
                    </div>
                </div>
                        <?php endif; ?>

            <!-- Categories -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder me-2"></i>Kategorie
                    </h5>
                </div>
                <div class="card-body">
<?php if (empty($categories)): ?>
                        <p class="text-muted mb-0">Brak przypisanych kategorii</p>
<?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
    <?php foreach ($categories as $category): ?>
                                <span class="badge bg-primary">
                                    <i class="fas fa-folder me-1"></i><?= Html::encode($category->name) ?>
                                </span>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Tagi
                    </h5>
                </div>
                <div class="card-body">
<?php if (empty($tags)): ?>
                        <p class="text-muted mb-0">Brak przypisanych tagów</p>
            <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
    <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-tag me-1"></i><?= Html::encode($tag->name) ?>
                                </span>
    <?php endforeach; ?>
                        </div>
<?php endif; ?>
                </div>
            </div>

            <!-- Other EXIF Data -->
                            <?php $exifData = $model->getFormattedExif(); ?>
                            <?php if (!empty($exifData)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-camera me-2"></i>Dane techniczne EXIF
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
    <?php foreach ($exifData as $key => $value): ?>
        <?php
        // Pomiń dane praw autorskich - są już wyświetlone wyżej
        if (in_array($key, ['Prawa autorskie', 'Autor', 'Komentarz autora', 'Opis obrazu', 'Unikatowy ID obrazu', 'Nazwa dokumentu'])) {
            continue;
        }
        ?>
                                    <tr>
                                        <td class="fw-bold text-muted" style="width: 40%;"><?= Html::encode($key) ?></td>
                                        <td><?= Html::encode($value) ?></td>
                                    </tr>
                <?php endforeach; ?>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Wyświetlane dane można konfigurować w 
    <?= Html::a('ustawieniach galerii', ['/settings/index'], ['class' => 'text-decoration-none']) ?>
                            </small>
                        </div>
                    </div>
                </div>
                        <?php endif; ?>

            <!-- Available Thumbnails -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>Dostępne miniatury
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($thumbnails)): ?>
                        <p class="text-muted mb-0">Brak dostępnych miniatur</p>
<?php else: ?>
                        <div class="list-group list-group-flush">
    <?php foreach ($thumbnails as $size => $url): ?>
                                <a href="<?= $url ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="fas fa-external-link-alt me-2 text-primary"></i>
                                    <span class="flex-grow-1"><?= ucfirst($size) ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-download"></i>
                                    </small>
                                </a>
    <?php endforeach; ?>
                        </div>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Html::encode($model->title) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
<?php if (isset($thumbnails['large'])): ?>
                    <img src="<?= $thumbnails['large'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid">
<?php elseif (isset($thumbnails['medium'])): ?>
                    <img src="<?= $thumbnails['medium'] ?>" alt="<?= Html::encode($model->title) ?>" class="img-fluid">
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function copySearchCode() {
        const searchCode = '<?= Html::encode($model->search_code) ?>';

        if (navigator.clipboard) {
            navigator.clipboard.writeText(searchCode).then(function () {
                // Show success message
                showToast('Kod został skopiowany do schowka!', 'success');
            }, function (err) {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(searchCode);
            });
        } else {
            fallbackCopyTextToClipboard(searchCode);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;

        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showToast('Kod został skopiowany do schowka!', 'success');
            } else {
                showToast('Nie udało się skopiować kodu', 'error');
            }
        } catch (err) {
            showToast('Nie udało się skopiować kodu', 'error');
        }

        document.body.removeChild(textArea);
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
</script>