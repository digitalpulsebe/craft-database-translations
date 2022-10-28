<?php

namespace digitalpulsebe\database_translations\components;

class DbMessageSource extends \yii\i18n\DbMessageSource
{
    public $sourceMessageTable = '{{%dp_translations_source_message}}';
    public $messageTable = '{{%dp_translations_message}}';
}