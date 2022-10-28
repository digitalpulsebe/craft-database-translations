<?php

namespace digitalpulsebe\database_translations\models;

use craft\db\ActiveRecord;
use craft\db\ActiveQuery;

/**
 * @property string $message
 * @property string $category
 */
class SourceMessage extends ActiveRecord
{
    protected $_lastUpdated = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dp_translations_source_message}}';
    }

    public function getMessages(): ActiveQuery
    {
        return $this->hasMany(Message::class, ['id' => 'id']);
    }

    public function getTranslation(string $language)
    {
        foreach ($this->messages as $message) {
            if ($message->language == $language) {
                return $message->translation;
            }
        }

        return null;
    }

    public function getMessage(string $language): ?Message
    {
        foreach ($this->messages as $message) {
            if ($message->language == $language) {
                return $message;
            }
        }

        return null;
    }

    public function getLastUpdated()
    {
        if ($this->_lastUpdated) {
            return $this->_lastUpdated;
        }

        $lastDate = null;

        foreach ($this->messages as $message) {
            if ($lastDate == null || $message->dateUpdated > $lastDate) {
                $lastDate = $message->dateUpdated;
            }
        }

        return $this->_lastUpdated = $lastDate;
    }

    public function rules()
    {
        return [
            ['message', 'string'] ,
            ['category', 'string'] ,
            [['message', 'category'], 'required']
        ];
    }
}