<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Model\Cart;

class ShippingMethod extends \Magento\Quote\Model\Cart\ShippingMethod implements
    \Amasty\StorePickup\Api\Data\ShippingMethodInterface
{
    public function setComment($comment)
    {
        return $this->setData('comment', $comment);
    }

    public function getComment()
    {
        return $this->_get('comment');
    }
}
