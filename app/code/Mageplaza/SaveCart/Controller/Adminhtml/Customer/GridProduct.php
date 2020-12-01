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
use Mageplaza\SaveCart\Model\Product;
use Mageplaza\SaveCart\Model\ResourceModel\Product as ProductResource;

/**
 * Class Grid
 * @package Mageplaza\GiftCard\Controller\Adminhtml\Customer
 */
class GridProduct extends Index
{
    /**
     * Execute
     *
     * @return Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();

        $productModel    = $this->_objectManager->get(Product::class);
        $productResource = $this->_objectManager->get(ProductResource::class);

        if ($deleteId = $this->getRequest()->getParam('delete')) {
            $product = $productModel->load($deleteId);
            $productResource->delete($product);
        }

        return $this->resultLayoutFactory->create();
    }
}
