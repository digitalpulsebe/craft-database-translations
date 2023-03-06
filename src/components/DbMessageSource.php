<?php

namespace digitalpulsebe\database_translations\components;

use digitalpulsebe\database_translations\DatabaseTranslations;

class DbMessageSource extends \yii\i18n\DbMessageSource
{
    public $sourceMessageTable = '{{%dp_translations_source_message}}';
    public $messageTable = '{{%dp_translations_message}}';

    /**
     * @inheritDoc
     */
    protected function loadMessages($category, $language): array
    {
        $mapping = DatabaseTranslations::$plugin->settings->getDestinationMapping();

        if (isset($mapping[$language])) {
            $language = $mapping[$language];
        }

        return parent::loadMessages($category, $language);
    }
}