<?php

namespace digitalpulsebe\database_translations\helpers;

use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\i18n\I18N;
use craft\web\View;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\SourceMessage;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\SetNode;
use Twig\Source;
use Craft;
use Exception;
use yii\i18n\PhpMessageSource;

class PhpTranslationsHelper
{
    public static function findFiles(): array
    {
        $files = FileHelper::findFiles(Craft::$app->path->getSiteTranslationsPath(), ['only' => ['*.php']]);

        foreach (DatabaseTranslations::$plugin->originalCategories as $i18nCategory => $categorySettings) {
            if ($categorySettings) {
                if ($categorySettings instanceof PhpMessageSource) {
                    $basePath = $categorySettings->basePath;
                } elseif (is_array($categorySettings)) {
                    $basePath = $categorySettings['basePath'];
                }
                if ($basePath) {
                    try {
                        $files = array_merge($files, FileHelper::findFiles(App::parseEnv($basePath), ['only' => ['*.php']]));
                    } catch (\Throwable $exception) {
                        // directory does not exist probably
                    }
                }
            }
        }

        sort($files);

        return $files;
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
