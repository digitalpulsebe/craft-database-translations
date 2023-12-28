<?php

namespace digitalpulsebe\database_translations\controllers;

use digitalpulsebe\database_translations\helpers\TemplateHelper;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\web\Response;
use craft\web\Controller;

class ParseController extends Controller
{
    public function actionReview(): Response
    {
        $templateMessages = TemplateHelper::searchAllTemplateMessages();
        return $this->renderTemplate('database-translations/_parse/review.twig', ['templateMessages' => $templateMessages]);
    }

    public function actionImport(): Response
    {
        $this->requirePostRequest();

        $count = 0;

        foreach ($this->request->post('messages') as $inputRow) {
            if ($inputRow['include'] == '1') {
                $sourceMessage = new SourceMessage();
                $sourceMessage->message = $inputRow['message'];
                $sourceMessage->category = $inputRow['category'];
                if ($sourceMessage->save()) {
                    $count++;
                }
            }
        }

        $this->setSuccessFlash("Imported $count new messages");
        return $this->redirect('database-translations');
    }
}