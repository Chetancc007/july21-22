<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductTableAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\ProductTableAttribute\Logger\Logger;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\ProductTableAttribute\Helper\Data;

class CatalogBeforeSave implements ObserverInterface
{

    /**
     * Logger $logger
     */
    private $logger;

    /**
     * @var RequestInterface $request
     */
    private $request;

    /**
     * @var Webkul\ProductTableAttribute\Helper\Data
     */
    private $helper;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Logger $logger
     * @param RequestInterface $request
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Logger $logger,
        RequestInterface $request,
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            $productData = $product->getData();
            $params = $this->request->getParams();

            $attributes = $this->helper->getTableAttributes($params['product']['attribute_set_id']);

            foreach ($attributes as $attribute) {
                $tmpCode = 'wk_temp_'.$attribute['attribute_code'];

                if (isset($params['product'][$tmpCode])) {
                    $code = 'wk_ta_'.$attribute['attribute_code'];
                    $productData[$code] = $params['product'][$tmpCode];
                }
            }
            $product->setStoreId($this->storeManager->getStore()->getId())->setData($productData);
        } catch (\Exception $e) {
            $this->logger->addInfo('Error in observer = CatalogBeforeSave- '. $e->getMessage());
        }
    }
}
