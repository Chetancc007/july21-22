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

namespace Mageplaza\SaveCart\Block\Account\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollectionFactory;

/**
 * Class Dashboard
 * @package Mageplaza\SaveCart\Block\Account
 */
class View extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CartFactory
     */
    protected $cartModel;

    /**
     * @var CartResource
     */
    protected $cartResource;

    /**
     * @var CartItemCollectionFactory
     */
    protected $cartItemCollection;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * View constructor.
     *
     * @param CartFactory $cartModel
     * @param CartResource $cartResource
     * @param Data $helper
     * @param CartItemCollectionFactory $cartItemCollection
     * @param EncoderInterface $urlEncoder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CartFactory $cartModel,
        CartResource $cartResource,
        Data $helper,
        CartItemCollectionFactory $cartItemCollection,
        EncoderInterface $urlEncoder,
        Template\Context $context,
        array $data = []
    ) {
        $this->cartModel          = $cartModel;
        $this->cartResource       = $cartResource;
        $this->helper             = $helper;
        $this->cartItemCollection = $cartItemCollection;
        $this->urlEncoder         = $urlEncoder;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCartDescription()
    {
        return $this->helper->getCart()->getDescription();
    }

    /**
     * @return mixed
     */
    public function getCartCreated()
    {
        return $this->helper->getCart()->getCreatedAt();
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        $cartItems = $this->cartItemCollection->create()->addFieldToFilter(
            'cart_id',
            $this->helper->getCart()->getCartId()
        );

        return $cartItems;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->helper->getCart()->getStoreId();
    }

    /**
     * @param $item
     *
     * @return string|null
     */
    public function getProductName($item)
    {
        $productName = '';
        try {
            $productName = $this->helper->getProductName($item, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $productName;
    }

    /**
     * @param $item
     *
     * @return float|string
     */
    public function getPrice($item)
    {
        $price = '';
        try {
            return $this->helper->getPrice($item, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $price;
    }

    /**
     * @param $item
     *
     * @return float|string
     */
    public function getSubtotalConverted($item)
    {
        return $this->helper->getSubtotalConverted($item, $this->getStoreId());
    }

    /**
     * @return float|string
     */
    public function getCartTotal()
    {
        return $this->helper->getCartTotal($this->helper->getCart());
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getSku($item)
    {
        $sku = '';
        try {
            return $this->helper->getSku($item, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $sku;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getImage($item)
    {
        return $this->helper->getImage($item, $this->getStoreId());
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('mpsavecart/dashboard/index');
    }

    /**
     * @return string
     */
    public function getRestoreUrl()
    {
        return $this->getUrl(
            'mpsavecart/cart/restore',
            [
                'id' => $this->helper->getToken()
            ]
        );
    }
}
