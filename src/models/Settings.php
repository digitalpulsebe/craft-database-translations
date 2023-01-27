<?php

namespace digitalpulsebe\database_translations\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * override default name in control panel
     * @var string
     */
    public string $pluginName = 'Translations';

    /**
     * list of categories to process
     * @var array|string[][]
     */
    public array $categories = [['key' => 'site']];

    /**
     * enable to handle \yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION
     * and insert missing as a new record
     * @var bool
     */
    public bool $handleMissingTranslationEvent = false;

    /**
     * categories as array
     * @return array
     */
    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->categories as $row) {
            if (is_array($row) && isset($row['key'])) {
                $categories[] = $row['key'];
            }
        }
        return $categories;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['categories', 'required'],
            ['pluginName', 'string'],
            ['pluginName', 'required'],
        ];
    }
}
