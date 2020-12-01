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

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\SaveCart\Block\Account\Dashboard;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Product;
use Mageplaza\SaveCart\Model\ResourceModel\Product\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class Products
 * @package Mageplaza\SaveCart\Block\Account\Dashboard
 */
class Products extends Dashboard
{
    /**
     * @var Collection
     */
    protected $productCollection;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Collection
     */
    protected $products;

    /**
     * Products constructor.
     *
     * @param CollectionFactory $productCollection
     * @param Session $session
     * @param ProductRepository $productRepository
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactory $productCollection,
        Session $session,
        ProductRepository $productRepository,
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->productCollection = $productCollection;
        $this->session           = $session;
        $this->productRepository = $productRepository;

        parent::__construct($helper, $context, $data);
    }

    /**
     * @return bool|Collection
     * @throws LocalizedException
     */
    public function getProducts()
    {
        if (!($customerId = $this->session->getCustomerId())) {
            return false;
        }

        if (!$this->products) {
            /** @var Collection $products */
            $products = $this->productCollection->create();
            $products->filterCollection($customerId)
                ->setOrder('created_at', 'desc');
            $this->products = $products;
        }

        if ($productLimit = $this->_request->getParam('product_limit')) {
            $this->products->setPageSize($productLimit);
        }

        if ($productPageNumber = $this->_request->getParam('product_page_number')) {
            $this->products->setCurPage($productPageNumber);
        }

        return $this->products;
    }

    /**
     * @return $this|Template
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProducts()) {
            /** @var Pager $pager */
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'mpsavecart.product.history.pager'
            )->setCollection(
                $this->getProducts()
            );
            $pager->setLimitVarName('product_limit')->setPageVarName('product_page_number');
            $this->setChild('pager', $pager);
            $this->getProducts()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param Product $item
     *
     * @return float|string
     */
    public function getPrice($item)
    {
        $price = '';
        try {
            $price = $this->helper->getPrice($item, $item->getStoreId());
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $price;
    }

    /**
     * @param Product $item
     *
     * @return float|string
     */
    public function getSubtotalConverted($item)
    {
        return $this->helper->getSubtotalConverted($item, $item->getStoreId());
    }

    /**
     * @param Product $item
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getProductName($item)
    {
        return $this->helper->getProductName($item, $item->getStoreId());
    }

    /**
     * @param Product $item
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSku($item)
    {
        return $this->helper->getSku($item, $item->getStoreId());
    }

    /**
     * @param Product $item
     *
     * @return string
     */
    public function getImage($item)
    {
        return $this->helper->getImage($item, $item->getStoreId());
    }

    /**
     * @return mixed
     */
    public function allowShare()
    {
        return $this->helper->allowShare();
    }

    /**
     * @param Product $item
     *
     * @return string
     */
    public function getProductUrl($item)
    {
        $productUrl = '';
        try {
            $productUrl = $this->productRepository->getById($item['product_id'], false, $item->getStoreId(), true)
                ->getProductUrl();
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $productUrl;
    }

    /**
     * @param Product $item
     *
     * @return string
     */
    public function getDeleteUrl($item)
    {
        return $this->getUrl(
            'mpsavecart/product/delete',
            [
                'id' => $item->getToken()
            ]
        );
    }

    /**
     * @param Product $item
     *
     * @return string
     */
    public function getRestoreUrl($item)
    {
        return $this->getUrl(
            'mpsavecart/product/restore',
            [
                'id' => $item->getToken()
            ]
        );
    }

    /**
     * @param Product $item
     *
     * @return string
     */
    public function getShareUrl($item)
    {
        return $this->getUrl('mpsavecart/product/share', ['id' => $item->getToken()]);
    }
}
