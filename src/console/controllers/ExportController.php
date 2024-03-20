<?php

namespace digitalpulsebe\database_translations\console\controllers;

use Craft;
use craft\console\Controller;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\console\ExitCode;

class ExportController extends Controller
{

    /**
     * Generate a content migration file for all translation rows in the current database
     */
    public function actionMigration(): int
    {
        $sourceMessages = SourceMessage::find()->with('messages')->all();
        DatabaseTranslations::getInstance()->databaseTranslationsService->createMigration($sourceMessages);

        return ExitCode::OK;
    }
}