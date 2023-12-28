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
use craft\base\Element;
use craft\base\FieldInterface;
use craft\elements\Entry;
use craft\models\Section;
use craft\models\Site;
use Craft;

class CopyService extends Component
{
    static array $matrixFields = [
        'craft\fields\Matrix',
        'benf\neo\Field',
        'verbb\supertable\fields\SuperTableField',
    ];

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

        $targetValues = $this->copyElement($source, $sourceSite, $targetSite);

        if (isset($targetValues['title'])) {
            $targetEntry->title = $targetValues['title'];
            $targetEntry->slug = null;
            unset($targetValues['title']);
        }

        $targetEntry->setFieldValues($targetValues);

        Craft::$app->elements->saveElement($targetEntry);
        return $targetEntry;
    }

    public function copyElement(Element $source, Site $sourceSite, Site $targetSite)
    {
        $target = [];

        if ($source->title) {
            $target['title'] = $source->title;
        }

        foreach ($source->fieldLayout->getCustomFields() as $field) {
            if (in_array(get_class($field), static::$matrixFields)) {
                // dig deeper in Matrix fields
                $target[$field->handle] = $this->copyMatrixField($source, $field, $sourceSite, $targetSite);
            } else {
                $target[$field->handle] = $source->getSerializedFieldValues([$field->handle])[$field->handle] ?? null;
            }
        }

        return $target;
    }

    public function copyMatrixField(Element $element, FieldInterface $field, Site $sourceSite, Site $targetSite): array
    {
        $query = $element->getFieldValue($field->handle);

        // serialize current value
        $serialized = $element->getSerializedFieldValues([$field->handle])[$field->handle];

        foreach ($query->all() as $matrixElement) {
            $copiedMatrixValues = $this->copyElement($matrixElement, $sourceSite, $targetSite);
            foreach ($copiedMatrixValues as $matrixFieldHandle => $value) {
                // only set translated values in matrix array
                if ($value && isset($serialized[$matrixElement->id])) {
                    if ($matrixFieldHandle == 'title') {
                        $serialized[$matrixElement->id][$matrixFieldHandle] = $value;
                    } else {
                        $serialized[$matrixElement->id]['fields'][$matrixFieldHandle] = $value;
                    }
                }
            }
        }

        return $serialized;
    }

    public function findTargetEntry(Entry $source, int $targetSiteId): Entry
    {
        $targetEntry = Entry::find()->status(null)->id($source->id)->siteId($targetSiteId)->one();

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
                Craft::$app->elements->saveElement($source);
                $targetEntry = Entry::find()->status(null)->id($source->id)->siteId($targetSiteId)->one();
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
