<?php

namespace digitalpulsebe\database_translations\controllers;

use Craft;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\SourceMessage;
use yii\web\Response;
use craft\web\Controller;

class ApiController extends Controller
{

    public $enableCsrfValidation = false;
    
    public function actionIndex(): Response
    {
        $settings = DatabaseTranslations::$plugin->getSettings();
        $filters = $this->request->post('filters');

        return $this->asJson([
            'categories' => $settings->getCategories(),
            'locales' => \Craft::$app->i18n->getSiteLocaleIds(),
            'sourceMessages' => SourceMessage::filter($filters)->with('messages')->all()
        ]);
    }

    public function actionExportFormats()
    {
        $options = [
            ['label' => 'CSV', 'value' => 'csv'],
        ];

        if (Craft::$app->user->checkPermission('exportTranslationMigration')) {
            $options[] = ['label' => 'Migration', 'value' => 'migration'];
        }

        return $this->asJson([
            'options' => $options,
        ]);
    }

    public function actionMessages(): Response
    {
        $filters = $this->request->post('filters');

        return $this->asJson([
            'sourceMessages' => SourceMessage::filter($filters)->with('messages')->all()
        ]);
    }
}