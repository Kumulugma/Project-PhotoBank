<?php

use yii\helpers\Html;
\backend\assets\AppAsset::registerControllerCss($this, 'settings');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */

$this->title = 'Edytuj rozmiar: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rozmiary miniatur', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edytuj';
?>
<div class="thumbnail-size-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye me-2"></i>Zobacz rozmiar', ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-info'
            ]) ?>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista rozmiarów', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>