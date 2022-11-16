<?php
/**
 * Database Translations plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\database_translations\services;

use craft\base\Component;

class DatabaseTranslationsService extends Component
{
    public function languageIds(): array
    {
        return \Craft::$app->i18n->getSiteLocaleIds();
    }
}
