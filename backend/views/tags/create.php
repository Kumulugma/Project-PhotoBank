<?php

use yii\helpers\Html;

\backend\assets\AppAsset::registerControllerCss($this, 'tags');

$this->title = 'Dodaj nowy tag';
$this->params['breadcrumbs'][] = ['label' => 'Tagi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tag-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>