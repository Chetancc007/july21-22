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

use Exception;
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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Product;
use Mageplaza\SaveCart\Model\ProductFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Product as ProductResource;

/**
 * Class Fromcart
 * @package Mageplaza\SaveCart\Controller\Index
 */
class Fromcart extends Action
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
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Fromcart constructor.
     *
     * @param Context $context
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     * @param ProductFactory $productFactory
     * @param ProductResource $productResource
     * @param Session $session
     */
    public function __construct(
        Context $context,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        Validator $formKeyValidator,
        ProductFactory $productFactory,
        ProductResource $productResource,
        Session $session
    ) {
        $this->cart             = $cart;
        $this->cartHelper       = $cartHelper;
        $this->escaper          = $escaper;
        $this->formKeyValidator = $formKeyValidator;
        $this->productResource  = $productResource;
        $this->productFactory   = $productFactory;
        $this->session          = $session;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $itemId = (int) $this->getRequest()->getParam('item');
            /** @var Item $item */
            $item = $this->cart->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new LocalizedException(
                    __('The requested cart item doesn\'t exist.')
                );
            }

            $quote      = $this->session->getQuote();
            $customerId = $quote->getCustomerId();
            $storeId    = $quote->getStoreId();

            $productId  = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $productQty = $item->getQty();

            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->setProductId($productId)
                ->setQty($productQty)
                ->setCustomerId($customerId)
                ->setStoreId($storeId)
                ->setBuyRequest(Data::jsonEncode($buyRequest->getData()));

            $this->productResource->save($product);

            $this->cart->getQuote()->removeItem($itemId);
            $this->cart->save();

            $this->messageManager->addSuccessMessage(__(
                '%1 has been moved to your Saved Products List.',
                $this->escaper->escapeHtml($item->getProduct()->getName())
            ));

            $items = $this->cart->getQuote()->getAllItems();
            if (!count($items)) {
                return $this->_redirect('*/dashboard/');
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('This item cannot be moved to your Saved Products List.')
            );
        }

        return $resultRedirect->setUrl($this->cartHelper->getCartUrl());
    }
}
