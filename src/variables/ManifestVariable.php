<?php

namespace digitalpulsebe\database_translations\variables;

use digitalpulsebe\database_translations\helpers\Manifest as ManifestHelper;
use digitalpulsebe\database_translations\assetbundles\DatabaseTranslationsAsset;

use Craft;
use craft\helpers\Template;

class ManifestVariable
{
    protected $sourcePath = '@digitalpulsebe/database_translations/assetbundles/dist';

    // Protected Static Properties
    // =========================================================================

    protected static $config = [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => false,
        // Manifest names
        'manifest'     => [
            'legacy' => 'manifest.json',
            'modern' => 'manifest.json',
        ],
        // Public server config
        'server'       => [
            'manifestPath' => '/',
            'publicPath' => '/',
        ],
        // webpack-dev-server config
        'devServer'    => [
            'manifestPath' => 'http://127.0.0.1:8080',
            'publicPath' => '/',
        ],
    ];

    // Public Methods
    // =========================================================================

    /**
     * ManifestVariable constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        ManifestHelper::invalidateCaches();
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            $this->sourcePath,
            true
        );
        self::$config['server']['manifestPath'] = Craft::getAlias($this->sourcePath);
        self::$config['server']['publicPath'] = $baseAssetsUrl;
        $useDevServer = getenv('NYS_PLUGIN_DEVSERVER');
        if ($useDevServer !== false) {
            self::$config['useDevServer'] = (bool)$useDevServer;
        }
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return \Twig\Markup
     * @throws \yii\web\NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null): \Twig\Markup
    {
        return Template::raw(
            ManifestHelper::getCssModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|string
     * @throws \yii\web\NotFoundHttpException
     */
    public function getModuleUri(string $moduleName, string $type = 'modern', $config = null)
    {
        return ManifestHelper::getModule(self::$config, $moduleName, $type, true);
    }
}
