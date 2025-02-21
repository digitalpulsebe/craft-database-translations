<?php

namespace digitalpulsebe\database_translations\helpers;

use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\i18n\I18N;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\SourceMessage;
use Craft;
use yii\i18n\PhpMessageSource;

class PhpTranslationsHelper
{
    public static function findFiles(): array
    {
        if (!is_dir(Craft::$app->path->getSiteTranslationsPath())) {
            $files = [];
        } else {
            $files = FileHelper::findFiles(Craft::$app->path->getSiteTranslationsPath(), ['only' => ['*.php']]);
        }

        $return = [];
        foreach($files as $file) {
            $return['site'][$file] = [
                'value' => $file,
                'label' => $file
            ];
        }

        $categoryTranslations = [];
        foreach (DatabaseTranslations::$plugin->originalCategories as $i18nCategory => $categorySettings) {
            if ($categorySettings) {
                if ($categorySettings instanceof PhpMessageSource) {
                    $basePath = $categorySettings->basePath;
                } elseif (is_array($categorySettings)) {
                    $basePath = $categorySettings['basePath'];
                }
                if ($basePath) {
                    try {
                        $files = FileHelper::findFiles(App::parseEnv($basePath), ['only' => ['*.php']]);

                        foreach($files as $file) {
                            $categoryTranslations[$i18nCategory][$file] = [
                                'value' => $file,
                                'label' => $file
                            ];
                        }
                    } catch (\Throwable $exception) {
                        // directory does not exist probably
                    }
                }
            }
        }

        if(!empty($categoryTranslations)) {
            ksort($categoryTranslations);
            $return =  array_merge($return, $categoryTranslations);
        }

        return $return;
    }

    public static function findDisabledCategories(): array
    {
        $i18n = Craft::$app->getI18n();

        $categories = [];
        $enabledCategories = DatabaseTranslations::getInstance()->originalCategories;

        foreach ($i18n->translations as $i18nCategory => $categorySettings) {
            if (!in_array($i18nCategory, $enabledCategories)) {
                $categories[] = $i18nCategory;
            }
        }

        sort($categories);

        return $categories;
    }

    public static function findNewTranslations(string $filePath, string $language, string $category): array
    {
        $existing = SourceMessage::find()->with('messages')->where(['category' => $category])->orderBy('message')->indexBy('message')->all();

        $messages = ['new' => [], 'existing' => []];

        $rows = include($filePath);

        foreach($rows as $key => $value) {
            $existingSourceMessage = $existing[$key] ?? null;
            $existingMessage = $existingSourceMessage ? $existingSourceMessage->getMessage($language) : null;

            if ($existingSourceMessage && $existingMessage) {
                $messages['existing'][$key] = [
                    'message' => $key,
                    'new' => $value,
                    'old' => $existingMessage->translation,
                ];
            } else {
                $messages['new'][$key] = [
                    'message' => $key,
                    'new' => $value,
                    'old' => '',
                ];
            }
        }

        return $messages;
    }
}
