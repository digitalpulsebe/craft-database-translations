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

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use digitalpulsebe\database_translations\models\SourceMessage;
use Exception;

class DatabaseTranslationsService extends Component
{
    public function languageIds(): array
    {
        return \Craft::$app->i18n->getSiteLocaleIds();
    }

    /**
     * @param SourceMessage[] $sourceMessages
     * @param string[] $languages
     * @return string filename of the migration
     */
    public function createMigration($sourceMessages, $languages = null): string
    {
        $name = 'm' . gmdate('ymd_His') . '_translations';

        $file = '@contentMigrations' . DIRECTORY_SEPARATOR . $name . '.php';

        $templateFile = Craft::getAlias('@digitalpulsebe/database_translations'. DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . '_migration' . DIRECTORY_SEPARATOR . 'migration.php.template');

        if ($templateFile === false) {
            throw new Exception('There was a problem getting the template file path');
        }

        $content = Craft::$app->getView()->renderFile($templateFile, [
            'sourceMessages' => $sourceMessages,
            'languages' => $languages,
            'namespace' => Craft::$app->getContentMigrator()->migrationNamespace,
            'className' => $name,
        ]);

        FileHelper::writeToFile(Craft::getAlias($file), $content);

        return $name;
    }
}
