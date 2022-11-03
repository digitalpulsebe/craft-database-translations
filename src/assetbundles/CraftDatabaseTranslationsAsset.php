<?php
/**
 * CraftDatabaseTranslations plugin for Craft CMS 3.x
 *
 *
 * @link      https://digitalpulse.be
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\database_translations\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Digital Pulse
 * @package   CraftDatabaseTranslations
 * @since     3.0.0
 */
class CraftDatabaseTranslationsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@digitalpulsebe/database_translations/assetbundles/database_translations/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
        ];

        $this->css = [
        ];

        parent::init();
    }
}
