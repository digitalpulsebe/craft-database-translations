<?php

namespace digitalpulsebe\database_translations\models;

use craft\db\ActiveRecord;
use craft\db\ActiveQuery;
use digitalpulsebe\database_translations\DatabaseTranslations;
use yii\db\Query;

/**
 * @property int $id
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

    public function updateTranslation($language, $value)
    {
        $emptyValues = [''];

        if (in_array($value, $emptyValues)) {
            // empty value
            $cleanValue = null;
        } else {
            // string fields
            $cleanValue = trim($value);
        }

        $translation = $this->getMessage($language);
        $originalValue = null;

        if (!$translation) {
            $translation = new Message();
            $translation->id = $this->id;
            $translation->language = $language;
        } else {
            $originalValue = $translation->translation;
        }

        if ($cleanValue != $originalValue) {
            $translation->translation = $cleanValue;
            return $translation->save();
        }

        return true;
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

    /**
     * @param array|null $filters
     * @return ActiveQuery
     */
    public static function filter($filters = []): ActiveQuery
    {
        $query = static::find();

        $orderIsSet = false;

        if (is_array($filters)) {
            foreach ($filters as $filterKey => $filterValues) {
                if (!empty($filterValues)) {

                    if ($filterKey == 'category') {
//                    \Craft::dd(['category' =>  $filterValues]);
                        $query->andWhere(['category' =>  $filterValues]);
                    }

                    if ($filterKey == 'search') {
                        $query->andWhere(['or',
                            ['like', 'message', $filterValues],
                            ['exists', (new Query())
                                ->from(Message::tableName())
                                ->where('dp_translations_message.id = dp_translations_source_message.id')
                                ->andWhere(['like', 'translation', $filterValues])
                            ]
                        ]);
                    }

                    if ($filterKey == 'missing') {
                        $query->andWhere(['not exists', (new Query())
                            ->from(Message::tableName())
                            ->where('dp_translations_message.id = dp_translations_source_message.id')
                            ->andWhere(['language' => $filterValues])
                            ->andWhere('translation is not null')
                            ->andWhere('translation <> \'\'')
                        ]);
                    }

                    if ($filterKey == 'order') {
                        $orderIsSet = true;
                        if (in_array($filterValues, DatabaseTranslations::$plugin->databaseTranslationsService->languageIds())) {
                            $query->join('LEFT JOIN', Message::tableName(), 'dp_translations_message.id = dp_translations_source_message.id AND dp_translations_message.language = \''.$filterValues.'\'');
                            $query->orderBy('dp_translations_message.translation');
                        } else {
                            $query->orderBy($filterValues);
                        }
                    }

                }
            }
        }

        if (!$orderIsSet) {
            $query->orderBy('message');
        }

        return $query;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['messages'] = $this->messages ?? [];
        return $data;
    }
}