<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
\backend\assets\AppAsset::registerControllerCss($this, 'tags');
\backend\assets\AppAsset::registerComponentCss($this, 'tables');
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tags';
$this->params['breadcrumbs'][] = $this->title;

// Register CSS/JS for datepicker
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js');
?>
<div class="tag-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Tag', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'frequency',
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return date('Y-m-d H:i', $model->created_at);
                },
                'filter' => Html::activeTextInput($searchModel, 'created_at', [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'YYYY-MM-DD',
                    'autocomplete' => 'off'
                ]),
            ],
            [
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    return date('Y-m-d H:i', $model->updated_at);
                },
                'filter' => Html::activeTextInput($searchModel, 'updated_at', [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'YYYY-MM-DD',
                    'autocomplete' => 'off'
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<?php
$this->registerJs("
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
");
?>