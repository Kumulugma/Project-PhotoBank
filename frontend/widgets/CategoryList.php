<?php
namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\models\Category;

/**
 * CategoryList widget displays a list of categories.
 */
class CategoryList extends Widget
{
    /**
     * @var string the title of the widget
     */
    public $title = 'Kategorie';
    
    /**
     * @var bool whether to show photo count for each category
     */
    public $showCount = true;
    
    /**
     * @var int maximum number of categories to display (0 = all)
     */
    public $limit = 0;
    
    /**
     * @var string the widget container HTML tag
     */
    public $containerTag = 'div';
    
    /**
     * @var array HTML attributes for the container tag
     */
    public $containerOptions = ['class' => 'category-list-widget'];
    
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $query = Category::find()->orderBy(['name' => SORT_ASC]);
        
        if ($this->limit > 0) {
            $query->limit($this->limit);
        }
        
        $categories = $query->all();
        
        if (empty($categories)) {
            return '';
        }
        
        $html = Html::beginTag($this->containerTag, $this->containerOptions);
        
        if ($this->title) {
            $html .= Html::tag('h4', $this->title);
        }
        
        $html .= '<ul class="list-unstyled">';
        
        foreach ($categories as $category) {
            $html .= '<li>';
            $html .= Html::a(
                Html::encode($category->name) . 
                ($this->showCount ? ' <span class="badge bg-secondary">' . $category->getPhotoCount(true, true) . '</span>' : ''),
                ['/gallery/category', 'slug' => $category->slug]
            );
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= Html::endTag($this->containerTag);
        
        return $html;
    }
}