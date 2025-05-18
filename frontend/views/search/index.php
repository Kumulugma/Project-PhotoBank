<?php
/* @var $this yii\web\View */
/* @var $model frontend\models\SearchForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $categories common\models\Category[] */
/* @var $tags common\models\Tag[] */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\helpers\ArrayHelper;

$this->title = 'Wyszukiwanie zdjęć';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="search-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="search-box">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'options' => ['data-pjax' => true],
        ]); ?>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'keywords')->textInput(['placeholder' => 'Wpisz słowa kluczowe']) ?>
            </div>
            
            <div class="col-md-6">
                <?= $form->field($model, 'categories')->checkboxList(
                    ArrayHelper::map($categories, 'id', 'name'),
                    ['item' => function($index, $label, $name, $checked, $value) {
                        return '<div class="form-check">
                            <input type="checkbox" class="form-check-input" name="' . $name . '" value="' . $value . '" ' . ($checked ? 'checked' : '') . '>
                            <label class="form-check-label">' . $label . '</label>
                        </div>';
                    }]
                ) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="popular-tags mb-3">
                    <label>Popularne tagi:</label>
                    <div class="tag-cloud">
                        <?php foreach ($tags as $tag): ?>
                            <label class="tag-checkbox">
                                <input type="checkbox" name="SearchForm[tags][]" value="<?= $tag->id ?>" <?= is_array($model->tags) && in_array($tag->id, $model->tags) ? 'checked' : '' ?>>
                                <?= Html::encode($tag->name) ?> (<?= $tag->frequency ?>)
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <?= Html::submitButton('Szukaj', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Resetuj', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
    
    <?php if ($dataProvider !== null): ?>
        <h2>Wyniki wyszukiwania</h2>
        
        <?php if ($dataProvider->getCount() > 0): ?>
            <div class="photo-gallery">
                <?= ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemOptions' => ['class' => 'item'],
                    'summary' => 'Wyświetlanie <b>{begin}-{end}</b> z <b>{totalCount}</b> znalezionych zdjęć.',
                    'layout' => "{summary}\n<div class='row'>{items}</div>\n{pager}",
                    'itemView' => '/gallery/_photo',
                ]) ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>Nie znaleziono zdjęć spełniających podane kryteria.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>