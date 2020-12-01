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

namespace Mageplaza\SaveCart\Block\Account\Dashboard;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\SaveCart\Block\Account\Dashboard;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\CollectionFactory;

/**
 * Class Products
 * @package Mageplaza\SaveCart\Block\Account\Dashboard
 */
class Carts extends Dashboard
{
    /**
     * @var CollectionFactory
     */
    protected $cartCollection;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Collection
     */
    protected $carts;

    /**
     * Carts constructor.
     *
     * @param CollectionFactory $cartCollection
     * @param Session $session
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactory $cartCollection,
        Session $session,
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->cartCollection = $cartCollection;
        $this->session        = $session;

        parent::__construct($helper, $context, $data);
    }

    /**
     * @return bool|Collection
     */
    public function getCarts()
    {
        if (!($customerId = $this->session->getCustomerId())) {
            return false;
        }
        if (!$this->carts) {
            $this->carts = $this->cartCollection->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }
        if ($cartLimit = $this->_request->getParam('cart_limit')) {
            $this->carts->setPageSize($cartLimit);
        }

        if ($cartPageNumber = $this->_request->getParam('cart_page_number')) {
            $this->carts->setCurPage($cartPageNumber);
        }

        return $this->carts;
    }

    /**
     * @return $this|Template
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCarts()) {
            /** @var Pager $pager */
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'mpsavecart.cart.history.pager'
            )->setCollection(
                $this->getCarts()
            );
            $pager->setLimitVarName('cart_limit')->setPageVarName('cart_page_number');
            $this->setChild('cart_pager', $pager);
            $this->getCarts()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('cart_pager');
    }

    /**
     * @param $cart
     *
     * @return float|string
     */
    public function getCartTotal($cart)
    {
        return $this->helper->getCartTotal($cart);
    }

    /**
     * @return mixed
     */
    public function allowShare()
    {
        return $this->helper->allowShare();
    }

    /**
     * @param $cart
     *
     * @return string
     */
    public function getItems($cart)
    {
        $items = '';
        try {
            $items = $this->helper->getItems($cart);
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $items;
    }

    /**
     * @param Cart $cart
     *
     * @return string
     */
    public function getDeleteUrl($cart)
    {
        return $this->getUrl(
            'mpsavecart/cart/delete',
            [
                'id' => $cart->getToken()
            ]
        );
    }

    /**
     * @param Cart $cart
     *
     * @return string
     */
    public function getRestoreUrl($cart)
    {
        return $this->getUrl(
            'mpsavecart/cart/restore',
            [
                'id' => $cart->getToken()
            ]
        );
    }

    /**
     * @param Cart $cart
     *
     * @return string
     */
    public function getShareUrl($cart)
    {
        return $this->getUrl('mpsavecart/cart/share', ['id' => $cart->getToken()]);
    }

    /**
     * @param Cart $cart
     *
     * @return string
     */
    public function getViewUrl($cart)
    {
        return $this->getUrl('mpsavecart/cart/view', ['id' => $cart->getToken()]);
    }
}
