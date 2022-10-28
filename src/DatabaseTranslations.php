<?php
/**
 * Database Translations plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\database_translations;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\App;
use craft\i18n\I18N;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use digitalpulsebe\database_translations\components\DbMessageSource;
use digitalpulsebe\database_translations\models\Settings;
use digitalpulsebe\database_translations\services\DatabaseTranslationsService;
use digitalpulsebe\database_translations\variables\DatabaseTranslationsVariable;
use yii\base\Event;

/**
 *
 * @author    Digital Pulse
 * @package   DatabaseTranslations
 * @since     1.0.0
 *
 * @property  DatabaseTranslationsService $databaseTranslationsService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class DatabaseTranslations extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * DatabaseTranslations::$plugin
     *
     * @var DatabaseTranslations
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public $hasCpSection = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->name = $this->settings->pluginName;

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('databaseTranslations', DatabaseTranslationsVariable::class);
            }
        );

        $this->initRoutes();
        $this->initDbMessageSource();
    }

    private function initDbMessageSource()
    {
        /** @var I18N $i18n */
        $i18n = Craft::$app->getComponents(false)['i18n'];

        foreach ($this->settings->getCategories() as $category) {
            $i18n->translations[$category] = [
                'class' => DbMessageSource::class,
                'sourceLanguage' => Craft::$app->getSites()->getPrimarySite()->language,
                'forceTranslation' => true,
            ];
        }

        Craft::$app->setComponents(['i18n' => $i18n]);
    }

    private function initRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['database-translations/settings'] = 'database-translations/settings/index';
            }
        );
    }

    public function getCpNavItem()
    {
        $nav = parent::getCpNavItem();
        $nav['subnav']['translations'] = ['label' => 'Translations', 'url' => 'database-translations'];
        $nav['subnav']['create'] = ['label' => 'Create', 'url' => 'database-translations/create'];

        if (Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $nav['subnav']['settings'] = ['label' => 'Settings', 'url' => 'database-translations/settings'];
        }

        return $nav;
    }


    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'database-translations/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
