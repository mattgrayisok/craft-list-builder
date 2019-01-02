<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\migrations;

use mattgrayisok\listbuilder\ListBuilder;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listbuilder_source}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listbuilder_source}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    //'siteId' => $this->integer()->notNull(),
                    'type' => $this->integer()->notNull()->defaultValue(1),
                    'name' => $this->string(255)->notNull()->defaultValue('New Source'),
                    'config' => $this->string(2047)->notNull()->defaultValue('{}'),
                    'shortcode' => $this->string(255)->notNull()->defaultValue(''),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listbuilder_signup}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listbuilder_signup}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    //'siteId' => $this->integer()->notNull(),
                    'email' => $this->string(255)->notNull()->defaultValue(''),
                    'sourceId' => $this->integer()->defaultValue(null),
                    'consent' => $this->integer()->defaultValue(0),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listbuilder_destination}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listbuilder_destination}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'dateEnabled' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    //'siteId' => $this->integer()->notNull(),
                    'type' => $this->integer()->notNull()->defaultValue(1),
                    'config' => $this->string(2047)->defaultValue('{}'),
                    'name' => $this->string(255)->notNull()->defaultValue('New Destination'),
                    'errorMsg' => $this->string(1023)->defaultValue(null),
                    'enabled' => $this->boolean()->defaultValue(true),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listbuilder_signup_destination}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%listbuilder_signup_destination}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'signupId' => $this->integer()->notNull(),
                    'destinationId' => $this->integer()->notNull(),
                    'success' => $this->boolean()->notNull(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        //Unique index on email
        $this->createIndex(
            $this->db->getIndexName(
                '{{%listbuilder_signup}}',
                'email',
                true
            ),
            '{{%listbuilder_signup}}',
            'email',
            true
        );

        //Index on date created for filtering
        $this->createIndex(
            $this->db->getIndexName(
                '{{%listbuilder_signup}}',
                'dateCreated',
                false
            ),
            '{{%listbuilder_signup}}',
            'dateCreated',
            false
        );

        //Index on every source's shortcode + unique
        $this->createIndex(
            $this->db->getIndexName(
                '{{%listbuilder_source}}',
                'shortcode',
                true
            ),
            '{{%listbuilder_source}}',
            'shortcode',
            true
        );
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        //Reference source from signup, don't delete on source deletion
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listbuilder_signup}}', 'sourceId'),
            '{{%listbuilder_signup}}',
            'sourceId',
            '{{%listbuilder_source}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listbuilder_signup_destination}}', 'signupId'),
            '{{%listbuilder_signup_destination}}',
            'signupId',
            '{{%listbuilder_signup}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listbuilder_signup_destination}}', 'destinationId'),
            '{{%listbuilder_signup_destination}}',
            'destinationId',
            '{{%listbuilder_destination}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%listbuilder_signup_destination}}');
        $this->dropTableIfExists('{{%listbuilder_signup}}');
        $this->dropTableIfExists('{{%listbuilder_source}}');
        $this->dropTableIfExists('{{%listbuilder_destination}}');
    }
}
