<?php

namespace digitalpulsebe\database_translations\helpers;

use Craft;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;

class EntryHelper
{

    /**
     * @param int|array $elementIds
     * @param int $siteId
     * @return EntryQuery
     */
    public static function query($elementIds, int $siteId): EntryQuery
    {
        return Entry::find()->drafts(null)->status(null)->id($elementIds)->siteId($siteId);
    }

    /**
     * @param int $elementId
     * @param int $siteId
     * @return Entry
     */
    public static function one(int $elementId, int $siteId): ?Entry
    {
        return self::query($elementId, $siteId)->one();
    }

    /**
     * @param array $elementIds the element ids to select
     * @param int $siteId the siteId
     * @return Entry[]
     */
    public static function all(array $elementIds, int $siteId): array
    {
        return self::query($elementIds, $siteId)->all();
    }
}
