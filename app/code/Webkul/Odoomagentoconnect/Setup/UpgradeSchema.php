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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.2.1', '<')) {
            $column = [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Tax Name',
            ];
            $connection->addColumn($setup->getTable('odoomagentoconnect_tax'), 'code', $column);
        }
        if (version_compare($context->getVersion(), '2.2.2', '<')) {
            $column = [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
            ];
            $columnEntityId = [
                'identity' => true,
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ];

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_product'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_product'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_product'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_template'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_template'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_template'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_set'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_set'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_set'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_attribute'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_attribute'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_attribute'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_option'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_option'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_option'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_customer'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_customer'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_customer'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_currency'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_currency'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_order'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_order'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_order'), 'odoo_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_order'), 'odoo_customer_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_tax'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_tax'), 'magento_id', $column);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_tax'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_payment'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_payment'), 'odoo_id', $column);

            $connection->modifyColumn($setup->getTable('odoomagentoconnect_carrier'), 'entity_id', $columnEntityId);
            $connection->modifyColumn($setup->getTable('odoomagentoconnect_carrier'), 'odoo_id', $column);
        }
    }
}
