<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var Setup\SampleData\Executor
     */
    private $executor;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Setup\SampleData\Executor $executor,
        Installer $installer
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->executor = $executor;
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->executor->exec($this->installer);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'am_shipping_type',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Shipping Type',
                'input' => 'select',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }
}
