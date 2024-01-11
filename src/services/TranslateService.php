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
use digitalpulsebe\craftmultitranslator\MultiTranslator;

class TranslateService extends Component
{
    public function isAvailable()
    {
        if( ! class_exists("digitalpulsebe\craftmultitranslator\MultiTranslator")) {
            return false;
        }

        return !empty(MultiTranslator::getInstance());
    }

    public function translate(string $sourceLocale = null, string $targetLocale = null, string $text = null): ?string
    {
        return MultiTranslator::getInstance()->translate->translateText($sourceLocale, $targetLocale, $text);
    }
}
