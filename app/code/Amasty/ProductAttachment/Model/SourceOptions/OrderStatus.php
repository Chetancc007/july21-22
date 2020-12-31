<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Model\SourceOptions;

class OrderStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    public function toOptionArray()
    {
        //remove Please Select option
        return array_slice(parent::toOptionArray(), 1);
    }
}
