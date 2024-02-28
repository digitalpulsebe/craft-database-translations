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
use craft\elements\Entry;
use craft\models\Section;
use craft\models\Site;
use Craft;
use digitalpulsebe\database_translations\helpers\EntryHelper;

class CopyService extends Component
{
    /**
     * @param Entry $source
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return Entry
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function copyEntry(Entry $source, Site $sourceSite, Site $targetSite): Entry
    {
        $targetEntry = $this->findTargetEntry($source, $targetSite->id);

        if (isset($source->title)) {
            $targetEntry->title = $source->title;
            $targetEntry->slug = null;
        }

        $targetEntry->setFieldValues($source->getSerializedFieldValues());

        if ($targetEntry->getIsDraft()) {
            Craft::$app->drafts->saveElementAsDraft($targetEntry);
        } else {
            Craft::$app->elements->saveElement($targetEntry);
        }

        return $targetEntry;
    }

    public function findTargetEntry(Entry $source, int $targetSiteId): Entry
    {
        $targetEntry = EntryHelper::one($source->id, $targetSiteId);

        if (empty($targetEntry)) {
            // we need to create one for this target site
            if ($source->section->propagationMethod == Section::PROPAGATION_METHOD_CUSTOM) {
                // create for site first, but keep enabled status
                $sitesEnabled = $source->getEnabledForSite();
                if (is_array($sitesEnabled) && !isset($sitesEnabled[$targetSiteId])) {
                    $sitesEnabled[$targetSiteId] = $source->enabledForSite;
                } else {
                    $sitesEnabled = [
                        $source->site->id => $source->enabledForSite,
                        $targetSiteId => $source->enabledForSite,
                    ];
                }

                $source->setEnabledForSite($sitesEnabled);

                if ($source->getIsDraft()) {
                    Craft::$app->drafts->saveElementAsDraft($source);
                } else {
                    Craft::$app->elements->saveElement($source);
                }

                $targetEntry = EntryHelper::one($source->id, $targetSiteId);
            } elseif ($source->section->propagationMethod == Section::PROPAGATION_METHOD_ALL) {
                // it should have been there, so propagate
                $targetEntry = Craft::$app->elements->propagateElement($source, $targetSiteId, false);
            } else {
                // duplicate to the target site
                $targetEntry = Craft::$app->elements->duplicateElement($source, ['siteId' => $targetSiteId]);
            }
        }

        return $targetEntry;
    }
}
