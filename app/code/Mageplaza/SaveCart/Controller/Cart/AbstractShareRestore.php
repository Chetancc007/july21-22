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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\DecoderInterface;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\CartItem;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollectionFactory;
use RuntimeException;

/**
 * Class ShareRestore
 * @package Mageplaza\SaveCart\Controller\Product
 */
abstract class AbstractShareRestore extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CartItemCollectionFactory
     */
    protected $cartItemCollection;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var CartFactory
     */
    protected $cart;

    /**
     * @var CartResource
     */
    protected $cartResource;

    /**
     * AbstractShareRestore constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param CheckoutCart $checkoutCart
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemCollectionFactory $cartItemCollection
     * @param DecoderInterface $urlDecoder
     * @param CartFactory $cart
     * @param CartResource $cartResource
     */
    public function __construct(
        Context $context,
        Data $helper,
        CheckoutCart $checkoutCart,
        ProductRepositoryInterface $productRepository,
        CartItemCollectionFactory $cartItemCollection,
        DecoderInterface $urlDecoder,
        CartFactory $cart,
        CartResource $cartResource
    ) {
        $this->helper             = $helper;
        $this->checkoutCart       = $checkoutCart;
        $this->productRepository  = $productRepository;
        $this->cartItemCollection = $cartItemCollection;
        $this->urlDecoder         = $urlDecoder;
        $this->cart               = $cart;
        $this->cartResource       = $cartResource;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $cart  = $this->helper->getCart();
        $items = $this->cartItemCollection->create()->addFieldToFilter('cart_id', $cart->getCartId());
        /** @var CartItem $item */
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $this->productRepository->getById($item->getProductId(), false, $item->getStoreId(), true);
            try {
                $infoBuyRequest = Data::jsonDecode($item['buy_request']);
                $this->checkoutCart->addProduct($product, $infoBuyRequest);
            } catch (LocalizedException $e) {
                throw new RuntimeException(__('Cannot add to cart'));
            }
        }
        $this->checkoutCart->save();
    }
}
