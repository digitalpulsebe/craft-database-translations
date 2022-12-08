<?php

namespace digitalpulsebe\database_translations\helpers;

use craft\helpers\FileHelper;
use craft\web\View;
use digitalpulsebe\database_translations\models\SourceMessage;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\SetNode;
use Twig\Source;
use Craft;
use Exception;

class PhpTranslationsHelper
{
    public static function findFiles(): array
    {
        return FileHelper::findFiles(Craft::$app->path->getSiteTranslationsPath(), ['only' => ['*.php']]);
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