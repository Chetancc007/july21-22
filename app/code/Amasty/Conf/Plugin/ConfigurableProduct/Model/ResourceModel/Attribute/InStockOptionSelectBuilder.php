<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\ConfigurableProduct\Model\ResourceModel\Attribute;

use Amasty\Conf\Model\ConfigProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\InStockOptionSelectBuilder as NativeBuilder;

class InStockOptionSelectBuilder
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Disable Magento stock filter
     *
     * @param NativeBuilder $nativeSubject
     * @param \Closure $proceed
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     */
    public function aroundAfterGetSelect(
        NativeBuilder $nativeSubject,
        \Closure $proceed,
        OptionSelectBuilderInterface $subject,
        Select $select
    ) {
        if (!$this->configProvider->showOutOfStockConfigurableAttributes()) {
            $select = $proceed($subject, $select);
        }

        return $select;
    }
}
