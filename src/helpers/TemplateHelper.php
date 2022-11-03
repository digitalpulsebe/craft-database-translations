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

class TemplateHelper
{
    public static function searchAllTemplateMessages()
    {
        $view = new View();
        $view->setTemplateMode('site');
        $twig = $view->getTwig();
        $templateMessages = ['new' => [], 'existing' => []];
        $existing = SourceMessage::find()->with('messages')->orderBy('message')->indexBy('message')->all();

        foreach (static::getAllTemplateFiles() as $file) {
            $template_str = file_get_contents($file);
            $twig_source = new Source($template_str, basename($file));

            try {
                $token_stream = $twig->tokenize($twig_source);
                $templateNode = $twig->parse($token_stream);
                $foundMessages = [];
                static::parseTemplateNodes($templateNode, $foundMessages);

                foreach($foundMessages as $foundMessage) {
                    $foundMessage['file'] = str_replace(Craft::getAlias('@templates'), '', $file);
                    if (isset($existing[$foundMessage['message']])) {
                        $templateMessages['existing'][$foundMessage['message']] = $foundMessage;
                    } else {
                        $templateMessages['new'][$foundMessage['message']] = $foundMessage;
                    }
                }
            } catch (\Exception $e) {
                //
            }
        }

        ksort($templateMessages['new']);

        return $templateMessages;
    }

    private static function parseTemplateNodes($node, array &$foundMessages, &$setNodes = []): void
    {
        if ($node instanceof SetNode) {
            try {
                $setNodes[$node->getNode('names')->getAttribute('name')] = $node->getNode('values')->getAttribute(
                    'value'
                );
            } catch (Exception $e) {
            }
        }
        if ($node instanceof FilterExpression) {
            try {
                $name = $node->getNode('filter')->getAttribute('value');
                if ($name === 't') {
                    $valueNode = $node->getNode('node');
                    $argumentNode = $node->getNode('arguments');
                    $category = 'site';
                    if ($argumentNode->getIterator()->count() > 0) {
                        foreach ($argumentNode->getIterator() as $key => $value) {
                            if ($key === 'category') {
                                $category = $value->getAttribute('value');
                            }
                        }
                    }
                    if ($valueNode instanceof ConstantExpression) {
                        $value = $valueNode->getAttribute('value');
                        $foundMessages[] = ['message' => $value, 'category' => $category];
                    }
                    if ($valueNode instanceof TempNameExpression) {
                        $tempName = $valueNode->getAttribute('name');
                        if (isset($setNodes[$tempName])) {
                            $foundMessages[] = ['message' => $setNodes[$tempName], 'category' => $category];
                        }
                    }
                }
            } catch (Exception $e) {
            }
        }
        if ($node) {
            foreach ($node as $child) {
                static::parseTemplateNodes($child, $foundMessages, $setNodes);
            }
        }
    }

    public static function getAllTemplateFiles(): array
    {
        return FileHelper::findFiles(
            Craft::getAlias('@templates'),
            [
                'only' => [
                    '*.twig'
                ]
            ]
        );
    }
}