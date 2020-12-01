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

namespace Mageplaza\SaveCart\Controller\Index;

use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\CartItem;
use Mageplaza\SaveCart\Model\CartItemFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\CollectionFactory as CartCollection;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem as CartItemResource;

/**
 * Class Fromcart
 * @package Mageplaza\SaveCart\Controller\Index
 */
class SaveCart extends Action
{
    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CartResource
     */
    protected $cartResource;

    /**
     * @var CartFactory
     */
    protected $cartFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CartCollection
     */
    protected $cartCollection;

    /**
     * @var CartItemFactory
     */
    protected $cartItem;

    /**
     * @var CartItemResource
     */
    protected $cartItemResource;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * SaveCart constructor.
     *
     * @param Context $context
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param CartFactory $cartFactory
     * @param CartResource $cartResource
     * @param Session $session
     * @param CartCollection $cartCollection
     * @param CartItemFactory $cartItem
     * @param CartItemResource $cartItemResource
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        CartFactory $cartFactory,
        CartResource $cartResource,
        Session $session,
        CartCollection $cartCollection,
        CartItemFactory $cartItem,
        CartItemResource $cartItemResource,
        Validator $formKeyValidator
    ) {
        $this->cart             = $cart;
        $this->cartHelper       = $cartHelper;
        $this->escaper          = $escaper;
        $this->cartResource     = $cartResource;
        $this->cartFactory      = $cartFactory;
        $this->session          = $session;
        $this->cartCollection   = $cartCollection;
        $this->cartItem         = $cartItem;
        $this->cartItemResource = $cartItemResource;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/');
        }

        $cartName     = $this->getRequest()->getParam('mpsavecart-name');
        $description  = $this->getRequest()->getParam('mpsavecart-description');
        $quoteSession = $this->session->getQuote();
        $customerId   = $quoteSession->getCustomerId();

        /** @var Collection $carts */
        $carts         = $this->cartCollection->create();
        $cartAvailable = $carts->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('cart_name', $cartName);

        if (count($cartAvailable)) {
            $this->messageManager->addWarningMessage(__('Cart Name has already existed'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;
        }

        $allItems = $this->cart->getQuote()->getAllItems();
        $storeId  = $quoteSession->getStoreId();

        /** @var Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setCartName($cartName)
            ->setDescription($description)
            ->setCustomerId($customerId)
            ->setStoreId($storeId);

        $this->cartResource->save($cart);
        $cartId = $cart->getCartId();

        /** @var Item $item */
        foreach ($allItems as $item) {
            if (!empty($item->getParentItemId())) {
                continue;
            }
            /** @var CartItem $cartItem */
            $cartItem = $this->cartItem->create();
            $cartItem->setQty($item->getQty())
                ->setProductId($item->getProductId())
                ->setBuyRequest(Data::jsonEncode($item->getBuyRequest()))
                ->setStoreId($storeId)
                ->setCartId($cartId);

            $this->cartItemResource->save($cartItem);
            $this->cart->getQuote()->removeItem($item->getId());
        }

        /** @var Cart $cart */

        $this->cart->save();

        return $this->_redirect('*/dashboard/');
    }
}
