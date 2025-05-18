<?php
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Galeria zdjęć';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gallery-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>Przeglądaj wszystkie publiczne zdjęcia w naszej galerii. Kliknij na zdjęcie, aby zobaczyć je w pełnej rozdzielczości.</p>
    
    <?php Pjax::begin(); ?>
    
    <div class="photo-gallery">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemOptions' => ['class' => 'item'],
            'summary' => 'Wyświetlanie <b>{begin}-{end}</b> z <b>{totalCount}</b> zdjęć.',
            'layout' => "{summary}\n<div class='row'>{items}</div>\n{pager}",
            'itemView' => '_photo',
        ]) ?>
    </div>
    
    <?php Pjax::end(); ?>
</div>