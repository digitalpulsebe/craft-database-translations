<?php

namespace digitalpulsebe\database_translations\controllers;

use \Craft;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\helpers\EntryHelper;
use yii\web\Response;
use craft\web\Controller;

class SidebarController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionCopy(): Response
    {
        $this->requirePermission('copyElements');
        $sourceSite = Craft::$app->getSites()->getSiteById($this->request->get('sourceSiteId'));
        $targetSite = Craft::$app->getSites()->getSiteById($this->request->get('targetSiteId'));

        $entry = EntryHelper::one($this->request->get('elementId'), $sourceSite->id);
        $copiedEntry = DatabaseTranslations::getInstance()->copyService->copyEntry($entry, $sourceSite, $targetSite);
        return $this->asSuccess('Element translated', ['elementId' => $copiedEntry->id], $copiedEntry->getCpEditUrl());
    }

    public function actionCopyToAll()
    {
        $this->requirePermission('copyElements');

        $sourceSite = Craft::$app->getSites()->getSiteById($this->request->get('sourceSiteId'));
        $entry = EntryHelper::one($this->request->get('elementId'), $sourceSite->id);

        $targetSites = collect(\craft\helpers\ElementHelper::supportedSitesForElement($entry, true))
            ->filter(function ($site) use ($sourceSite) { return $site['siteId'] != $sourceSite->id; })
            ->map(function ($site) { return Craft::$app->sites->getSiteById($site['siteId']); });

        $successSites = collect();

        foreach ($targetSites as $targetSite) {
            try {
                $copiedEntry = DatabaseTranslations::getInstance()->copyService->copyEntry($entry, $sourceSite, $targetSite);

                if (!empty($copiedEntry)) {
                    if (!empty($copiedEntry->errors)) {
                        $this->setFailFlash('Validation errors '.json_encode($copiedEntry->errors));
                    } else {
                        $successSites->push($targetSite);
                    }
                }
            } catch (\Throwable $throwable) {
                Craft::$app->session->setError($throwable->getMessage());
            }
        }

        $targetSiteNames = $successSites->pluck('name')->join(', ');
        $this->setSuccessFlash("Element copied to $targetSiteNames");

        return $this->redirect($entry->cpEditUrl);
    }

}
