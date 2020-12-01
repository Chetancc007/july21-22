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

namespace Mageplaza\SaveCart\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Cart
 * @package Mageplaza\SaveCart\Model
 * @method getStoreId()
 * @method getId()
 * @method getItems()
 * @method setCartName(string $cartName)
 * @method setDescription(string $description)
 * @method getDescription()
 * @method getCreatedAt()
 * @method getCartName()
 * @method getCustomerId()
 * @method getCartId()
 * @method getToken()
 */
class Cart extends AbstractModel
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Cart::class);
    }
}
