<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\SaveCart\Helper;

use Exception;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Core\Helper\Media;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\Config\Source\PageLinkArea;
use Mageplaza\SaveCart\Model\Product as SaveCartProduct;
use Mageplaza\SaveCart\Model\ProductFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart as CartResource;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollectionFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Product as ProductResource;

/**
 * Class Data
 * @package Mageplaza\SaveCart\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpsavecart';

    /**
     * Currently logged in customer
     *
     * @var CustomerInterface
     */
    protected $currentCustomer;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PostHelper
     */
    protected $postDataHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductFactory
     */
    protected $productModel;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var CartFactory
     */
    protected $cartModel;

    /**
     * @var CartResource
     */
    protected $cartResource;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var BundleType
     */
    protected $bundleType;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var CartItemCollectionFactory
     */
    protected $cartItemCollection;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var DownloadableType
     */
    protected $downloadProduct;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param PostHelper $postDataHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductFactory $productModel
     * @param ProductResource $productResource
     * @param ProductRepository $productRepository
     * @param CartFactory $cartModel
     * @param CartResource $cartResource
     * @param Configurable $configurable
     * @param BundleType $bundleType
     * @param GroupManagementInterface $groupManagement
     * @param CartItemCollectionFactory $cartItemCollection
     * @param Image $imageHelper
     * @param AssetRepository $assetRepo
     * @param Media $mediaHelper
     * @param Json $json
     * @param DownloadableType $downloadProduct
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        PostHelper $postDataHelper,
        PriceCurrencyInterface $priceCurrency,
        ProductFactory $productModel,
        ProductResource $productResource,
        ProductRepository $productRepository,
        CartFactory $cartModel,
        CartResource $cartResource,
        Configurable $configurable,
        BundleType $bundleType,
        GroupManagementInterface $groupManagement,
        CartItemCollectionFactory $cartItemCollection,
        Image $imageHelper,
        AssetRepository $assetRepo,
        Media $mediaHelper,
        Json $json,
        DownloadableType $downloadProduct
    ) {
        $this->customerSession    = $customerSession;
        $this->priceCurrency      = $priceCurrency;
        $this->postDataHelper     = $postDataHelper;
        $this->productModel       = $productModel;
        $this->productResource    = $productResource;
        $this->cartModel          = $cartModel;
        $this->cartResource       = $cartResource;
        $this->productRepository  = $productRepository;
        $this->configurable       = $configurable;
        $this->bundleType         = $bundleType;
        $this->groupManagement    = $groupManagement;
        $this->cartItemCollection = $cartItemCollection;
        $this->imageHelper        = $imageHelper;
        $this->assetRepo          = $assetRepo;
        $this->mediaHelper        = $mediaHelper;
        $this->json               = $json;
        $this->downloadProduct    = $downloadProduct;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isEnabled() && $this->getCustomer();
    }

    /**
     * Retrieve current customer
     *
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        if (!$this->currentCustomer && $this->customerSession->isLoggedIn()) {
            $this->currentCustomer = $this->customerSession->getCustomerDataObject();
        }

        return $this->currentCustomer;
    }

    /**
     * Retrieve params for adding product to product list
     *
     * @param int $itemId
     *
     * @return string
     */
    public function getMoveFromCartParams($itemId)
    {
        $url    = $this->_getUrl('mpsavecart/index/fromcart');
        $params = ['item' => $itemId];

        return $this->postDataHelper->getPostData($url, $params);
    }

    /**
     * @param int $area
     * @param null $storeId
     *
     * @return bool
     */
    public function showLinkOn($area = PageLinkArea::TOPLINK, $storeId = null)
    {
        $isEnabled = $this->isEnabled($storeId);
        $pageAreas = explode(',', $this->getConfigGeneral('page_link_area', $storeId));

        return $isEnabled && in_array((string) $area, $pageAreas, true);
    }

    /**
     * @param float $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($amount, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $amount,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->priceCurrency->convert($amount, $scope);
    }

    /**
     * @throws Exception
     */
    public function deleteProduct()
    {
        $product = $this->getSavedProduct();
        $this->productResource->delete($product);
    }

    /**
     * @throws Exception
     */
    public function deleteCart()
    {
        $cart = $this->getCart();
        $this->cartResource->delete($cart);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getLabel($storeId = null)
    {
        return $this->getConfigGeneral('button_tittle', $storeId);
    }

    /**
     * @param $item
     * @param $storeId
     *
     * @return array|ProductInterface|Product|mixed|null
     * @throws NoSuchEntityException
     */
    public function getProduct($item, $storeId)
    {
        $buyRequest = self::jsonDecode($item['buy_request']);
        $product    = $this->productRepository->getById($item['product_id'], false, $storeId, true);
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            return $this->configurable->getProductByAttributes($buyRequest['super_attribute'], $product);
        }
        if ($product->getTypeId() === BundleType::TYPE_CODE) {
            return $this->getChildBundles($buyRequest, $product, $storeId);
        }

        if ($product->getTypeId() === DownloadableType::TYPE_DOWNLOADABLE) {
            $price = 0;

            if (!isset($buyRequest['links'])) {
                return $product;
            }
            foreach ($product->getDownloadableLinks() as $link) {
                if (in_array($link->getId(), $buyRequest['links'])) {
                    $price += $link->getPrice();
                }
            }
            $product->setPrice($price);

            return $product;
        }

        return $product;
    }

    /**
     * @param $buyRequest
     * @param $product
     * @param $storeId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getChildBundles($buyRequest, $product, $storeId)
    {
        $childBundles = [];
        $selections   = $this->bundleType->getSelectionsByIds($buyRequest['bundle_option'], $product);
        foreach ($selections as $selection) {
            $childProduct   = $this->productRepository->getById($selection->getId(), false, $storeId, true);
            $childQty       = $this->getQty($selection, $buyRequest['bundle_option_qty'], $selection->getOptionId());
            $childBundles[] = [
                'qty'     => $childQty,
                'product' => $childProduct
            ];
        }

        return $childBundles;
    }

    /**
     * @param DataObject $selection
     * @param int[] $qtys
     * @param int $selectionOptionId
     *
     * @return float
     */
    protected function getQty($selection, $qtys, $selectionOptionId)
    {
        if (isset($qtys[$selectionOptionId]) && $selection->getSelectionCanChangeQty()) {
            $qty = (float) $qtys[$selectionOptionId] > 0 ? $qtys[$selectionOptionId] : 1;
        } else {
            $qty = (float) $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
        }
        $qty = (float) $qty;

        return $qty;
    }

    /**
     * @param $item
     * @param $storeId
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getProductName($item, $storeId)
    {
        $product = $this->productRepository->getById($item['product_id'], false, $storeId, true);

        return $product->getName();
    }

    /**
     * @param $item
     * @param $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSku($item, $storeId)
    {
        $product = $this->productRepository->getById($item['product_id'], false, $storeId, true);

        return $product->getSku();
    }

    /**
     * @param $item
     * @param $storeId
     * @param null $customerGroupId
     *
     * @return float|int|mixed
     * @throws NoSuchEntityException
     */
    public function getProductFinalPrice($item, $storeId, $customerGroupId = null)
    {
        $products = $this->getProduct($item, $storeId);

        if (!is_array($products)) {
            try {
                return $this->getProductPrice($products, $item['qty'], $customerGroupId);
            } catch (LocalizedException $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        $price = 0;
        foreach ($products as $product) {
            try {
                $price +=
                    $product['qty'] * $this->getProductPrice($product['product'], $product['qty'], $customerGroupId);
            } catch (LocalizedException $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return $price;
    }

    /**
     * @param $item
     * @param $storeId
     *
     * @return string
     */
    public function getImage($item, $storeId)
    {
        $imageUrl = '';
        try {
            $product         = $this->productRepository->getById($item['product_id'], false, $storeId, true);
            $origImageHelper = $this->imageHelper->init($product, 'product_thumbnail_image', ['type' => 'thumbnail']);
            $imageUrl        = $origImageHelper->getUrl();
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $imageUrl;
    }

    /**
     * @param $item
     * @param $storeId
     * @param null $customerGroupId
     * @param bool $format
     * @param bool $includeContainer
     *
     * @return float|string
     * @throws NoSuchEntityException
     */
    public function getPrice($item, $storeId, $customerGroupId = null, $format = true, $includeContainer = true)
    {
        return $this->convertPrice(
            $this->getProductFinalPrice($item, $storeId, $customerGroupId),
            $format,
            $includeContainer
        );
    }

    /**
     * @param $item
     * @param $storeId
     * @param null $customerGroupId
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getSubtotal($item, $storeId, $customerGroupId = null)
    {
        return $this->getProductFinalPrice($item, $storeId, $customerGroupId) * $item['qty'];
    }

    /**
     * @param Product $product
     * @param $qty
     * @param null $customerGroupId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getProductPrice($product, $qty, $customerGroupId = null)
    {
        $customerSession = $this->getCustomer();
        if ($customerSession && !$customerGroupId) {
            $customerGroupId = $customerSession->getGroupId();
        }

        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
        $tierPrices = $product->getTierPrices();
        $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

        $prices = [$finalPrice];
        foreach ($tierPrices as $item) {
            if ($qty < $item->getQty() || !in_array($item->getCustomerGroupId(), [$allGroupId, $customerGroupId])) {
                continue;
            }
            $prices[] = $item->getValue();
        }

        return min($prices);
    }

    /**
     * @param $item
     * @param $storeId
     * @param bool $format
     * @param bool $includeContainer
     *
     * @return float|string
     */
    public function getSubtotalConverted($item, $storeId, $format = true, $includeContainer = true)
    {
        try {
            $subtotalConverted = $this->convertPrice($this->getSubtotal($item, $storeId), $format, $includeContainer);
        } catch (NoSuchEntityException $e) {
            $subtotalConverted = '';
            $this->_logger->critical($e->getMessage());
        }

        return $subtotalConverted;
    }

    /**
     * @param $cart
     * @param null $customerGroupId
     *
     * @return float|string
     */
    public function getCartTotal($cart, $customerGroupId = null)
    {
        $cartItems = $this->cartItemCollection->create()
            ->addFieldToFilter('cart_id', $cart['cart_id']);

        $total = 0;
        foreach ($cartItems as $item) {
            try {
                $total += $this->getSubtotal($item, $cart['store_id'], $customerGroupId);
            } catch (NoSuchEntityException $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return $this->convertPrice($total);
    }

    /**
     * @param $cart
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getItems($cart)
    {
        $cartItems = $this->cartItemCollection->create()
            ->addFieldToFilter('cart_id', $cart['cart_id']);
        $strItems  = '';
        foreach ($cartItems as $item) {
            $product  = $this->productRepository->getById($item['product_id'], false, $cart['store_id'], true);
            $strItem  = '<span>' . $product->getName() . ' X ' . $item['qty'] . '</span>' . '<br>';
            $strItems .= $strItem;
        }

        return $strItems;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function allowShare($storeId = null)
    {
        return $this->getConfigGeneral('allow_share', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function showButtonGuest($storeId = null)
    {
        return $this->getConfigGeneral('show_button_guest', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getIconHtml($storeId = null)
    {
        $iconUrl = $this->getIconUrl($storeId);

        return '<img src="' . $iconUrl . '" alt="' . __('Buy Product Icon') . '" width="20" height="18" />';
    }

    /**
     * @param null $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getIconUrl($storeId = null)
    {
        $icon = $this->getConfigGeneral('icon', $storeId);
        if ($icon && $this->mediaHelper->getMediaDirectory()->isExist('mageplaza/savecart/' . $icon)) {
            $iconUrl = $this->mediaHelper->getMediaUrl('mageplaza/savecart/' . $icon);
        } else {
            $iconUrl = $this->assetRepo->getUrlWithParams(
                'Mageplaza_SaveCart::images/default/point.png',
                ['_secure' => $this->_getRequest()->isSecure()]
            );
        }

        return $iconUrl;
    }

    /**
     * @param Cart|SaveCartProduct $item
     *
     * @return bool
     */
    public function checkCustomer($item)
    {
        $customer = $this->getCustomer();

        return !$customer || ($customer->getId() !== $item->getCustomerId());
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->_request->getParam('id');
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        $cartToken = $this->getToken();
        /** @var Cart $cart */
        $cart = $this->cartModel->create();
        $this->cartResource->load($cart, $cartToken, 'token');

        return $cart;
    }

    /**
     * @return SaveCartProduct
     */
    public function getSavedProduct()
    {
        $token = $this->getToken();
        /** @var SaveCartProduct $cart */
        $product = $this->productModel->create();
        $this->productResource->load($product, $token, 'token');

        return $product;
    }

    /**
     * @param $valueToEncode
     *
     * @return string
     */
    public function jsEncode($valueToEncode)
    {
        try {
            return $this->json->serialize($valueToEncode);
        } catch (Exception $e) {
            return '{}';
        }
    }

    /**
     * @param $encodeValue
     *
     * @return array
     */
    public function jsDecode($encodeValue)
    {
        try {
            return $this->json->unserialize($encodeValue);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @throws LocalizedException
     */
    public function checkEnabled()
    {
        if (!$this->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }
    }
}
