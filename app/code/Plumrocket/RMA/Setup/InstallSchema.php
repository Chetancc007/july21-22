<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Install text table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_text'))
            ->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'entity_type_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity Type Id'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Label'
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_text', ['entity_type_id']),
                ['entity_type_id']
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_text', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'plumrocket_rma_text',
                    ['entity_id', 'entity_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'entity_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName('plumrocket_rma_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Plumrocket RMA Texts');
        $installer->getConnection()->createTable($table);

        /**
         * Install store table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_store'))
            ->addColumn(
                'entity_type_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity Type Id'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_store', ['entity_type_id']),
                ['entity_type_id']
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_store', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'plumrocket_rma_store',
                    ['entity_id', 'entity_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'entity_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName('plumrocket_rma_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Plumrocket RMA Store');
        $installer->getConnection()->createTable($table);

        /**
         * Creating reason table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_reason'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'payer',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Payer id'
            )
            ->addColumn(
                'position',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_reason', ['payer']),
                ['payer']
            )
            /*
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_reason', ['status']),
                ['status']
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_reason', ['position']),
                ['position']
            )
            */
            ->setComment('Plumrocket RMA Reason');
        $installer->getConnection()->createTable($table);

        /**
         * Creating condition table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_condition'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'position',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            /*
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_condition', ['status']),
                ['status']
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_condition', ['position']),
                ['position']
            )
            */
            ->setComment('Plumrocket RMA Condition');
        $installer->getConnection()->createTable($table);

        /**
         * Creating resolution table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_resolution'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'position',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            /*
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_resolution', ['status']),
                ['status']
            )
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_resolution', ['position']),
                ['position']
            )
            */
            ->setComment('Plumrocket RMA Resolution');
        $installer->getConnection()->createTable($table);

        /**
         * Creating return rule table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_return_rule'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'website_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_TEXT,
                50,
                ['unsigned' => true, 'nullable' => false],
                'Customer Group'
            )
            ->addColumn(
                'priority',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            ->addColumn(
                'resolution',
                Table::TYPE_TEXT,
                 5000,
                 ['nullable'  => false],
                 'Resolutions in json'
             )
            ->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                 5000,
                 ['nullable'  => false],
                 'Conditions Serialized'
             )
            /*
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_return_rule', ['status']),
                ['status']
            )
            */
            ->addIndex(
                $installer->getIdxName('plumrocket_rma_return_rule', ['priority']),
                ['priority']
            )
            ->setComment('Plumrocket RMA Return Rules');
        $installer->getConnection()->createTable($table);

        /**
         * Creating quick response template table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_response_template'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                5000,
                [],
                'Message'
            )
            ->setComment('Plumrocket RMA Quick Response Template');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_rma_returns'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_returns'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'increment_id',
                Table::TYPE_TEXT,
                32,
                [],
                'Increment Id'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order Id'
            )
            ->addColumn(
                'manager_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Manager Id'
            )
            ->addColumn(
                'is_closed',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => false],
                'System Post'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'shipping_label',
                Table::TYPE_TEXT,
                200,
                [],
                'RMA Shipping Label'
            )
            ->addColumn(
                'note',
                Table::TYPE_TEXT,
                5000,
                [],
                'RMA Note'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                50,
                [],
                'RMA Code'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Date of creation'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Date of modification'
            )
            ->addColumn(
                'read_mark_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => null],
                'Read Mark Time'
            )
            ->setComment('Plumrocket RMA Returns');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_rma_returns_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plumrocket_rma_returns_address')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Parent Id'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Order Id'
        )->addColumn(
            'region_id',
            Table::TYPE_INTEGER,
            null,
            [],
            'Region Id'
        )->addColumn(
            'fax',
            Table::TYPE_TEXT,
            255,
            [],
            'Fax'
        )->addColumn(
            'region',
            Table::TYPE_TEXT,
            255,
            [],
            'Region'
        )->addColumn(
            'postcode',
            Table::TYPE_TEXT,
            255,
            [],
            'Postcode'
        )->addColumn(
            'lastname',
            Table::TYPE_TEXT,
            255,
            [],
            'Lastname'
        )->addColumn(
            'street',
            Table::TYPE_TEXT,
            255,
            [],
            'Street'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            255,
            [],
            'City'
        )->addColumn(
            'telephone',
            Table::TYPE_TEXT,
            255,
            [],
            'Phone Number'
        )->addColumn(
            'country_id',
            Table::TYPE_TEXT,
            2,
            [],
            'Country Id'
        )->addColumn(
            'firstname',
            Table::TYPE_TEXT,
            255,
            [],
            'Firstname'
        )->addColumn(
            'prefix',
            Table::TYPE_TEXT,
            255,
            [],
            'Prefix'
        )->addColumn(
            'middlename',
            Table::TYPE_TEXT,
            255,
            [],
            'Middlename'
        )->addColumn(
            'suffix',
            Table::TYPE_TEXT,
            255,
            [],
            'Suffix'
        )->addColumn(
            'company',
            Table::TYPE_TEXT,
            255,
            [],
            'Company'
        )/*->addIndex(
            $installer->getIdxName('plumrocket_rma_returns_address', ['parent_id']),
            ['parent_id']
        )*/->addIndex(
            $installer->getIdxName(
                'plumrocket_rma_returns_address',
                ['parent_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['parent_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('plumrocket_rma_returns_address', 'parent_id', 'plumrocket_rma_returns', 'entity_id'),
            'parent_id',
            $installer->getTable('plumrocket_rma_returns'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Plumrocket RMA Returns Address'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_rma_returns_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plumrocket_rma_returns_item')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'order_item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Item Id'
        )->addColumn(
            'reason_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Return Reason'
        )->addColumn(
            'condition_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Item Condition'
        )->addColumn(
            'resolution_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Resolution'
        )->addColumn(
            'qty_purchased',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Qty Purchased'
        )->addColumn(
            'qty_requested',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Qty Requested'
        )->addColumn(
            'qty_authorized',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Qty Authorized'
        )->addColumn(
            'qty_received',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Qty Received'
        )->addColumn(
            'qty_approved',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Qty Approved'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('plumrocket_rma_returns_item', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('plumrocket_rma_returns_item', 'parent_id', 'plumrocket_rma_returns', 'entity_id'),
            'parent_id',
            $installer->getTable('plumrocket_rma_returns'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Plumrocket RMA Returns Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_rma_returns_track'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('plumrocket_rma_returns_track')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            30,
            [],
            'Type'
        )->addColumn(
            'carrier_code',
            Table::TYPE_TEXT,
            32,
            [],
            'Carrier Code'
        )->addColumn(
            'track_number',
            Table::TYPE_TEXT,
            250,
            [],
            'Number'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('plumrocket_rma_returns_track', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('plumrocket_rma_returns_track', 'parent_id', 'plumrocket_rma_returns', 'entity_id'),
            'parent_id',
            $installer->getTable('plumrocket_rma_returns'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Plumrocket RMA Returns Track'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'plumrocket_rma_returns_message'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('plumrocket_rma_returns_message'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'parent_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Parent Id'
            )
            ->addColumn(
                'from_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'From Id'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                30,
                [],
                'Type'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                100,
                [],
                'Name'
            )
            ->addColumn(
                'text',
                Table::TYPE_TEXT,
                5000,
                [],
                'Text'
            )
            ->addColumn(
                'files',
                Table::TYPE_TEXT,
                500,
                ['nullable' => true],
                'File'
            )
            ->addColumn(
                'is_system',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'System Post'
            )
            ->addColumn(
                'is_internal',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Internal Post'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Date of creation'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Date of modification'
            )
            ->setComment('Plumrocket RMA Returns Message');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
