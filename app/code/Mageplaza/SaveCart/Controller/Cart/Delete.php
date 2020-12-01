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

namespace Mageplaza\SaveCart\Controller\Cart;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class Delete
 * @package Mageplaza\SaveCart\Controller\Product
 */
class Delete extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        if ($this->helper->checkCustomer($this->helper->getCart())) {
            return $this->_redirect('customer/account');
        }

        $this->helper->deleteCart();
        $this->messageManager->addSuccessMessage(__('Delete Successfully'));

        return $this->_redirect('*/dashboard/');
    }
}
