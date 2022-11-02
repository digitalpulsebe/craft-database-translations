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
        return SourceMessage::filter($filters)->with('messages');
    }

    public function categories(): array
    {
        return DatabaseTranslations::$plugin->settings->getCategories();
    }
}
