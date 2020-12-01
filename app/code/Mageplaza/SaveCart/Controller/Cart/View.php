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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;

/**
 * Class View
 * @package Mageplaza\SaveCart\Controller\Cart
 */
class View extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CartResource
     */
    protected $cartResource;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CartFactory
     */
    protected $cartModel;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param PageFactory $resultPageFactory
     * @param CartFactory $cartModel
     * @param CartResource $cartResource
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Data $helper,
        PageFactory $resultPageFactory,
        CartFactory $cartModel,
        CartResource $cartResource,
        Registry $registry
    ) {
        $this->helper            = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartResource      = $cartResource;
        $this->registry          = $registry;
        $this->cartModel         = $cartModel;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->helper->isEnabled()) {
            throw new NotFoundException(__('Save Cart is turned off.'));
        }

        /** @var Cart $cart */
        $cart = $this->helper->getCart();
        if (!$cart->getId() || $this->helper->checkCustomer($cart)) {
            return $this->_redirect('customer/account');
        }

        $resultPage = $this->resultPageFactory->create();
        /** @var Cart $cart */
        $cart = $this->cartModel->create();
        $this->cartResource->load($cart, $this->helper->getToken(), 'token');

        /** @var Page $resultPage */
        $resultPage->getConfig()->getTitle()->set($cart->getCartName());

        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');

        if ($navigationBlock) {
            $navigationBlock->setActive('mpsavecart/dashboard');
        }

        return $resultPage;
    }
}
