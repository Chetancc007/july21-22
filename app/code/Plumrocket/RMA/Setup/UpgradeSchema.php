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
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $setup->getConnection();

        /**
         * Version 2.0.5
         */
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection->addIndex(
                $setup->getTable('plumrocket_rma_returns'),
                $setup->getIdxName($setup->getTable('plumrocket_rma_returns'), ['increment_id', 'status']),
                ['increment_id', 'status'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        $setup->endSetup();
    }
}