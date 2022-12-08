<?php

namespace digitalpulsebe\database_translations\controllers;

use digitalpulsebe\database_translations\models\Message;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\web\Response;
use craft\web\Controller;

class TranslationsController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionCreate(): Response
    {
        $this->requirePostRequest();

        $sourceMessage = new SourceMessage();
        $sourceMessage->message = $this->request->post('message');
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
        $separatorName = $this->request->post('separator');
        $separator = $separatorsMap[$separatorName];
        $languages = $this->request->post('languages');

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

        $date = strftime('%Y%m%d_%H%M%S');
        return $this->response->sendStreamAsFile($file, "export_translations-$date.csv");
    }
}