<?php

use yii\helpers\Html;
\backend\assets\AppAsset::registerControllerCss($this, 'categories');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
/* @var $this yii\web\View */
/* @var $model common\models\Category */

$this->title = 'Edytuj kategorię: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Kategorie', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edytuj';
?>
<div class="category-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye me-2"></i>Zobacz kategorię', ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-info'
            ]) ?>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista kategorii', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>