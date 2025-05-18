<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */

$this->title = 'Dodaj rozmiar miniatur';
$this->params['breadcrumbs'][] = ['label' => 'Rozmiary miniatur', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="thumbnail-size-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista rozmiarów', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>