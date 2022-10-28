<?php

namespace digitalpulsebe\database_translations\migrations;

class Install extends \craft\db\Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dp_translations_source_message}}', [
            'id' => $this->primaryKey(),
            'category' => $this->string(),
            'message' => $this->text(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ], $tableOptions);

        $this->createTable('{{%dp_translations_message}}', [
            'id' => $this->integer()->notNull(),
            'language' => $this->string(16)->notNull(),
            'translation' => $this->text(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ], $tableOptions);

        $this->addPrimaryKey('pk_message_id_language', '{{%dp_translations_message}}', ['id', 'language']);
        $onUpdateConstraint = 'RESTRICT';
        if ($this->db->driverName === 'sqlsrv') {
            // 'NO ACTION' is equivalent to 'RESTRICT' in MSSQL
            $onUpdateConstraint = 'NO ACTION';
        }
        $this->addForeignKey('fk_dp_translations_message_source_message', '{{%dp_translations_message}}', 'id', '{{%dp_translations_source_message}}', 'id', 'CASCADE', $onUpdateConstraint);
        $this->createIndex('idx_source_message_category', '{{%dp_translations_source_message}}', 'category');
        $this->createIndex('idx_message_language', '{{%dp_translations_message}}', 'language');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_dp_translations_message_source_message', '{{%dp_translations_message}}');
        $this->dropTable('{{%dp_translations_message}}');
        $this->dropTable('{{%dp_translations_source_message}}');
    }
}