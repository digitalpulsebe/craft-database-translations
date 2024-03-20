<?php

namespace digitalpulsebe\database_translations\controllers;

use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\jobs\BulkTranslateJob;
use digitalpulsebe\database_translations\models\Message;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\base\Exception;
use yii\web\Response;
use craft\web\Controller;
use Craft;

class TranslationsController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionCreate(): Response
    {
        $this->requirePostRequest();

        $sourceMessage = new SourceMessage();
        $sourceMessage->message = trim($this->request->post('message'));
        $sourceMessage->category = $this->request->post('category');

        if (!$sourceMessage->validate()) {
            // todo

            return $this->asJson([
                'errors' => $sourceMessage->errors
            ]);
        }

        $sourceMessage->save();

        $this->setSuccessFlash('Message created');
        return $this->redirectToPostedUrl();
    }

    public function actionUpdate(): Response
    {
        $this->requirePostRequest();

        /* @var SourceMessage[] $sourceMessages */
        $sourceMessages = SourceMessage::find()->with('messages')->indexBy('id')->all();

        $errors = [];

        $inputMessages = $this->request->post('messages');

        if (empty($inputMessages)) {
            return $this->asJson([
                'success' => false,
                'errors' => ['Missing messages']
            ]);
        }

        foreach ($inputMessages as $id => $values) {
            $sourceMessage = $sourceMessages[$id] ?? null;

            if ($sourceMessage) {
                foreach ($values as $language => $value) {
                    $message = $sourceMessage->getMessage($language);

                    if (!$message) {
                        $message = new Message();
                        $message->id = $id;
                        $message->language = $language;
                    }

                    if (DatabaseTranslations::$plugin->settings->trimValuesOnSave) {
                        $value = trim($value);
                    }

                    if ($value === '') {
                        $value = null;
                    }

                    $message->translation = $value;

                    if(!$message->save()) {
                        $errors[$id] = $message->errors;
                    }
                }
            }
        }

        if (count($errors)) {
            return $this->asJson([
                'success' => false,
                'errors' => $errors
            ]);
        }

        if ($this->request->getAcceptsJson()) {
            $updatedIds = array_keys($inputMessages);
            return $this->asJson([
                'success' => true,
                'sourceMessages' => SourceMessage::find()->where(['id' => $updatedIds])->with('messages')->all()
            ]);
        }

        $this->setSuccessFlash();
        return $this->redirectToPostedUrl();
    }

    public function actionDelete($id = null): Response
    {
        if ($id == null) {
            $id = $this->request->post('messages');
        }

        SourceMessage::deleteAll(['id' => $id]);

        if ($this->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->redirect('database-translations');
    }

    public function actionExport()
    {
        $separatorsMap = [
            'semicolon' => ';',
            'comma' => ',',
        ];

        $this->requirePostRequest();
        $filters = $this->request->post('filters');
        $separatorName = $this->request->post('separator', 'semicolon');
        $separator = $separatorsMap[$separatorName];
        $languages = $this->request->post('languages', DatabaseTranslations::$plugin->databaseTranslationsService->languageIds());

        $sourceMessages = SourceMessage::filter($filters)->orderBy('message')->with('messages')->all();

        $columns = array_merge(['category', 'message'], array_values($languages));

        $file = tmpfile();
        fputcsv($file, array_values($columns), $separator);

        foreach ($sourceMessages as $sourceMessage)
        {
            $row = [$sourceMessage->category, $sourceMessage->message];
            foreach ($languages as $language) {
                $row[] = $sourceMessage->getTranslation($language);
            }
            fputcsv($file, $row, $separator);
        }

        $date = date('Ymd_His');

        if ($this->request->getAcceptsJson()) {
            fseek($file, 0);
            $content = stream_get_contents($file);
            return $this->asJson([
                'success' => true,
                'fileSize' => strlen($content),
                'fileName' => "export_translations-$date.csv",
                'file' => $content,
            ]);
        }

        return $this->response->sendStreamAsFile($file, "export_translations-$date.csv");
    }

    public function actionExportMigration()
    {
        $this->requirePostRequest();
        $filters = $this->request->post('filters');
        $languages = $this->request->post('languages', null);
        $sourceMessages = SourceMessage::filter($filters)->orderBy('message')->with('messages')->all();

        $filename = DatabaseTranslations::getInstance()->databaseTranslationsService->createMigration($sourceMessages, $languages);

        return $this->asJson([
            'fileName' => $filename,
            'success' => true,
        ]);
    }

    public function actionTranslate()
    {
        try {
            $this->requirePostRequest();
            $ids = $this->request->post('messages');

            if(!DatabaseTranslations::getInstance()->translateService->isAvailable()) {
                throw new Exception('Multi Translator not available');
            }

            $sourceLocale = $this->request->post('sourceLocale');
            $targetLocale = $this->request->post('targetLocale');

            Craft::$app->getQueue()->push(new BulkTranslateJob([
                'messageIds' => $ids,
                'sourceLocale' => $sourceLocale,
                'targetLocale' => $targetLocale,
            ]));

            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => true
                ]);
            }

            $this->setSuccessFlash();
            return $this->redirectToPostedUrl();
        } catch (\Throwable $throwable) {
            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'error' => $throwable->getMessage(),
                ]);
            }

            $this->setFailFlash($throwable->getMessage());
            return $this->redirectToPostedUrl();
        }

    }
}
