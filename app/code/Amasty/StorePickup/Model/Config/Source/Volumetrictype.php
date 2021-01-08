<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Amasty\StorePickup\Helper\Config as HelperConfig;

class Volumetrictype implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            HelperConfig::VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE => __('Volumetric weight attribute'),
            HelperConfig::VOLUMETRIC_ATTRIBUTE_TYPE => __ ('Volume attribute'),
            HelperConfig::VOLUMETRIC_DIMENSIONS_ATTRIBUTE => __('Dimensions attribute'),
            HelperConfig::VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE => __('Separate dimension attribute')
        ];
    }
}
