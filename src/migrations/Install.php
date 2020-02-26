<?php

namespace oofbar\membership\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%membership_grants}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'planId' => $this->integer()->notNull(),
            'userGroupId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%membership_logs}}', [
            'id' => $this->primaryKey(),
            'message' => $this->text()->notNull(),
            'grantId' => $this->integer()->notNull(),
            'subscriptionId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Foreign Keys
        $this->addForeignKey(null, '{{%membership_grants}}', ['planId'], '{{%commerce_plans}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%membership_grants}}', ['userGroupId'], '{{%usergroups}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%membership_logs}}', ['grantId'], '{{%membership_grants}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%membership_logs}}', ['subscriptionId'], '{{%commerce_subscriptions}}', ['id'], 'CASCADE');

        // Indexes
        $this->createIndex(null, '{{%membership_grants}}', ['planId', 'userGroupId'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%membership_grants}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%membership_logs}}', $this);

        $this->dropTableIfExists('{{%membership_grants}}');
        $this->dropTableIfExists('{{%membership_logs}}');
    }
}
