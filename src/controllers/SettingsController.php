<?php

namespace digitalpulsebe\database_translations\controllers;

use craft\helpers\UrlHelper;
use yii\web\Response;
use craft\web\Controller;

class SettingsController extends Controller
{
    public function actionIndex(): Response
    {
        return $this->redirect(UrlHelper::cpUrl('settings/plugins/database-translations'));
    }
}