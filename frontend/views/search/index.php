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

<div class="search-page">
    <div class="container">
        <!-- Search Header -->
        <div class="search-hero">
            <div class="search-hero-content">
                <h1 class="search-hero-title">
                    <i class="fas fa-search search-hero-icon"></i>
                    <?= Html::encode($this->title) ?>
                </h1>
                <p class="search-hero-subtitle">
                    Znajdź dokładnie to czego szukasz wśród <?= \common\models\Photo::find()->where(['status' => \common\models\Photo::STATUS_ACTIVE, 'is_public' => true])->count() ?> zdjęć
                </p>
            </div>
        </div>

        <!-- Search Form Section -->
        <div class="search-form-section">
            <div class="search-form-card">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'options' => [
                        'data-pjax' => true,
                        'class' => 'advanced-search-form',
                        'id' => 'searchForm'
                    ],
                ]); ?>
                
                <!-- Main Search Input -->
                <div class="search-input-section">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-input-icon"></i>
                        <?= $form->field($model, 'keywords')->textInput([
                            'placeholder' => 'Wpisz słowa kluczowe...',
                            'class' => 'search-main-input',
                            'autocomplete' => 'off',
                            'id' => 'keywords-input'
                        ])->label(false) ?>
                        <button type="submit" class="search-submit-btn">
                            <i class="fas fa-search"></i>
                            <span>Szukaj</span>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
                
                <!-- Advanced Filters Toggle -->
                <div class="filters-toggle-section">
                    <button type="button" 
                            class="filters-toggle-btn" 
                            id="filtersToggle"
                            aria-expanded="false"
                            aria-controls="advancedFilters">
                        <i class="fas fa-sliders-h"></i>
                        <span>Filtry zaawansowane</span>
                        <i class="fas fa-chevron-down toggle-icon"></i>
                    </button>
                </div>
                
                <!-- Advanced Filters -->
                <div class="advanced-filters" id="advancedFilters">
                    <div class="filters-grid">
                        <!-- Categories Filter -->
                        <div class="filter-group">
                            <div class="filter-group-header">
                                <i class="fas fa-folder filter-icon"></i>
                                <h3 class="filter-title">Kategorie</h3>
                                <span class="filter-count"><?= count($categories) ?></span>
                            </div>
                            <div class="filter-content">
                                <div class="categories-grid">
                                    <?php foreach ($categories as $category): ?>
                                        <?php 
                                        $isSelected = is_array($model->categories) && in_array($category->id, $model->categories);
                                        $photoCount = $category->getPhotos()->count();
                                        ?>
                                        <label class="category-checkbox <?= $isSelected ? 'selected' : '' ?>">
                                            <input type="checkbox" 
                                                   name="SearchForm[categories][]" 
                                                   value="<?= $category->id ?>" 
                                                   <?= $isSelected ? 'checked' : '' ?>>
                                            <div class="category-card">
                                                <div class="category-card-icon">
                                                    <i class="fas fa-folder"></i>
                                                </div>
                                                <div class="category-card-content">
                                                    <span class="category-name"><?= Html::encode($category->name) ?></span>
                                                    <span class="category-photos"><?= $photoCount ?> zdjęć</span>
                                                </div>
                                                <div class="category-card-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tags Filter -->
                        <div class="filter-group">
                            <div class="filter-group-header">
                                <i class="fas fa-tags filter-icon"></i>
                                <h3 class="filter-title">Popularne tagi</h3>
                                <span class="filter-count"><?= count($tags) ?></span>
                            </div>
                            <div class="filter-content">
                                <div class="tags-cloud">
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
                                            <span class="tag-bubble tag-<?= $size ?>">
                                                <i class="fas fa-tag"></i>
                                                <?= Html::encode($tag->name) ?>
                                                <span class="tag-frequency"><?= $frequency ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <?php if (!empty($model->keywords) || !empty($model->categories) || !empty($model->tags)): ?>
                    <div class="active-filters-section">
                        <div class="active-filters-header">
                            <h4 class="active-filters-title">
                                <i class="fas fa-filter"></i>
                                Aktywne filtry
                            </h4>
                            <?= Html::a(
                                '<i class="fas fa-times"></i> Wyczyść wszystkie',
                                ['index'],
                                ['class' => 'clear-all-filters']
                            ) ?>
                        </div>
                        <div class="active-filters-list">
                            <?php if (!empty($model->keywords)): ?>
                                <span class="active-filter keyword-filter">
                                    <i class="fas fa-search"></i>
                                    "<?= Html::encode($model->keywords) ?>"
                                    <button type="button" class="remove-filter" data-field="keywords">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($model->categories)): ?>
                                <?php foreach ($model->categories as $categoryId): ?>
                                    <?php $category = ArrayHelper::getValue(ArrayHelper::index($categories, 'id'), $categoryId); ?>
                                    <?php if ($category): ?>
                                        <span class="active-filter category-filter">
                                            <i class="fas fa-folder"></i>
                                            <?= Html::encode($category->name) ?>
                                            <button type="button" class="remove-filter" data-field="categories" data-value="<?= $categoryId ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($model->tags)): ?>
                                <?php foreach ($model->tags as $tagId): ?>
                                    <?php $tag = ArrayHelper::getValue(ArrayHelper::index($tags, 'id'), $tagId); ?>
                                    <?php if ($tag): ?>
                                        <span class="active-filter tag-filter">
                                            <i class="fas fa-tag"></i>
                                            <?= Html::encode($tag->name) ?>
                                            <button type="button" class="remove-filter" data-field="tags" data-value="<?= $tagId ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Form Actions -->
                <div class="search-form-actions">
                    <button type="submit" class="btn btn-primary btn-lg search-btn">
                        <i class="fas fa-search"></i>
                        Wyszukaj zdjęcia
                    </button>
                    
                    <?= Html::a(
                        '<i class="fas fa-undo"></i> Resetuj filtry',
                        ['index'],
                        ['class' => 'btn btn-outline-secondary btn-lg reset-btn']
                    ) ?>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- Search Results Section -->
        <div class="search-results-section">
            <?php if ($dataProvider !== null): ?>
                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <h2 class="results-title">
                            <i class="fas fa-images"></i>
                            Wyniki wyszukiwania
                        </h2>
                        
                        <?php if ($dataProvider->getCount() > 0): ?>
                            <div class="results-count">
                                Znaleziono <strong><?= $dataProvider->totalCount ?></strong> 
                                <?= $dataProvider->totalCount === 1 ? 'zdjęcie' : 'zdjęć' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($dataProvider->getCount() > 0): ?>
                        <div class="results-controls">
                            <div class="view-controls">
                                <button type="button" 
                                        class="view-toggle-btn active" 
                                        data-view="grid"
                                        aria-label="Widok siatki">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button" 
                                        class="view-toggle-btn" 
                                        data-view="list"
                                        aria-label="Widok listy">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            
                            <div class="sort-controls">
                                <select id="sortBy" class="sort-select">
                                    <option value="newest">Najnowsze</option>
                                    <option value="oldest">Najstarsze</option>
                                    <option value="name">Nazwa A-Z</option>
                                    <option value="name-desc">Nazwa Z-A</option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Results Content -->
                <?php if ($dataProvider->getCount() > 0): ?>
                    <div class="search-results-content">
                        <?= ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemOptions' => ['class' => 'photo-item-wrapper'],
                            'summary' => false,
                            'layout' => "<div class='photo-gallery' id='photoGallery'>{items}</div>\n{pager}",
                            'itemView' => function ($model, $key, $index, $widget) {
                                return $this->render('/gallery/_photo-card', [
                                    'model' => $model, 
                                    'index' => $index
                                ]);
                            },
                            'pager' => [
                                'class' => 'yii\widgets\LinkPager',
                                'options' => ['class' => 'pagination-wrapper'],
                                'linkOptions' => ['class' => 'pagination-link'],
                                'disabledListItemSubTagOptions' => ['class' => 'pagination-link disabled'],
                                'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                                'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                                'maxButtonCount' => 7,
                            ],
                        ]) ?>
                    </div>
                <?php else: ?>
                    <!-- No Results -->
                    <div class="no-results">
                        <div class="no-results-content">
                            <div class="no-results-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3 class="no-results-title">Brak wyników</h3>
                            <p class="no-results-text">
                                Nie znaleźliśmy zdjęć spełniających podane kryteria wyszukiwania.
                            </p>
                            <div class="no-results-suggestions">
                                <h4>Spróbuj:</h4>
                                <ul>
                                    <li>Użyć innych słów kluczowych</li>
                                    <li>Sprawdzić pisownię</li>
                                    <li>Wybrać mniej restrykcyjne filtry</li>
                                    <li><?= Html::a('Przeglądać wszystkie zdjęcia', ['/gallery/index']) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Initial State -->
                <div class="search-initial-state">
                    <div class="initial-state-content">
                        <div class="initial-state-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="initial-state-title">Zacznij wyszukiwanie</h3>
                        <p class="initial-state-text">
                            Wprowadź słowa kluczowe lub wybierz kategorie i tagi, aby znaleźć interesujące Cię zdjęcia.
                        </p>
                        
                        <!-- Quick Search -->
                        <div class="quick-search">
                            <h4 class="quick-search-title">Popularne wyszukiwania:</h4>
                            <div class="quick-search-tags">
                                <?php 
                                $popularTags = array_slice($tags, 0, 8);
                                foreach ($popularTags as $popularTag): 
                                ?>
                                    <button type="button" 
                                            class="quick-search-tag" 
                                            data-tag-id="<?= $popularTag->id ?>"
                                            data-tag-name="<?= Html::encode($popularTag->name) ?>">
                                        <i class="fas fa-tag"></i>
                                        <?= Html::encode($popularTag->name) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Search Page Styles */
.search-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 2rem 0;
}

/* Search Hero Section */
.search-hero {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 1px solid #e2e8f0;
    color: #1e293b;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: var(--radius);
    position: relative;
}

.search-hero-content {
    text-align: center;
}

.search-hero-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.search-hero-icon {
    font-size: 1.75rem;
    color: #64748b;
}

.search-hero-subtitle {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 0;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Search Form Section */
.search-form-section {
    margin-bottom: 3rem;
}

.search-form-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

/* Main Search Input */
.search-input-section {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: stretch;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: var(--radius);
    overflow: hidden;
    transition: all 0.2s ease;
}

.search-input-wrapper:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1rem;
    z-index: 2;
    pointer-events: none;
}

.form-group {
    flex: 1;
    margin: 0;
}

.search-main-input {
    width: 100%;
    padding: 1rem 1rem 1rem 2.75rem;
    border: none;
    background: transparent;
    font-size: 1rem;
    outline: none;
    color: #1f2937;
}

.search-main-input::placeholder {
    color: #9ca3af;
}

.hint-block,
.help-block {
    display: none;
}

.search-submit-btn {
    padding: 1rem 1.5rem;
    background: #6366f1;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 1px solid #e5e7eb;
}

.search-submit-btn:hover {
    background: #4f46e5;
}

.search-submit-btn:active {
    background: #4338ca;
}

/* Filters Toggle */
.filters-toggle-section {
    padding: 1rem 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.filters-toggle-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f1f5f9;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.3s ease;
    color: #475569;
    font-weight: 500;
}

.filters-toggle-btn:hover {
    background: #e2e8f0;
    color: var(--primary-color);
}

.filters-toggle-btn[aria-expanded="true"] {
    background: var(--primary-color);
    color: white;
}

.toggle-icon {
    transition: transform 0.3s ease;
}

.filters-toggle-btn[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

/* Advanced Filters */
.advanced-filters {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.advanced-filters.expanded {
    max-height: 1000px;
}

.filters-grid {
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.filter-group {
    background: #f8fafc;
    border-radius: var(--radius);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.filter-group-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.filter-icon {
    color: var(--primary-color);
    font-size: 1.125rem;
}

.filter-title {
    flex: 1;
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}

.filter-count {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
}

.filter-content {
    padding: 1.5rem;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.category-checkbox {
    cursor: pointer;
    display: block;
}

.category-checkbox input {
    display: none;
}

.category-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: var(--radius);
    padding: 1rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
}

.category-checkbox.selected .category-card {
    border-color: var(--primary-color);
    background: #f0f4ff;
}

.category-card-icon {
    color: var(--primary-color);
    font-size: 1.25rem;
}

.category-card-content {
    flex: 1;
}

.category-name {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.category-photos {
    color: #64748b;
    font-size: 0.875rem;
}

.category-card-check {
    color: var(--primary-color);
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.category-checkbox.selected .category-card-check {
    opacity: 1;
    transform: scale(1);
}

/* Tags Cloud */
.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.tag-checkbox {
    cursor: pointer;
}

.tag-checkbox input {
    display: none;
}

.tag-bubble {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
    color: #475569;
    transition: all 0.3s ease;
}

.tag-bubble:hover {
    border-color: var(--primary-color);
    background: #f0f4ff;
    color: var(--primary-color);
    transform: translateY(-1px);
}

.tag-checkbox.selected .tag-bubble {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.tag-frequency {
    background: rgba(0, 0, 0, 0.1);
    padding: 0.125rem 0.375rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    margin-left: 0.25rem;
}

.tag-checkbox.selected .tag-frequency {
    background: rgba(255, 255, 255, 0.2);
}

/* Tag sizes */
.tag-small { font-size: 0.75rem; }
.tag-medium { font-size: 0.875rem; }
.tag-large { font-size: 1rem; font-weight: 600; }

/* Active Filters */
.active-filters-section {
    padding: 1.5rem 2rem;
    background: #fefce8;
    border-top: 1px solid #e2e8f0;
}

.active-filters-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
}

.active-filters-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #92400e;
}

.clear-all-filters {
    color: #dc2626;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    transition: all 0.3s ease;
}

.clear-all-filters:hover {
    background: #fee2e2;
    text-decoration: none;
}

.active-filters-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.active-filter {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid #d97706;
    border-radius: var(--radius);
    font-size: 0.875rem;
    color: #92400e;
}

.remove-filter {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    padding: 0.125rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
}

.remove-filter:hover {
    background: #fee2e2;
}

/* Form Actions */
.search-form-actions {
    padding: 2rem;
    background: #f8fafc;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.search-btn, .reset-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: var(--radius);
    transition: all 0.3s ease;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.reset-btn:hover {
    background: #f1f5f9;
    border-color: #64748b;
    color: #475569;
    transform: translateY(-2px);
}

/* Results Section */
.search-results-section {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.results-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.results-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.results-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
}

.results-count {
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: white;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
}

.results-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.view-controls {
    display: flex;
    background: #f1f5f9;
    border-radius: var(--radius);
    padding: 0.25rem;
}

.view-toggle-btn {
    padding: 0.5rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    color: #64748b;
    transition: all 0.3s ease;
}

.view-toggle-btn.active {
    background: white;
    color: var(--primary-color);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.sort-select {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: var(--radius);
    background: white;
    font-size: 0.875rem;
    color: #475569;
    cursor: pointer;
}

/* Search Results Content */
.search-results-content {
    padding: 2rem;
}

.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.photo-gallery.list-view {
    grid-template-columns: 1fr;
    gap: 1rem;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination-link {
    padding: 0.75rem 1rem;
    margin: 0 0.25rem;
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
    text-decoration: none;
    border-radius: var(--radius);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
}

.pagination-link:hover:not(.disabled) {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transform: translateY(-1px);
    text-decoration: none;
}

.pagination-link.disabled {
    color: #cbd5e1;
    cursor: not-allowed;
}

.pagination-link.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

/* No Results */
.no-results {
    padding: 3rem 2rem;
    text-align: center;
}

.no-results-content {
    max-width: 500px;
    margin: 0 auto;
}

.no-results-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}

.no-results-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 1rem;
}

.no-results-text {
    color: #64748b;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.no-results-suggestions {
    background: #f8fafc;
    border-radius: var(--radius);
    padding: 1.5rem;
    text-align: left;
}

.no-results-suggestions h4 {
    margin-bottom: 1rem;
    color: #475569;
}

.no-results-suggestions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.no-results-suggestions li {
    padding: 0.5rem 0;
    color: #64748b;
    position: relative;
    padding-left: 1.5rem;
}

.no-results-suggestions li:before {
    content: '•';
    color: var(--primary-color);
    font-weight: bold;
    position: absolute;
    left: 0;
}

.no-results-suggestions a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.no-results-suggestions a:hover {
    text-decoration: underline;
}

/* Initial State */
.search-initial-state {
    padding: 3rem 2rem;
    text-align: center;
}

.initial-state-content {
    max-width: 600px;
    margin: 0 auto;
}

.initial-state-icon {
    font-size: 5rem;
    color: #e2e8f0;
    margin-bottom: 2rem;
}

.initial-state-title {
    font-size: 2rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 1rem;
}

.initial-state-text {
    color: #64748b;
    margin-bottom: 2rem;
    line-height: 1.6;
    font-size: 1.125rem;
}

/* Quick Search */
.quick-search {
    margin-top: 2rem;
}

.quick-search-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 1rem;
}

.quick-search-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
}

.quick-search-tag {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: var(--radius-full);
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.quick-search-tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-hero-title {
        font-size: 1.75rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .search-hero-subtitle {
        font-size: 0.9rem;
    }
    
    .search-form-card {
        margin: 0 1rem;
    }
    
    .search-input-wrapper {
        flex-direction: column;
    }
    
    .search-main-input {
        padding: 1rem;
    }
    
    .search-input-icon {
        display: none;
    }
    
    .search-submit-btn {
        margin: 0;
        justify-content: center;
        border-left: none;
        border-top: 1px solid #e5e7eb;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .results-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .results-controls {
        justify-content: space-between;
    }
    
    .search-form-actions {
        flex-direction: column;
        padding: 1.5rem;
    }
    
    .search-btn, .reset-btn {
        width: 100%;
        justify-content: center;
    }
    
    .active-filters-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
    }
    
    .photo-gallery {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }
    
    .quick-search-tags {
        gap: 0.5rem;
    }
    
    .quick-search-tag {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .search-page {
        padding: 1rem 0;
    }
    
    .search-hero {
        margin: 0 1rem 1rem;
        padding: 2rem 1rem;
    }
    
    .search-hero-title {
        font-size: 1.75rem;
    }
    
    .photo-gallery {
        grid-template-columns: 1fr;
        padding: 0.5rem;
    }
    
    .pagination-link {
        padding: 0.5rem;
        min-width: 40px;
    }
}

/* Loading States */
.search-form.loading .search-submit-btn {
    position: relative;
    color: transparent;
}

.search-form.loading .search-submit-btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Focus States */
.category-checkbox:focus-within .category-card,
.tag-checkbox:focus-within .tag-bubble {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .search-form-card,
    .search-results-section {
        border: 2px solid var(--text-primary);
    }
    
    .category-card,
    .tag-bubble {
        border-width: 2px;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .search-submit-btn:hover,
    .category-card:hover,
    .tag-bubble:hover,
    .quick-search-tag:hover {
        transform: none;
    }
    
    .search-form.loading .search-submit-btn::after {
        animation: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const searchForm = document.getElementById('searchForm');
    const filtersToggle = document.getElementById('filtersToggle');
    const advancedFilters = document.getElementById('advancedFilters');
    const keywordsInput = document.getElementById('keywords-input');
    
    // Advanced filters toggle
    if (filtersToggle && advancedFilters) {
        filtersToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            this.setAttribute('aria-expanded', newState);
            advancedFilters.classList.toggle('expanded', newState);
            
            // Animate scroll to filters
            if (newState) {
                setTimeout(() => {
                    advancedFilters.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 300);
            }
        });
        
        // Auto-expand filters if any are selected
        const hasActiveFilters = document.querySelector('.active-filters-section');
        if (hasActiveFilters) {
            filtersToggle.setAttribute('aria-expanded', 'true');
            advancedFilters.classList.add('expanded');
        }
    }
    
    // Quick search tags
    const quickSearchTags = document.querySelectorAll('.quick-search-tag');
    quickSearchTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const tagId = this.dataset.tagId;
            const tagName = this.dataset.tagName;
            
            // Find the corresponding checkbox
            const checkbox = document.querySelector(`input[name="SearchForm[tags][]"][value="${tagId}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.closest('.tag-checkbox').classList.add('selected');
                
                // Expand filters and scroll to tags
                if (filtersToggle && advancedFilters) {
                    filtersToggle.setAttribute('aria-expanded', 'true');
                    advancedFilters.classList.add('expanded');
                    
                    setTimeout(() => {
                        checkbox.closest('.filter-group').scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }, 300);
                }
            }
        });
    });
    
    // Filter selection handlers
    const categoryCheckboxes = document.querySelectorAll('input[name="SearchForm[categories][]"]');
    const tagCheckboxes = document.querySelectorAll('input[name="SearchForm[tags][]"]');
    
    // Category selection
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('.category-checkbox');
            label.classList.toggle('selected', this.checked);
            
            // Auto-submit if enabled
            if (this.checked && window.autoSubmitSearch) {
                setTimeout(() => searchForm.submit(), 500);
            }
        });
    });
    
    // Tag selection
    tagCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('.tag-checkbox');
            label.classList.toggle('selected', this.checked);
            
            // Auto-submit if enabled
            if (this.checked && window.autoSubmitSearch) {
                setTimeout(() => searchForm.submit(), 500);
            }
        });
    });
    
    // Remove filter functionality
    const removeFilterButtons = document.querySelectorAll('.remove-filter');
    removeFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const field = this.dataset.field;
            const value = this.dataset.value;
            
            if (field === 'keywords') {
                keywordsInput.value = '';
            } else if (field === 'categories' && value) {
                const checkbox = document.querySelector(`input[name="SearchForm[categories][]"][value="${value}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('.category-checkbox').classList.remove('selected');
                }
            } else if (field === 'tags' && value) {
                const checkbox = document.querySelector(`input[name="SearchForm[tags][]"][value="${value}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('.tag-checkbox').classList.remove('selected');
                }
            }
            
            // Submit form after removing filter
            setTimeout(() => searchForm.submit(), 100);
        });
    });
    
    // View toggle functionality
    const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
    const photoGallery = document.getElementById('photoGallery');
    
    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update button states
            viewToggleButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update gallery view
            if (photoGallery) {
                photoGallery.classList.toggle('list-view', view === 'list');
            }
            
            // Save preference
            localStorage.setItem('searchViewMode', view);
        });
    });
    
    // Restore view preference
    const savedViewMode = localStorage.getItem('searchViewMode');
    if (savedViewMode) {
        const button = document.querySelector(`[data-view="${savedViewMode}"]`);
        if (button) {
            button.click();
        }
    }
    
    // Sort functionality
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            
            // Add sort parameter to form
            const sortInput = document.createElement('input');
            sortInput.type = 'hidden';
            sortInput.name = 'sort';
            sortInput.value = sortValue;
            
            // Remove existing sort input
            const existingSortInput = searchForm.querySelector('input[name="sort"]');
            if (existingSortInput) {
                existingSortInput.remove();
            }
            
            searchForm.appendChild(sortInput);
            searchForm.submit();
        });
    }
    
    // Search suggestions (basic implementation)
    let searchTimeout;
    if (keywordsInput) {
        keywordsInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // Here you could implement AJAX search suggestions
                    console.log('Search suggestions for:', query);
                }, 300);
            }
        });
    }
    
    // Form submission with loading state
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            this.classList.add('loading');
            
            // Re-enable after 10 seconds as failsafe
            setTimeout(() => {
                this.classList.remove('loading');
            }, 10000);
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            keywordsInput.focus();
        }
        
        // Escape to clear search and close filters
        if (e.key === 'Escape') {
            if (keywordsInput === document.activeElement) {
                keywordsInput.blur();
            } else if (advancedFilters.classList.contains('expanded')) {
                filtersToggle.click();
            }
        }
    });
    
    // Smooth animations for filter cards
    const filterCards = document.querySelectorAll('.category-card, .tag-bubble');
    filterCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.closest('.selected')) {
                this.style.transform = 'translateY(0)';
            }
        });
    });
    
    // Initialize tooltips for truncated text
    const initializeTooltips = () => {
        const textElements = document.querySelectorAll('.category-name, .tag-bubble');
        textElements.forEach(element => {
            if (element.scrollWidth > element.clientWidth) {
                element.title = element.textContent;
            }
        });
    };
    
    initializeTooltips();
    
    console.log('Search page initialized successfully');
});

// Global function for auto-submit (can be enabled/disabled)
window.autoSubmitSearch = false;

// Function to toggle auto-submit
window.toggleAutoSubmit = function(enabled) {
    window.autoSubmitSearch = enabled;
    const button = document.querySelector('.auto-submit-toggle');
    if (button) {
        button.textContent = enabled ? 'Wyłącz automatyczne wyszukiwanie' : 'Włącz automatyczne wyszukiwanie';
        button.classList.toggle('active', enabled);
    }
};
</script>