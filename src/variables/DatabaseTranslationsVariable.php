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
use digitalpulsebe\database_translations\variables\ManifestVariable as Manifest;

use yii\db\Query;

/**
 * Database Translations defines the `databaseTranslations` global template variable.
 *
 * @property Manifest   manifest
 *
 * @author    digitalpulsebe
 * @package   Database Translations
 * @since     1.0.0
 */

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
