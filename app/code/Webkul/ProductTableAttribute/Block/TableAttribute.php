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

namespace Webkul\ProductTableAttribute\Block;

use Magento\Framework\View\Element\Template\Context;
use Webkul\ProductTableAttribute\Logger\Logger;
use Webkul\ProductTableAttribute\Helper\Data;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeFactory;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Webkul MpSellerMapLocator TableAttribute Block
 */

class TableAttribute extends \Magento\Catalog\Block\Product\View\Description
{

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var Webkul\ProductTableAttribute\Helper\Data
     */
    private $helper;

    /**
     * @var ProductTableAttributeFactory $productTableAttributeFactory
     */
    private $productTableAttributeFactory;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param Context $context
     * @param Logger $logger
     * @param Data $helper
     * @param ProductTableAttributeFactory $productTableAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        Context $context,
        Logger $logger,
        Data $helper,
        ProductTableAttributeFactory $productTableAttributeFactory,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->logger = $logger;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->productTableAttributeFactory = $productTableAttributeFactory;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
    }

    /**
     * get current product
     *
     * @return \Mageto\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * get table attributes
     *
     * @return array
     */
    public function getTableAttributes()
    {
        $rows = [];
        $product = $this->getProduct();
        $attributes = $this->helper->getTableAttributes($product->getAttributeSetId());
        foreach ($attributes as $attribute) {
            $code = 'wk_ta_'.$attribute['attribute_code'];
            if ($product->getData($code) != "") {
                $rows[] = [
                    'id'   =>  $attribute['attribute_id'],
                    'name'   =>  $attribute['attribute_name'],
                    'values' =>  $product->getData($code)
                ];
            }
        }
        return $rows;
    }

    /**
     * get column heads
     *
     * @param int $id
     * @return array
     */
    public function getTableHead($id)
    {
        $head = [];
        $collection = $this->getHeadCollection($id);
        $collection->addFieldToFilter('store_id', $this->storeManager->getStore()->getId());
        foreach ($collection as $headData) {
            if ($headData->getColumnName() != "") {
                $head[] = [
                    'column_name'   =>  $headData->getColumnName(),
                    'column_id'     =>  $headData->getColumnId()
                ];
            }
        }
        if (empty($head)) {
            $checkCollection = $this->getHeadCollection($id);
            $checkCollection->addFieldToFilter('store_id', 0);
            foreach ($checkCollection as $checkData) {
                if ($checkData->getColumnName() != "") {
                    $head[] = [
                        'column_name'   =>  $headData->getColumnName(),
                        'column_id'     =>  $headData->getColumnId()
                    ];
                }
            }
        }
        return $head;
    }

    /**
     * get head collection
     *
     * @param int $id
     * @return object
     */
    private function getheadCollection($id)
    {
        $collection = $this->productTableAttributeFactory
                           ->create()
                           ->getCollection()
                           ->addFieldToFilter('attribute_id', $id);

        $table2 = $this->resource->getTableName('wk_table_columns_value');

        $collection->getSelect()->join(
            [
                                        'second' => $table2
                                        ],
            'main_table.column_id = second.column_id'
        );
        return $collection;
    }
}
