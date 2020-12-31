<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Amasty\Xsearch\Setup\Operation\ChangeFAQEnableFieldValue;

class UpgradeData implements UpgradeDataInterface
{
    const AMASTY_XSEARCH_SEARCH_ATTRIBUTES_ATTRIBUTES = 'amasty_xsearch/search_attributes/attributes';

    /**
     * @var ChangeFAQEnableFieldValue
     */
    private $faqChange;

    /**
     * @param ChangeFAQEnableFieldValue $faqChange
     */
    public function __construct(
        ChangeFAQEnableFieldValue $faqChange
    ) {
        $this->faqChange = $faqChange;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->startSetup();

            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => self::AMASTY_XSEARCH_SEARCH_ATTRIBUTES_ATTRIBUTES,
                'value' => null
            ];

            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.14.6', '<')) {
            $this->faqChange->execute($setup);
        }
    }
}
