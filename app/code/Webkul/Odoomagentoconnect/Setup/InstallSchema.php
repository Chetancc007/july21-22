<?php
/**
 * Webkul Odoomagentoconnect Schema Setup
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Setup;

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
        $context = $context;

        $installer->startSetup();

        /**
         * Create table 'catalog_product_bundle_option'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_category'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Odoo Category Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Category Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'need_sync',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'no'],
                'Need Sync'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_category', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_category', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_category', ['need_sync']),
                ['need_sync']
            );

        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_product'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Product Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Product Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'need_sync',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'no'],
                'Need Sync'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_product', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_product', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_product', ['need_sync']),
                ['need_sync']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_template'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Template Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Template Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'need_sync',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'no'],
                'Need Sync'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_template', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_template', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_template', ['need_sync']),
                ['need_sync']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_set'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Name'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Set Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Set Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_set', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_set', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_set', ['name']),
                ['name']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_attribute'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Name'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Attribute Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Attribute Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_attribute', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_attribute', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_attribute', ['name']),
                ['name']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_option'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Name'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Option Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Option Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_option', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_option', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_option', ['name']),
                ['name']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_customer'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Customer Id'
            )
            ->addColumn(
                'address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Address Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Customer Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'need_sync',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'no'],
                'Need Sync'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_customer', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_customer', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_customer', ['address_id']),
                ['address_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_customer', ['need_sync']),
                ['need_sync']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_currency'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Magento Currency Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Currency Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_currency', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_currency', ['magento_id']),
                ['magento_id']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_order'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Magento Order'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Order Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Order Id'
            )
            ->addColumn(
                'odoo_line_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Odoo Order Line'
            )
            ->addColumn(
                'odoo_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Customer Id'
            )
            ->addColumn(
                'odoo_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Odoo Order'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_order', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_order', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_order', ['magento_order']),
                ['magento_order']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_order', ['odoo_order']),
                ['odoo_order']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_order', ['odoo_customer_id']),
                ['odoo_customer_id']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_tax'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Magento Tax Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Tax Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'need_sync',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'no'],
                'Need Sync'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_tax', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_tax', ['magento_id']),
                ['magento_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_tax', ['need_sync']),
                ['need_sync']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_payment'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'magento_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Magento Payment Id'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Payment Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_payment', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_payment', ['magento_id']),
                ['magento_id']
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('odoomagentoconnect_carrier'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'carrier_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Carrier Name'
            )
            ->addColumn(
                'carrier_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Carrier Code'
            )
            ->addColumn(
                'odoo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Odoo Id'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => null],
                'Created By'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_carrier', ['odoo_id']),
                ['odoo_id']
            )
            ->addIndex(
                $installer->getIdxName('odoomagentoconnect_carrier', ['carrier_code']),
                ['carrier_code']
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
