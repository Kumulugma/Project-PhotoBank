<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ThumbnailSize */

$this->title = 'Create Thumbnail Size';
$this->params['breadcrumbs'][] = ['label' => 'Thumbnail Sizes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="thumbnail-size-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>