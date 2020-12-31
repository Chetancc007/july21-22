<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrand\Setup;

use Amasty\ShopbyBrand\Setup\Operation\AddShowInBrandSlider;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var AddShowInBrandSlider
     */
    private $addShowInBrandSlider;

    public function __construct(AddShowInBrandSlider $addShowInBrandSlider)
    {
        $this->addShowInBrandSlider = $addShowInBrandSlider;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.10.12', '<')) {
            $this->addShowInBrandSlider->execute($setup);
        }

        $setup->endSetup();
    }
}
