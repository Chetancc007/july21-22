<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Model\ResourceModel;

/**
 * Class Cart
 * @package Mageplaza\SaveCart\Model\ResourceModel
 */
class Cart extends AbstractResource
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_saved_cart', 'cart_id');
    }
}
