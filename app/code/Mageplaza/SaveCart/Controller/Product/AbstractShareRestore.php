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

namespace Mageplaza\SaveCart\Controller\Product;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\DecoderInterface;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\ProductFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Product as ProductResource;
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
     * @var ProductFactory
     */
    protected $productModel;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * AbstractShareRestore constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param ProductFactory $productModel
     * @param ProductResource $productResource
     * @param Cart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param DecoderInterface $urlDecoder
     */
    public function __construct(
        Context $context,
        Data $helper,
        ProductFactory $productModel,
        ProductResource $productResource,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        DecoderInterface $urlDecoder
    ) {
        $this->helper            = $helper;
        $this->productModel      = $productModel;
        $this->productResource   = $productResource;
        $this->cart              = $cart;
        $this->productRepository = $productRepository;
        $this->urlDecoder        = $urlDecoder;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        /** @var \Mageplaza\SaveCart\Model\Product $productSaved */
        $productSaved = $this->helper->getSavedProduct();
        if ($productSaved->getId()) {
            $buyRequest = Data::jsonDecode($productSaved->getBuyRequest());
            /** @var Product $product */
            $product = $this->productRepository->getById(
                $productSaved->getProductId(),
                false,
                $productSaved->getStoreId(),
                true
            );
            try {
                $this->cart->addProduct($product, $buyRequest)->save();
            } catch (LocalizedException $e) {
                throw new RuntimeException(__('Cannot add this product to cart'));
            }
        }
    }
}
