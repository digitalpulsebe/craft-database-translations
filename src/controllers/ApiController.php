<?php

namespace digitalpulsebe\database_translations\controllers;

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

    public function actionMessages(): Response
    {
        $filters = $this->request->post('filters');

        return $this->asJson([
            'sourceMessages' => SourceMessage::filter($filters)->with('messages')->all()
        ]);
    }
}