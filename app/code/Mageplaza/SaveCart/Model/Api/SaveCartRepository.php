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
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart as CheckoutCart;
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
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Mageplaza\SaveCart\Api\Data\CartSearchResultInterface;
use Mageplaza\SaveCart\Api\SaveCartRepositoryInterface;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\CartItem;
use Mageplaza\SaveCart\Model\CartItemFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\CollectionFactory;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollectionFactory;

/**
 * Class SaveCartRepository
 * @package Mageplaza\SaveCart\Model\Api
 */
class SaveCartRepository implements SaveCartRepositoryInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    protected $cartCollectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CartItemCollectionFactory
     */
    protected $cartItemCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var CartFactory
     */
    protected $saveCartFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CartItemFactory
     */
    protected $saveCartItemFactory;

    /**
     * SaveCartRepository constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param UrlInterface $url
     * @param CollectionFactory $cartCollectionFactory
     * @param CartItemCollectionFactory $cartItemCollectionFactory
     * @param Data $helperData
     * @param CartFactory $saveCartFactory
     * @param State $state
     * @param ProductRepository $productRepository
     * @param CheckoutCart $checkoutCart
     * @param CartRepositoryInterface $cartRepository
     * @param RequestInterface $request
     * @param CartItemFactory $saveCartItemFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        UrlInterface $url,
        CollectionFactory $cartCollectionFactory,
        CartItemCollectionFactory $cartItemCollectionFactory,
        Data $helperData,
        CartFactory $saveCartFactory,
        State $state,
        ProductRepository $productRepository,
        CheckoutCart $checkoutCart,
        CartRepositoryInterface $cartRepository,
        RequestInterface $request,
        CartItemFactory $saveCartItemFactory
    ) {
        $this->searchCriteriaBuilder     = $searchCriteriaBuilder;
        $this->collectionProcessor       = $collectionProcessor;
        $this->searchResultsFactory      = $searchResultsFactory;
        $this->cartCollectionFactory     = $cartCollectionFactory;
        $this->helperData                = $helperData;
        $this->cartItemCollectionFactory = $cartItemCollectionFactory;
        $this->url                       = $url;
        $this->saveCartFactory           = $saveCartFactory;
        $this->state                     = $state;
        $this->productRepository         = $productRepository;
        $this->checkoutCart              = $checkoutCart;
        $this->cartRepository            = $cartRepository;
        $this->request                   = $request;
        $this->saveCartItemFactory       = $saveCartItemFactory;
    }

    /**
     * @param int $customerId
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return SearchResultsInterface|CartSearchResultInterface
     * @throws LocalizedException
     */
    public function getList($customerId, SearchCriteriaInterface $searchCriteria = null)
    {
        $this->helperData->checkEnabled();
        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $collection = $this->cartCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);

        $this->collectionProcessor->process($searchCriteria, $collection);

        foreach ($collection->getItems() as $cart) {
            $items = $this->cartItemCollectionFactory->create()
                ->addFieldToFilter('cart_id', $cart->getCartId())->getItems();
            if ($this->helperData->allowShare()) {
                $cart->setShareUrl($this->url->getUrl('mpsavecart/cart/share', ['id' => $cart->getToken()]));
            }
            $storeId = $cart->getStoreId();
            foreach ($items as $cartItem) {
                $cartItem->setProductName($this->helperData->getProductName($cartItem, $storeId));
                $cartItem->setSku($this->helperData->getSku($cartItem, $storeId));
            }

            $cart->setItems($items);
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
     * @param string $token
     *
     * @return Cart
     * @throws LocalizedException
     * @throws Exception
     */
    public function get($customerId, $token)
    {
        $this->helperData->checkEnabled();
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $saveCart = $this->saveCartFactory->create()->load($token, 'token');

        if (!($cartId = $saveCart->getId()) || (int) $saveCart->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Cart does not exist'));
        }
        if ($this->helperData->allowShare()) {
            $saveCart->setShareUrl($this->url->getUrl('mpsavecart/cart/share', ['id' => $token]));
        }
        $items = $this->state->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'getCartItems'], [$cartId]);
        $saveCart->setItems($items);

        return $saveCart;
    }

    /**
     * @param int $cartId
     *
     * @return Cart[]
     * @throws NoSuchEntityException
     */
    public function getCartItems($cartId)
    {
        $items = $this->cartItemCollectionFactory->create()->addFieldToFilter('cart_id', $cartId)->getItems();

        foreach ($items as $item) {
            $storeId = $item->getStoreId();
            $item->setProductName($this->helperData->getProductName($item, $storeId));
            $item->setSku($this->helperData->getSku($item, $storeId));
            $item->setImage($this->helperData->getImage($item, $storeId));
            $item->setPrice($this->helperData->getPrice($item, $storeId, true, false));
            $item->setSubtotalConverted($this->helperData->getSubtotalConverted($item, $storeId, true, false));
        }

        return $items;
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
        $saveCart = $this->saveCartFactory->create()->load($token, 'token');

        if (!($saveCart->getId()) || (int) $saveCart->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Cart does not exist'));
        }
        $this->addItemsToCart($saveCart, $cartId);
        $saveCart->delete();

        return true;
    }

    /**
     * @param Cart $saveCart
     * @param int $cartId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function addItemsToCart($saveCart, $cartId)
    {
        $this->helperData->checkEnabled();
        $items = $this->cartItemCollectionFactory->create()->addFieldToFilter('cart_id', $saveCart->getCartId());

        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $this->checkoutCart->setQuote($quote);
        /** @var CartItem $item */
        foreach ($items as $item) {
            /** @var Product $product */
            $product        = $this->productRepository->getById(
                $item->getProductId(),
                false,
                $item->getStoreId(),
                true
            );
            $infoBuyRequest = $this->helperData->jsDecode($item['buy_request']);
            $this->checkoutCart->addProduct($product, $infoBuyRequest);
        }
        $this->checkoutCart->save();
    }

    /**
     * @param int $customerId
     * @param string $token
     *
     * @return bool
     * @throws Exception
     */
    public function delete($customerId, $token): bool
    {
        $this->helperData->checkEnabled();
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $saveCart = $this->saveCartFactory->create()->load($token, 'token');

        if (!($saveCart->getId()) || (int) $saveCart->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Cart does not exist'));
        }

        $saveCart->delete();

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
        if (!$this->helperData->allowShare()) {
            throw new LocalizedException(__('Sharing is disabled'));
        }
        $token = $this->request->getPost('token');
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $saveCart = $this->saveCartFactory->create()->load($token, 'token');
        if (!($saveCart->getId())) {
            throw  new LocalizedException(__('Cart does not exist'));
        }
        $this->addItemsToCart($saveCart, $cartId);

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
        $cartName    = $this->request->getParam('name');
        $description = $this->request->getParam('description');

        if ($cartName === '') {
            throw new LocalizedException(__('Cart name must not empty'));
        }

        /** @var Collection $carts */
        $carts         = $this->cartCollectionFactory->create();
        $cartAvailable = $carts->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('cart_name', $cartName);

        if (count($cartAvailable)) {
            throw new LocalizedException(__('Cart Name has already existed'));
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new LocalizedException(__('Cart empty'));
        }
        $allItems = $quote->getAllItems();
        $storeId  = $quote->getStoreId();

        /** @var Cart $cart */
        $saveCart = $this->saveCartFactory->create();
        $saveCart->setCartName($cartName)
            ->setDescription($description)
            ->setCustomerId($customerId)
            ->setStoreId($storeId)
            ->save();
        $saveCartId = $saveCart->getCartId();

        /** @var Item $item */
        foreach ($allItems as $item) {
            if (!empty($item->getParentItemId())) {
                continue;
            }
            /** @var CartItem $cartItem */
            $cartItem = $this->saveCartItemFactory->create();
            $cartItem->setQty($item->getQty())
                ->setProductId($item->getProductId())
                ->setBuyRequest($this->helperData->jsEncode($item->getBuyRequest()))
                ->setStoreId($storeId)
                ->setCartId($saveCartId)
                ->save();

            $quote->removeItem($item->getId());
        }

        $this->checkoutCart->setQuote($quote)->save();

        return true;
    }
}
