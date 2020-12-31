<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $tablesToDrop = [
            $setup->getTable('amasty_xsearch_related_term'),
            $setup->getTable('amasty_xsearch_users_search')
        ];
        $tablesToDrop = array_merge($tablesToDrop, $this->getScopeTables($installer));
        $installer->startSetup();

        foreach ($tablesToDrop as $table) {
            $installer->getConnection()->dropTable($table);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return array
     */
    private function getScopeTables(SchemaSetupInterface $setup)
    {
        return $setup->getConnection()->getTables(
            '%' . \Amasty\Xsearch\Model\Indexer\Category\Fulltext::INDEXER_ID . '%'
        );
    }
}
