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

<div class="container">
    <div class="page-content">
        <!-- Search Header -->
        <header class="search-header text-center mb-5">
            <h1 class="search-title">
                <i class="fas fa-search" aria-hidden="true"></i>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="search-subtitle">
                Znajdź dokładnie to czego szukasz wśród <?= \common\models\Photo::find()->where(['status' => \common\models\Photo::STATUS_ACTIVE, 'is_public' => true])->count() ?> zdjęć
            </p>
        </header>

        <!-- Search Form -->
        <section class="search-form-section" aria-label="Formularz wyszukiwania">
            <div class="search-box">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'options' => [
                        'data-pjax' => true,
                        'class' => 'search-form',
                        'id' => 'searchForm'
                    ],
                ]); ?>
                
                <!-- Keywords Search -->
                <div class="search-field-group">
                    <h3 class="field-group-title">
                        <i class="fas fa-keyboard" aria-hidden="true"></i>
                        Wyszukiwanie tekstowe
                    </h3>
                    <div class="form-field">
                        <?= $form->field($model, 'keywords')->textInput([
                            'placeholder' => 'Wpisz słowa kluczowe...',
                            'class' => 'form-control search-input',
                            'autocomplete' => 'off',
                            'id' => 'keywords-input'
                        ])->label(false) ?>
                        <div class="search-suggestions" id="searchSuggestions"></div>
                    </div>
                </div>
                
                <!-- Categories and Tags -->
                <div class="search-filters-grid">
                    <!-- Categories -->
                    <div class="search-field-group">
                        <h3 class="field-group-title">
                            <i class="fas fa-folder" aria-hidden="true"></i>
                            Kategorie
                            <span class="category-count">(<?= count($categories) ?>)</span>
                        </h3>
                        <div class="categories-container">
                            <?php foreach ($categories as $category): ?>
                                <?php 
                                $isSelected = is_array($model->categories) && in_array($category->id, $model->categories);
                                $photoCount = $category->getPhotos()->count();
                                ?>
                                <label class="category-item <?= $isSelected ? 'selected' : '' ?>">
                                    <input type="checkbox" 
                                           name="SearchForm[categories][]" 
                                           value="<?= $category->id ?>" 
                                           <?= $isSelected ? 'checked' : '' ?>
                                           class="category-checkbox">
                                    <div class="category-content">
                                        <div class="category-icon">
                                            <i class="fas fa-folder" aria-hidden="true"></i>
                                        </div>
                                        <div class="category-info">
                                            <span class="category-name"><?= Html::encode($category->name) ?></span>
                                            <span class="category-count"><?= $photoCount ?> zdjęć</span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="search-field-group">
                        <h3 class="field-group-title">
                            <i class="fas fa-tags" aria-hidden="true"></i>
                            Popularne tagi
                            <span class="tag-count">(<?= count($tags) ?>)</span>
                        </h3>
                        <div class="tags-container">
                            <div class="tag-cloud search-tags">
                                <?php foreach ($tags as $tag): ?>
                                    <?php 
                                    $isSelected = is_array($model->tags) && in_array($tag->id, $model->tags);
                                    $frequency = $tag->frequency ?? 0;
                                    $size = 'normal';
                                    
                                    if ($frequency > 50) $size = 'large';
                                    elseif ($frequency > 20) $size = 'medium';
                                    elseif ($frequency < 5) $size = 'small';
                                    ?>
                                    <label class="tag-checkbox <?= $isSelected ? 'selected' : '' ?>">
                                        <input type="checkbox" 
                                               name="SearchForm[tags][]" 
                                               value="<?= $tag->id ?>" 
                                               <?= $isSelected ? 'checked' : '' ?>>
                                        <span class="tag <?= $size ?>">
                                            <i class="fas fa-tag" aria-hidden="true"></i>
                                            <?= Html::encode($tag->name) ?> 
                                            <span class="tag-frequency">(<?= $frequency ?>)</span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Actions -->
                <div class="search-actions">
                    <div class="search-buttons">
                        <?= Html::submitButton(
                            '<i class="fas fa-search" aria-hidden="true"></i> Szukaj',
                            [
                                'class' => 'btn btn-primary btn-lg search-submit',
                                'id' => 'searchSubmit'
                            ]
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-undo" aria-hidden="true"></i> Resetuj',
                            ['index'],
                            ['class' => 'btn btn-outline-secondary btn-lg']
                        ) ?>
                    </div>
                    
                    <div class="search-stats" id="searchStats">
                        <?php if (!empty($model->keywords) || !empty($model->categories) || !empty($model->tags)): ?>
                            <div class="active-filters">
                                <span class="filters-label">Aktywne filtry:</span>
                                
                                <?php if (!empty($model->keywords)): ?>
                                    <span class="filter-tag">
                                        <i class="fas fa-keyboard" aria-hidden="true"></i>
                                        "<?= Html::encode($model->keywords) ?>"
                                        <button type="button" class="remove-filter" data-field="keywords">×</button>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($model->categories)): ?>
                                    <?php foreach ($model->categories as $categoryId): ?>
                                        <?php $category = ArrayHelper::getValue(ArrayHelper::index($categories, 'id'), $categoryId); ?>
                                        <?php if ($category): ?>
                                            <span class="filter-tag category-filter">
                                                <i class="fas fa-folder" aria-hidden="true"></i>
                                                <?= Html::encode($category->name) ?>
                                                <button type="button" class="remove-filter" data-field="categories" data-value="<?= $categoryId ?>">×</button>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($model->tags)): ?>
                                    <?php foreach ($model->tags as $tagId): ?>
                                        <?php $tag = ArrayHelper::getValue(ArrayHelper::index($tags, 'id'), $tagId); ?>
                                        <?php if ($tag): ?>
                                            <span class="filter-tag tag-filter">
                                                <i class="fas fa-tag" aria-hidden="true"></i>
                                                <?= Html::encode($tag->name) ?>
                                                <button type="button" class="remove-filter" data-field="tags" data-value="<?= $tagId ?>">×</button>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </section>

        <!-- Search Results -->
        <section class="search-results-section" aria-label="Wyniki wyszukiwania">
            <?php if ($dataProvider !== null): ?>
                <div class="results-header">
                    <h2 class="results-title">
                        <i class="fas fa-images" aria-hidden="true"></i>
                        Wyniki wyszukiwania
                    </h2>
                    
                    <?php if ($dataProvider->getCount() > 0): ?>
                        <div class="results-controls">
                            <div class="results-count">
                                Znaleziono <strong><?= $dataProvider->totalCount ?></strong> 
                                <?= $dataProvider->totalCount === 1 ? 'zdjęcie' : 'zdjęć' ?>
                            </div>
                            
                            <div class="view-controls">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary" 
                                        id="toggleViewMode"
                                        aria-label="Przełącz tryb wyświetlania">
                                    <i class="fas fa-th" aria-hidden="true"></i>
                                    <span class="view-mode-text">Siatka</span>
                                </button>
                                
                                <div class="sort-controls">
                                    <label for="sortBy" class="sr-only">Sortuj według</label>
                                    <select id="sortBy" class="form-control form-control-sm">
                                        <option value="newest">Najnowsze</option>
                                        <option value="oldest">Najstarsze</option>
                                        <option value="name">Nazwa A-Z</option>
                                        <option value="name-desc">Nazwa Z-A</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($dataProvider->getCount() > 0): ?>
                    <div class="photo-gallery search-results">
                        <?= ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemOptions' => ['class' => 'photo-item-wrapper'],
                            'summary' => false,
                            'layout' => "<div class='gallery-grid' id='galleryGrid'>{items}</div>\n{pager}",
                            'itemView' => function ($model, $key, $index, $widget) {
                                return $this->render('/gallery/_photo-card', [
                                    'model' => $model, 
                                    'index' => $index
                                ]);
                            },
                            'pager' => [
                                'class' => 'yii\widgets\LinkPager',
                                'options' => ['class' => 'pagination-wrapper text-center mt-5'],
                                'linkOptions' => ['class' => 'btn btn-outline-primary'],
                                'disabledListItemSubTagOptions' => ['class' => 'btn btn-outline-secondary disabled'],
                                'prevPageLabel' => '<i class="fas fa-chevron-left" aria-hidden="true"></i> Poprzednia',
                                'nextPageLabel' => 'Następna <i class="fas fa-chevron-right" aria-hidden="true"></i>',
                                'maxButtonCount' => 5,
                            ],
                        ]) ?>
                    </div>
                <?php else: ?>
                    <!-- No Results -->
                    <div class="no-results text-center">
                        <div class="no-results-icon">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </div>
                        <h3 class="no-results-title">Brak wyników</h3>
                        <p class="no-results-message">
                            Nie znaleziono zdjęć spełniających podane kryteria wyszukiwania.
                        </p>
                        <div class="search-suggestions-list">
                            <h4>Spróbuj:</h4>
                            <ul>
                                <li>Użyć innych słów kluczowych</li>
                                <li>Sprawdzić pisownię</li>
                                <li>Wybrać mniej restrykcyjne filtry</li>
                                <li>Przeglądać <a href="<?= \yii\helpers\Url::to(['/gallery/index']) ?>">wszystkie zdjęcia</a></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Initial State -->
                <div class="search-initial-state text-center">
                    <div class="initial-state-icon">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </div>
                    <h3 class="initial-state-title">Rozpocznij wyszukiwanie</h3>
                    <p class="initial-state-message">
                        Wprowadź słowa kluczowe lub wybierz kategorie i tagi, aby znaleźć interesujące Cię zdjęcia.
                    </p>
                    
                    <!-- Quick Search Suggestions -->
                    <div class="quick-search-suggestions">
                        <h4>Popularne wyszukiwania:</h4>
                        <div class="suggestion-tags">
                            <?php 
                            $popularTags = array_slice($tags, 0, 8);
                            foreach ($popularTags as $popularTag): 
                            ?>
                                <button type="button" 
                                        class="suggestion-tag" 
                                        data-tag-id="<?= $popularTag->id ?>"
                                        data-tag-name="<?= Html::encode($popularTag->name) ?>">
                                    <i class="fas fa-tag" aria-hidden="true"></i>
                                    <?= Html::encode($popularTag->name) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>