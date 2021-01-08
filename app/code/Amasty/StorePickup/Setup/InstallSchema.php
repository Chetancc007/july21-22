<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $tableMethod = $installer->getConnection()
            ->newTable($installer->getTable('amasty_storepick_method'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                8,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Is Active'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Name'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Comment'
            )
            ->addColumn(
                'comment_img',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Comment Image'
            )
            ->addColumn(
                'stores',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stores'
            )
            ->addColumn(
                'cust_groups',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Groups'
            )
            ->addColumn(
                'select_rate',
                Table::TYPE_SMALLINT,
                2,
                ['nullable' => false, 'unsigned' => true, 'default' => 0],
                'Select Rate'
            )
            ->addColumn(
                'min_rate',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Min Rate'
            )
            ->addColumn(
                'max_rate',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Max Rate'
            );

        $tableRate = $installer->getConnection()
            ->newTable($installer->getTable('amasty_storepick_rate'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'method_id',
                Table::TYPE_INTEGER,
                8,
                ['unsigned' => true, 'nullable' => false],
                'Method ID'
            )
            ->addColumn(
                'country',
                Table::TYPE_TEXT,
                4,
                ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'nullable' => false],
                'Country'
            )
            ->addColumn(
                'state',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'State'
            )
            ->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'City'
            )
            ->addColumn(
                'zip_from',
                Table::TYPE_TEXT,
                10,
                ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'nullable' => false],
                'ZIP From'
            )
            ->addColumn(
                'zip_to',
                Table::TYPE_TEXT,
                10,
                ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'nullable' => false],
                'ZIP To'
            )
            ->addColumn(
                'price_from',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Price From'
            )
            ->addColumn(
                'price_to',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Price to'
            )
            ->addColumn(
                'weight_from',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 0000],
                'Weight From'
            )
            ->addColumn(
                'weight_to',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 0000],
                'Weight to'
            )
            ->addColumn(
                'qty_from',
                Table::TYPE_DECIMAL,
                "12,2",
                ['unsigned' => true, 'nullable' => false, 'default' => '0.00'],
                'QTY From'
            )
            ->addColumn(
                'qty_to',
                Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.00'],
                'QTY To'
            )
            ->addColumn(
                'shipping_type',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Shipping Type'
            )
            ->addColumn(
                'cost_base',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Cost Base'
            )
            ->addColumn(
                'cost_percent',
                Table::TYPE_DECIMAL,
                '5,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Cost Percent'
            )
            ->addColumn(
                'cost_product',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Cost Product'
            )
            ->addColumn(
                'cost_weight',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'unsigned' => true, 'default' => 0, 00],
                'Cost Weight'
            )
            ->addColumn(
                'time_delivery',
                Table::TYPE_TEXT,
                255,
                ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'default' => null],
                'Time Delivery'
            )
            ->addColumn(
                'num_zip_from',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'default' => null],
                'Num Zip To'
            )
            ->addColumn(
                'num_zip_to',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'default' => null],
                'Num Zip  To'
            )
            ->addIndex('idx_amasty_storepick_rate_method_id', 'method_id')
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_storepick_rate',
                    'method_id',
                    'amasty_storepick_method',
                    'id'
                ),
                'method_id',
                $installer->getTable('amasty_storepick_method'),
                'id',
                Table::ACTION_CASCADE
            );

        $tableLabel = $installer->getConnection()
            ->newTable($setup->getTable('amasty_storepick_method_label'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'method_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Method Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Entity Id'
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                '255',
                ['nullable' => true, 'default' => null],
                'Label'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                '255',
                ['nullable' => true, 'default' => null],
                'Comment'
            )
            ->addForeignKey(
                $setup->getFkName('amasty_storepick_method_label', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName('amasty_storepick_method_label', 'method_id', 'amasty_storepick_method', 'id'),
                'method_id',
                $setup->getTable('amasty_storepick_method'),
                'id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($tableMethod);
        $installer->getConnection()->createTable($tableRate);
        $installer->getConnection()->createTable($tableLabel);
        $installer->endSetup();
    }
}
