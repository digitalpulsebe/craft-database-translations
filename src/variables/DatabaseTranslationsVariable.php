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
use digitalpulsebe\database_translations\models\SourceMessage;

use yii\db\Query;

/**
 * Database Translations defines the `databaseTranslations` global template variable.
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

    public function languageIds(): array
    {
        return DatabaseTranslations::$plugin->databaseTranslationsService->languageIds();
    }

    public function bestMatch($column, $fields)
    {
        // exact match
        foreach ($fields as $handle => $name) {
            if (strtolower($column) == strtolower($name)) {
                return $handle;
            }
        }

        // match inside name
        foreach ($fields as $handle => $name) {
            if (str_contains(strtolower($column),strtolower($name))||str_contains(strtolower($name),strtolower($column))) {
                return $handle;
            }
        }

        return null;
    }
}
