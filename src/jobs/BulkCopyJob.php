<?php

namespace digitalpulsebe\database_translations\jobs;

use \Craft;
use craft\elements\Entry;
use craft\queue\BaseJob;
use digitalpulsebe\database_translations\DatabaseTranslations;

class BulkCopyJob extends BaseJob
{
    public array $entryIds;
    public string $sourceSiteHandle;
    public string $targetSiteHandle;

    public ?string $description = 'Translating entries...';

    public function execute($queue): void
    {
        $this->setProgress($queue, 1);

        $this->description = "Copying entries...";

        $sourceSite = Craft::$app->getSites()->getSiteByHandle($this->sourceSiteHandle);
        $targetSite = Craft::$app->getSites()->getSiteByHandle($this->targetSiteHandle);

        $entries = Entry::find()->status(null)->id($this->entryIds)->siteId($sourceSite->id)->all();

        $entryCount = count($entries);

        foreach ($entries as $i => $entry) {
            $iHuman = $i+1;

            $this->setProgress($queue, $i/$entryCount, "Copying entry $iHuman/$entryCount");
            DatabaseTranslations::getInstance()->copyService->copyEntry($entry, $sourceSite, $targetSite);
        }

        $this->setProgress($queue, 100, 'done');
    }
}
