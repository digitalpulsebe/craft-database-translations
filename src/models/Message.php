<?php

namespace digitalpulsebe\database_translations\models;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $language
 * @property string $translation
 */
class Message extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dp_translations_message}}';
    }
}