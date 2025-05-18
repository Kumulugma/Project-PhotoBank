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

    /**
     * Custom validation messages in Polish
     */
    public function attributeMessages()
    {
        return [
            'keywords' => [
                'required' => 'Słowa kluczowe są wymagane.',
                'string' => 'Słowa kluczowe muszą być tekstem.',
                'max' => 'Słowa kluczowe nie mogą być dłuższe niż 255 znaków.',
            ],
            'categories' => [
                'required' => 'Kategorie są wymagane.',
            ],
            'tags' => [
                'required' => 'Tagi są wymagane.',
            ],
        ];
    }

    /**
     * Override getAttributeHint to provide Polish hints
     */
    public function getAttributeHint($attribute)
    {
        $hints = [
            'keywords' => 'Wprowadź słowa kluczowe do wyszukania zdjęć',
            'categories' => 'Wybierz kategorie zdjęć',
            'tags' => 'Wybierz tagi związane ze zdjęciami',
        ];
        
        return isset($hints[$attribute]) ? $hints[$attribute] : parent::getAttributeHint($attribute);
    }
}