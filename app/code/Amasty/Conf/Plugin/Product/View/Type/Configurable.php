<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Product\View\Type;

use Amasty\Conf\Helper\Data;
use Amasty\Conf\Model\ConfigurableConfigGetter;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;

class Configurable
{
    /**
     * @var ConfigurableConfigGetter
     */
    private $configGetter;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        ConfigurableConfigGetter $configGetter,
        Data $helper
    ) {
        $this->configGetter = $configGetter;
        $this->helper = $helper;
    }

    /**
     * @param TypeConfigurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(
        TypeConfigurable $subject,
        $result
    ) {
        if ($result) {
            $result = array_merge($this->helper->decode($result), $this->configGetter->execute($subject));
            $result = $this->helper->encode($result);
        }

        return $result;
    }
}
