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
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\errors\SiteNotFoundException;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\i18n\I18N;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use digitalpulsebe\database_translations\elements\actions\Copy;
use digitalpulsebe\database_translations\components\DbMessageSource;
use digitalpulsebe\database_translations\models\Settings;
use digitalpulsebe\database_translations\models\SourceMessage;
use digitalpulsebe\database_translations\services\CopyService;
use digitalpulsebe\database_translations\services\DatabaseTranslationsService;
use digitalpulsebe\database_translations\services\TranslateService;
use digitalpulsebe\database_translations\variables\DatabaseTranslationsVariable;
use yii\base\Event;
use yii\i18n\MessageSource;
use yii\i18n\MissingTranslationEvent;

/**
 *
 * @author    Digital Pulse
 * @package   DatabaseTranslations
 * @since     1.0.0
 *
 * @property  DatabaseTranslationsService $databaseTranslationsService
 * @property  CopyService $copyService
 * @property  TranslateService $translateService
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
    public string $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public bool $hasCpSection = true;

    /**
     *
     * @var array
     */
    public array $originalCategories = [];

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->name = $this->settings->pluginName;

        $this->setComponents([
            'databaseTranslationsService' => DatabaseTranslationsService::class,
            'copyService' => CopyService::class,
            'translateService' => TranslateService::class,
        ]);

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

        Craft::$app->onInit(function() {
            $this->initRoutes();
            $this->initDbMessageSource();
            $this->initHandleMissingTranslations();
            $this->registerPermissions();
            $this->registerActions();
        });

    }

    private function initDbMessageSource()
    {
        $i18n = Craft::$app->getI18n();

        $this->originalCategories = $i18n->translations;

        foreach ($this->settings->getCategories() as $category) {
            try {
                $i18n->translations[$category] = [
                    'class' => DbMessageSource::class,
                    'sourceLanguage' => Craft::$app->getSites()->getPrimarySite()->language,
                    'forceTranslation' => true,
                ];
            } catch (SiteNotFoundException $e) {
                // ignore, during install or tests, there might be no primary site.
            }
        }
    }

    private function initRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['database-translations/settings'] = 'database-translations/settings/index';
                $event->rules['database-translations/parse/review'] = 'database-translations/parse/review';
                $event->rules['database-translations/translations/update'] = 'database-translations/translations/update';
                $event->rules['database-translations/translations/delete/<id:\d+>'] = 'database-translations/translations/delete';
                $event->rules['database-translations/translations/delete'] = 'database-translations/translations/delete';
                $event->rules['database-translations/translations/export'] = 'database-translations/translations/export';
                $event->rules['database-translations/translations/export-migration'] = 'database-translations/translations/export-migration';
                $event->rules['database-translations/translations/translate'] = 'database-translations/translations/translate';
                $event->rules['database-translations/api/index'] = 'database-translations/api/index';
                $event->rules['database-translations/api/messages'] = 'database-translations/api/messages';
                $event->rules['database-translations/api/export-formats'] = 'database-translations/api/export-formats';
                $event->rules['database-translations/csv-import/map/<sessionKey:{slug}>'] = 'database-translations/csv-import/map';
                $event->rules['database-translations/csv-import/review/<sessionKey:{slug}>'] = 'database-translations/csv-import/review';
                $event->rules['database-translations/php-import/config'] = 'database-translations/php-import/config';
                $event->rules['database-translations/php-import/review'] = 'database-translations/php-import/review';
            }
        );
    }

    private function initHandleMissingTranslations() {
        if ($this->settings->getHandleMissingTranslations()) {
            Event::on(
                MessageSource::class,
                MessageSource::EVENT_MISSING_TRANSLATION,
                function (MissingTranslationEvent $event) {
                    if (!$event->message || !in_array($event->category, $this->settings->getCategories(), true)) {
                        return;
                    }

                    $sourceMessage = SourceMessage::find()->where([
                        'category' => $event->category,
                        'message' => $event->message
                    ])->one();

                    if (!$sourceMessage) {
                        $sourceMessage = new SourceMessage();
                        $sourceMessage->category = $event->category;
                        $sourceMessage->message = $event->message;
                        $sourceMessage->save();
                    }
                }
            );
        }
    }

    private function registerActions(): void
    {
        if ($this->settings->enableCopyAction) {
            Event::on(
                Entry::class,
                Element::EVENT_REGISTER_ACTIONS,
                function(RegisterElementActionsEvent $event) {
                    $defaultSiteHandle = Craft::$app->sites->currentSite->handle;
                    $sourceSiteHandle = Craft::$app->request->getParam('site', $defaultSiteHandle);

                    if (Craft::$app->user->checkPermission('bulkCopyElements')) {
                        $event->actions[] = [
                            'type' => Copy::class,
                            'sourceSiteHandle' => $sourceSiteHandle
                        ];
                    }
                }
            );
        }
    }

    /**
     * Register custom permission
     *
     * @return void
     */
    private function registerPermissions(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'Database Translations',
                    'permissions' => [
                        'bulkCopyElements' => [
                            'label' => 'Copy content bulk action',
                        ],
                        'exportTranslationMigration' => [
                            'label' => 'Export translations to migration file',
                        ],
                    ],
                ];
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $nav = parent::getCpNavItem();
        $nav['subnav']['translations'] = ['label' => 'Translations', 'url' => 'database-translations'];
        $nav['subnav']['create'] = ['label' => 'Add / Import', 'url' => 'database-translations/create'];
        $nav['subnav']['export'] = ['label' => 'Export', 'url' => 'database-translations/export'];

        if (Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $nav['subnav']['settings'] = ['label' => 'Settings', 'url' => 'database-translations/settings'];
        }

        return $nav;
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
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
