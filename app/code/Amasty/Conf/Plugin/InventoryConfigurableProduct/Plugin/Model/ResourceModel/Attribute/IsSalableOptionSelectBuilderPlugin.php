<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Amasty\Conf\Model\ConfigProvider;
use Magento\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute\IsSalableOptionSelectBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;

class IsSalableOptionSelectBuilderPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param IsSalableOptionSelectBuilder $subject
     * @param \Closure $proceed
     * @param OptionSelectBuilderInterface $origSubject
     * @param Select $select
     *
     * @return Select
     */
    public function aroundAfterGetSelect(
        IsSalableOptionSelectBuilder $subject,
        \Closure $proceed,
        OptionSelectBuilderInterface $origSubject,
        Select $select
    ) {
        if (!$this->configProvider->showOutOfStockConfigurableAttributes()) {
            $select = $proceed($origSubject, $select);
        }

        return $select;
    }
}
