<?php
namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * SearchForm is the model behind the search form.
 */
class SearchForm extends Model
{
    public $keywords;
    public $categories;
    public $tags;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keywords'], 'string', 'max' => 255],
            [['categories', 'tags'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'keywords' => 'Słowa kluczowe',
            'categories' => 'Kategorie',
            'tags' => 'Tagi',
        ];
    }
}