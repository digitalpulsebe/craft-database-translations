<?php

namespace digitalpulsebe\database_translations\models;

use craft\db\ActiveRecord;

class Message extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dp_translations_message}}';
    }

    /**
     * @param int $id
     * @param string $language
     * @param string $value
     * @return int Number of rows updated
     * @throws \yii\db\Exception
     */
    public static function insertOrUpdateTranslation(int $id, string $language, string $value): int
    {
        $tableName = self::tableName();
        return \Craft::$app->db
            ->createCommand("INSERT INTO $tableName (id, language, translation) VALUES (:id, :language, :translation) 
                ON DUPLICATE KEY UPDATE translation=:translation")
            ->bindValue(':id', $id)
            ->bindValue(':language', $language)
            ->bindValue(':translation', $value)
            ->execute();
        ;
    }
}