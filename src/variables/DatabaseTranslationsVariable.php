<?php
/**
 * Database Translations plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\database_translations\variables;


use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\Message;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\db\Query;

class DatabaseTranslationsVariable
{
    public function sourceMessages($filters = []): \craft\db\ActiveQuery
    {
        $query = SourceMessage::find()->with('messages');

        if (is_array($filters)) {
            foreach ($filters as $filterKey => $filterValues) {
                if (!empty($filterValues)) {
                    if ($filterKey == 'category') {
//                    \Craft::dd(['category' =>  $filterValues]);
                        $query->andWhere(['category' =>  $filterValues]);
                    }
                    if ($filterKey == 'search') {
                        $query->andWhere(['like', 'message', $filterValues]);
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
                        $query->orderBy($filterValues);
                    }
                }
            }
        }

        return $query;
    }

    public function categories(): array
    {
        return DatabaseTranslations::$plugin->settings->getCategories();
    }
}
