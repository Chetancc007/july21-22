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

namespace Mageplaza\SaveCart\Model\Api;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\SaveCart\Api\Data\ProductInterface;
use Mageplaza\SaveCart\Api\Data\ProductSearchResultInterface;
use Mageplaza\SaveCart\Api\SaveProductRepositoryInterface;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Product as SaveCartProduct;
use Mageplaza\SaveCart\Model\ProductFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class SaveProductRepository
 * @package Mageplaza\SaveCart\Model\Api
 */
class SaveProductRepository implements SaveProductRepositoryInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * SaveProductRepository constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param State $state
     * @param UrlInterface $url
     * @param CollectionFactory $productCollectionFactory
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param CartRepositoryInterface $cartRepository
     * @param Data $helperData
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        State $state,
        UrlInterface $url,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        CartRepositoryInterface $cartRepository,
        Data $helperData,
        RequestInterface $request
    ) {
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->collectionProcessor      = $collectionProcessor;
        $this->searchResultsFactory     = $searchResultsFactory;
        $this->state                    = $state;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory           = $productFactory;
        $this->helperData               = $helperData;
        $this->url                      = $url;
        $this->productRepository        = $productRepository;
        $this->cart                     = $cart;
        $this->cartRepository           = $cartRepository;
        $this->request                  = $request;
    }

    /**
     * @param int $customerId
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return SearchResultsInterface|ProductSearchResultInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function getList($customerId, SearchCriteriaInterface $searchCriteria = null)
    {
        $this->helperData->checkEnabled();
        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $collection = $this->productCollectionFactory->create()->filterCollection($customerId);

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var ProductInterface $item */
        foreach ($collection->getItems() as $item) {
            $imageUrl = $this->state->emulateAreaCode(
                Area::AREA_FRONTEND,
                [$this->helperData, 'getImage'],
                [$item, $item->getStoreId()]
            );
            $item->setImageUrl($imageUrl);
            $item->setPrice($this->helperData->getPrice($item, $item->getStoreId(), null, true, false));
            $item->setSubtotalConverted(
                $this->helperData->getSubtotalConverted($item, $item->getStoreId(), true, false)
            );
            if ($this->helperData->allowShare()) {
                $item->setShareUrl($this->url->getUrl('mpsavecart/product/share', ['id' => $item->getToken()]));
            }
        }

        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param int $customerId
     * @param int $cartId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function restore($customerId, $cartId): bool
    {
        $this->helperData->checkEnabled();
        $token = $this->request->getPost('token');
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $productSaved = $this->productFactory->create()->load($token, 'token');
        if (!$productSaved->getId() || (int) $productSaved->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Product does not exist'));
        }
        $this->addProductToCart($productSaved, $cartId);
        $productSaved->delete();

        return true;
    }

    /**
     * @param SaveCartProduct $productSaved
     * @param int $cartId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function addProductToCart($productSaved, $cartId)
    {
        $buyRequest = $this->helperData->jsDecode($productSaved->getBuyRequest());
        /** @var Product $product */
        $product = $this->productRepository->getById(
            $productSaved->getProductId(),
            false,
            $productSaved->getStoreId(),
            true
        );
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $this->cart->setQuote($quote);
        $this->cart->addProduct($product, $buyRequest)->save();
    }

    /**
     * @param int $customerId
     * @param string $token
     *
     * @return bool
     * @throws LocalizedException
     */
    public function delete($customerId, $token): bool
    {
        $this->helperData->checkEnabled();
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $productSaved = $this->productFactory->create()->load($token, 'token');

        if (!$productSaved->getId() || (int) $productSaved->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Product does not exist'));
        }
        $productSaved->delete();

        return true;
    }

    /**
     * @param int $cartId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function share($cartId): bool
    {
        $this->helperData->checkEnabled();
        $token = $this->request->getPost('token');
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        if (!$this->helperData->allowShare()) {
            throw new LocalizedException(__('Sharing is disabled'));
        }
        $productSaved = $this->productFactory->create()->load($token, 'token');
        if (!$productSaved->getId()) {
            throw  new LocalizedException(__('Product does not exist'));
        }
        $this->addProductToCart($productSaved, $cartId);

        return true;
    }

    /**
     * @param int $customerId
     * @param int $cartId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save($customerId, $cartId): bool
    {
        $this->helperData->checkEnabled();
        $itemId = $this->request->getPost('itemId');
        if (!$itemId) {
            throw new InputException(__('itemId is required'));
        }
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $item  = $quote->getItemById($itemId);

        if (!$item) {
            throw new LocalizedException(
                __('The requested cart item doesn\'t exist.')
            );
        }

        $storeId    = $quote->getStoreId();
        $productId  = $item->getProductId();
        $buyRequest = $item->getBuyRequest();
        $productQty = $item->getQty();

        /** @var SaveCartProduct $product */
        $product = $this->productFactory->create();
        $product->setProductId($productId)
            ->setQty($productQty)
            ->setCustomerId($customerId)
            ->setStoreId($storeId)
            ->setBuyRequest($this->helperData->jsEncode($buyRequest->getData()))
            ->save();

        $quote->removeItem($itemId);
        $this->cart->setQuote($quote);
        $this->cart->save();

        return true;
    }
}
