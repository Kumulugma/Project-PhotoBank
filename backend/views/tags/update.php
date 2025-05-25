<?php

use yii\helpers\Html;

\backend\assets\AppAsset::registerControllerCss($this, 'tags');

$this->title = 'Edytuj tag: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tagi', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edytuj';
?>

<div class="tag-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>