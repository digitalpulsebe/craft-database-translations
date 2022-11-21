<?php

namespace digitalpulsebe\database_translations\controllers;

use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\web\UploadedFile;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\helpers\TemplateHelper;
use digitalpulsebe\database_translations\models\Message;
use digitalpulsebe\database_translations\models\SourceMessage;
use Craft;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use craft\web\Controller;

class CsvImportController extends Controller
{

    /**
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionUpload()
    {
        $this->requirePostRequest();

        $sessionKey = StringHelper::randomString(16);

        $uploadedFile = UploadedFile::getInstanceByName('upload');

        if (empty($uploadedFile) || strpos($uploadedFile->type, 'csv') === false) {
            Craft::$app->session->setError("not a correct file type");
            return $this->redirectToPostedUrl();
        }

        $tempPath = Craft::$app->getPath()->getTempPath(true);
        if ($uploadedFile->saveAs("$tempPath/$sessionKey.csv")) {
            return $this->redirect('database-translations/csv-import/map/'.$sessionKey);
        }

        Craft::$app->session->setError("upload failed");
        return $this->redirectToPostedUrl();
    }

    /**
     * @return mixed
     */
    public function actionMap($sessionKey)
    {
        try {
            $fileStream = $this->getFileStream($sessionKey);
            $header = $this->getCsvHeader($fileStream);
            $rows = $this->getCsvRows($fileStream);
        } catch (\Throwable $exception) {
            Craft::$app->session->setError($exception->getMessage());
            return $this->redirect('database-translations/create');
        }

        // special fields
        $fields = [
            'category' => 'Category',
            'message' => 'Message',
        ];

        foreach(Craft::$app->i18n->getSiteLocaleIds() as $siteLocaleId) {
            $fields[$siteLocaleId] = 'Translations: '.$siteLocaleId;
        }

        return $this->renderTemplate('database-translations/_csv-import/map.twig', compact('header', 'rows', 'fields', 'sessionKey'));
    }

    public function actionImport()
    {
        $this->requirePostRequest();
        $sessionKey = $this->request->post('sessionKey');
        // mapping input
        $columns = $this->request->post('columns');
        $errors = [];

        $requiredFields = [
            'category' => 'Category',
            'message' => 'Message',
        ];

        // check if all required columns are present in mapping
        foreach ($requiredFields as $key => $label) {
            if (array_search($key, $columns) === false) {
                $errors[] = "Target field \"$label\" is required";
            }
        }

        if (!empty($errors)) {
            // errors? -> go back
            // keep previous selected input
            Craft::$app->session->set('columns', $columns);
            Craft::$app->session->setError(join('; ', $errors));
            return $this->redirectToPostedUrl();
        }

        $fileStream = $this->getFileStream($sessionKey);
        // take away the header row, we don't need to import
        $header = $this->getCsvHeader($fileStream);
        $rows = $this->getCsvRows($fileStream);
        // list of values we don't want
        $rowCount = count($rows);

        foreach ($rows as $rowIndex => $row) {
            $categoryColumn = array_search('category', $columns);
            $messageColumn = array_search('message', $columns);

            $sourceMessage = SourceMessage::find()->where([
                'category' => $row[$categoryColumn],
                'message' => $row[$messageColumn],
            ])->with(['messages'])->one();

            if (!$sourceMessage) {
                $sourceMessage = new SourceMessage();
                $sourceMessage->category = $row[$categoryColumn];
                $sourceMessage->message = $row[$messageColumn];
                $sourceMessage->save();
            }

            // loop over columns of one row to be imported
            foreach ($row as $i => $value) {
                // the translation to fill, is the column selected in mapping before
                $language = $columns[$i] ?? null;

                // update translation if this column is one of the languages
                if (in_array($language, DatabaseTranslations::$plugin->databaseTranslationsService->languageIds())) {
                    $sourceMessage->updateTranslation($language, $value);
                }
            }
        }

        $this->setSuccessFlash("Imported $rowCount rows");
        return $this->redirect('database-translations');
    }

    protected function getFileStream($sessionKey) {
        $filePath = Craft::$app->getPath()->getTempPath() . "/$sessionKey.csv";
        return fopen($filePath,'r');
    }

    protected function getCsvHeader($fileStream) {
        $header = fgetcsv($fileStream, null, ';');
        foreach($header as $i => $column) {
            // clean non-word characters (like a BOM)
            $header[$i] = preg_replace('/[^\w &%\'-\/]/','', $column);;
        }
        return $header;
    }

    protected function getCsvRows($fileStream) {
        $rows = [];
        while($row = fgetcsv($fileStream, null, ';')) {
            $rows[] = $row;
        }
        return $rows;
    }
}