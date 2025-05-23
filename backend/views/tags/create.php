<?php

use yii\helpers\Html;
\backend\assets\AppAsset::registerControllerCss($this, 'tags');
\backend\assets\AppAsset::registerComponentCss($this, 'forms');
/* @var $this yii\web\View */
/* @var $model common\models\Tag */

$this->title = 'Dodaj tag';
$this->params['breadcrumbs'][] = ['label' => 'Tagi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-list me-2"></i>Lista tagów', ['index'], [
                'class' => 'btn btn-outline-secondary'
            ]) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>