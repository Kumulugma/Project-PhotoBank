<?php
namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\models\Tag;

/**
 * TagCloud widget displays a tag cloud.
 */
class TagCloud extends Widget
{
    /**
     * @var string the title of the widget
     */
    public $title = 'Popularne tagi';
    
    /**
     * @var int maximum number of tags to display
     */
    public $limit = 20;
    
    /**
     * @var string the widget container HTML tag
     */
    public $containerTag = 'div';
    
    /**
     * @var array HTML attributes for the container tag
     */
    public $containerOptions = ['class' => 'tag-cloud-widget'];
    
    /**
     * @var array HTML attributes for the tag cloud container
     */
    public $cloudOptions = ['class' => 'tag-cloud'];
    
    /**
     * @var array HTML attributes for the tags
     */
    public $tagOptions = ['class' => 'tag'];
    
    /**
     * @var int minimum font size (%) for tags
     */
    public $minFontSize = 80;
    
    /**
     * @var int maximum font size (%) for tags
     */
    public $maxFontSize = 180;
    
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $tags = Tag::find()
            ->where(['>', 'frequency', 0])
            ->orderBy(['frequency' => SORT_DESC])
            ->limit($this->limit)
            ->all();
        
        if (empty($tags)) {
            return '';
        }
        
        // Znajdowanie min i max częstotliwości
        $minFreq = PHP_INT_MAX;
        $maxFreq = 0;
        
        foreach ($tags as $tag) {
            $minFreq = min($minFreq, $tag->frequency);
            $maxFreq = max($maxFreq, $tag->frequency);
        }
        
        $html = Html::beginTag($this->containerTag, $this->containerOptions);
        
        if ($this->title) {
            $html .= Html::tag('h4', $this->title);
        }
        
        $html .= Html::beginTag('div', $this->cloudOptions);
        
        foreach ($tags as $tag) {
            // Skalowanie rozmiaru czcionki
            if ($maxFreq > $minFreq) {
                $fontSize = $this->minFontSize + ($tag->frequency - $minFreq) * ($this->maxFontSize - $this->minFontSize) / ($maxFreq - $minFreq);
            } else {
                $fontSize = ($this->minFontSize + $this->maxFontSize) / 2;
            }
            
            $options = $this->tagOptions;
            $options['style'] = 'font-size: ' . round($fontSize) . '%;';
            
            $html .= Html::a(
                Html::encode($tag->name),
                ['/gallery/tag', 'name' => $tag->name],
                $options
            );
        }
        
        $html .= Html::endTag('div');
        $html .= Html::endTag($this->containerTag);
        
        return $html;
    }
}