<?php

namespace digitalpulsebe\database_translations\controllers;

use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\web\UploadedFile;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\helpers\PhpTranslationsHelper;
use digitalpulsebe\database_translations\helpers\TemplateHelper;
use digitalpulsebe\database_translations\models\Message;
use digitalpulsebe\database_translations\models\SourceMessage;
use Craft;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use craft\web\Controller;

class PhpImportController extends Controller
{
    public function actionConfig(): Response
    {
        $files = PhpTranslationsHelper::findFiles();
        $categories = DatabaseTranslations::$plugin->settings->getCategories();
        $languages = DatabaseTranslations::$plugin->databaseTranslationsService->languageIds();

        return $this->renderTemplate('database-translations/_php-import/config.twig', [
            'files' => $files,
            'categories' => $categories,
            'languages' => $languages,
        ]);
    }

    public function actionReview(): Response
    {
        $filePath = $this->request->get('file');
        $language = $this->request->get('language');
        $category = $this->request->get('category');

        $found = PhpTranslationsHelper::findNewTranslations($filePath, $language, $category);

        return $this->renderTemplate('database-translations/_php-import/review.twig', [
            'foundMessages' => $found,
            'language' => $language,
            'category' => $category,
        ]);
    }

    public function actionImport(): Response
    {
        $this->requirePostRequest();

        $count = 0;

        foreach ($this->request->post('messages') as $inputRow) {
            if ($inputRow['include'] == '1') {
                $language = $inputRow['language'];
                $category = $inputRow['category'];
                $messageKey = $inputRow['message'];
                $value = $inputRow['translation'];

                $sourceMessage = SourceMessage::find()->where([
                    'category' => $category,
                    'message' => $messageKey,
                ])->with(['messages'])->one();

                if (!$sourceMessage) {
                    $sourceMessage = new SourceMessage();
                    $sourceMessage->category = $category;
                    $sourceMessage->message = $messageKey;
                    $sourceMessage->save();
                }

                if ($sourceMessage) {
                    $sourceMessage->updateTranslation($language, $value);
                }

                $count++;
            }
        }

        $this->setSuccessFlash("Imported $count new messages");
        return $this->redirect('database-translations');
    }
}