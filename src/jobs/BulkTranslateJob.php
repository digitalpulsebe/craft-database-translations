<?php

namespace digitalpulsebe\database_translations\jobs;

use craft\queue\BaseJob;
use digitalpulsebe\database_translations\DatabaseTranslations;
use digitalpulsebe\database_translations\models\SourceMessage;

class BulkTranslateJob extends BaseJob
{
    public array $messageIds;
    public string $sourceLocale;
    public string $targetLocale;

    public ?string $description = 'Translating lines...';

    public function execute($queue): void
    {
        $this->setProgress($queue, 1);

        $this->description = "Translating lines...";

        $sourceMessages = SourceMessage::find()->where(['id' => $this->messageIds])->with('messages')->all();

        $totalCount = count($sourceMessages);

        foreach ($sourceMessages as $i => $sourceMessage) {
            $iHuman = $i+1;
            $this->setProgress($queue, $i/$totalCount, "Translating line $iHuman/$totalCount ($sourceMessage->message)");

            if ($this->sourceLocale == 'message') {
                $sourceText = $sourceMessage->message;
                $sourceLocale = null;
            } else {
                $sourceText = $sourceMessage->getTranslation($this->sourceLocale);
                $sourceLocale = $this->sourceLocale;
            }

            if (!empty($sourceText)) {
                $translation = DatabaseTranslations::getInstance()->translateService->translate($sourceLocale, $this->targetLocale, $sourceText);
                $sourceMessage->updateTranslation($this->targetLocale, $translation);
            }

        }

        $this->setProgress($queue, 100, 'done');
    }
}
