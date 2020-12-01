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

namespace Mageplaza\SaveCart\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\View\Result\Layout;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;

/**
 * Class Grid
 * @package Mageplaza\GiftCard\Controller\Adminhtml\Customer
 */
class GridCart extends Index
{
    /**
     * Execute
     *
     * @return Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();

        $cartModel    = $this->_objectManager->get(Cart::class);
        $cartResource = $this->_objectManager->get(CartResource::class);

        if ($deleteId = $this->getRequest()->getParam('delete')) {
            $product = $cartModel->load($deleteId);
            $cartResource->delete($product);
        }

        return $this->resultLayoutFactory->create();
    }
}
