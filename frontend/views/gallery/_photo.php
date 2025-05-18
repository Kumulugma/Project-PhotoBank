<?php
/* @var $model common\models\Photo */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="col-md-4 col-sm-6 photo-item mb-4">
    <div class="card h-100">
        <a href="<?= Url::to(['view', 'id' => $model->id]) ?>">
            <img src="<?= $model->thumbnails['medium'] ?>" class="card-img-top" alt="<?= Html::encode($model->title) ?>" data-large="<?= $model->thumbnails['large'] ?>" data-title="<?= Html::encode($model->title) ?>">
        </a>
        <div class="card-body">
            <h5 class="card-title"><?= Html::encode($model->title) ?></h5>
            <?php if ($model->description): ?>
                <p class="card-text"><?= Html::encode(mb_substr($model->description, 0, 100)) . (mb_strlen($model->description) > 100 ? '...' : '') ?></p>
            <?php endif; ?>
            
            <?php if ($model->tags): ?>
                <div class="tag-list mt-2">
                    <?php foreach ($model->tags as $tag): ?>
                        <?= Html::a(Html::encode($tag->name), ['/gallery/tag', 'name' => $tag->name], ['class' => 'tag']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary mt-2">Zobacz więcej</a>
        </div>
        <div class="card-footer text-muted">
            <small>Dodano: <?= Yii::$app->formatter->asDate($model->created_at) ?></small>
        </div>
    </div>
</div>