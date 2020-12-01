<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\SaveCart\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //Create cart table
        if (!$installer->tableExists('mageplaza_saved_cart')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_saved_cart'))
                ->addColumn('cart_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Cart Id')
                ->addColumn('cart_name', Table::TYPE_TEXT, 255, [], 'Cart Name')
                ->addColumn('description', Table::TYPE_TEXT, 255, [], 'Description')
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Customer Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Store ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_saved_cart',
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_saved_cart', 'store_id', 'store_', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_SET_NULL
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_saved_cart', 'customer_id'),
                    'customer_id'
                )->setComment('Saved Cart Table');

            $installer->getConnection()->createTable($table);
        }

        //Create cart item table
        if (!$installer->tableExists('mageplaza_saved_cart_item')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_saved_cart_item'))
                ->addColumn('cart_item_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Cart Item Id')
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn('cart_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Cart Id')
                ->addColumn('qty', Table::TYPE_INTEGER, null, [], 'Qty')
                ->addColumn('buy_request', Table::TYPE_TEXT, '2M', [], 'Buy Request')
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Store ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_saved_cart_item',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_saved_cart_item',
                        'cart_id',
                        'mageplaza_saved_cart',
                        'cart_id'
                    ),
                    'cart_id',
                    $installer->getTable('mageplaza_saved_cart'),
                    'cart_id',
                    Table::ACTION_CASCADE
                )->addIndex(
                    $installer->getIdxName('mageplaza_saved_cart_item', 'store_id'),
                    'store_id'
                )->setComment('Saved Cart Item Table');

            $installer->getConnection()->createTable($table);
        }

        //Create product table
        if (!$installer->tableExists('mageplaza_saved_product')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_saved_product'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Id')
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Customer Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Store ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn('qty', Table::TYPE_INTEGER, null, [], 'Qty')
                ->addColumn('buy_request', Table::TYPE_TEXT, '2M', [], 'Buy Request')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_saved_product',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->addForeignKey(
                    $installer->getFkName('mageplaza_saved_product', 'store_id', 'store_', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_SET_NULL
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_saved_product', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_saved_product', 'customer_id'),
                    'customer_id'
                )->setComment('Saved Product Table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
