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
    static array $textFields = [
        'craft\fields\PlainText',
        'craft\redactor\Field',
        'craft\ckeditor\Field',
    ];
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
//        $translatedValues = $this->translateElement($source, $sourceSite, $targetSite);

        $targetEntry = $this->findTargetEntry($source, $targetSite->id);

        if (isset($source->title)) {
            $targetEntry->title = $source->title;
            $targetEntry->slug = null;
        }

        $targetEntry->setFieldValues($source->getSerializedFieldValues());

        \Craft::$app->elements->saveElement($targetEntry);
        return $targetEntry;
    }


    /**
     * @param Element $source
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return array
     */
    public function translateElement(Element $source, Site $sourceSite, Site $targetSite): array
    {
        $target = [];

        if ($source->title) {
            $target['title'] = $source->title;
        }

        foreach ($source->fieldLayout->getCustomFields() as $field) {
            $target[$field->handle] = $source->getSerializedFieldValues([$field->handle])[$field->handle];
            /*$translatedValue = null;
            $fieldTranslatable = $field->translationMethod != Field::TRANSLATION_METHOD_NONE;

            if (in_array(get_class($field), static::$textFields)) {
                // normal text fields
                $translatedValue = $this->translateTextField($source, $field, $sourceSite, $targetSite);
            } elseif (in_array(get_class($field), static::$matrixFields)) {
                // dig deeper in Matrix fields
                $translatedValue = $this->translateMatrixField($source, $field, $sourceSite, $targetSite);
            } elseif (get_class($field) == Table::class) {
                // loop over table
                $translatedValue = $this->translateTable($source, $field, $sourceSite, $targetSite);
            } elseif (get_class($field) == 'lenz\linkfield\fields\LinkField') {
                // translate linkfield custom label
                $translatedValue = $this->translateLinkField($source, $field, $sourceSite, $targetSite);
            }

            if ($translatedValue) {
                $target[$field->handle] = $translatedValue;
            }*/
        }

        return $target;
    }

    public function translateTextField(Element $element, FieldInterface $field, Site $sourceSite, Site $targetSite): ?string
    {
        return $field->serializeValue($element->getFieldValue($field->handle), $element);
    }

    public function translateTable(Element $element, FieldInterface $field, Site $sourceSite, Site $targetSite): array
    {
        return $field->serializeValue($element->getFieldValue($field->handle), $element);
    }

    public function translateMatrixField(Element $element, FieldInterface $field, Site $sourceSite, Site $targetSite): array
    {
        $query = $element->getFieldValue($field->handle);

        // serialize current value
        return $element->getSerializedFieldValues([$field->handle])[$field->handle];
    }

    public function translateLinkField(Element $element, FieldInterface $field, Site $sourceSite, Site $targetSite): ?array
    {
        $value = $element->getFieldValue($field->handle);
        if ($value) {
            try {
                return $value->toArray();
            } catch (\Throwable $throwable) {
                // too bad, f*** linkfields
                return null;
            }
        }
        return null;
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
