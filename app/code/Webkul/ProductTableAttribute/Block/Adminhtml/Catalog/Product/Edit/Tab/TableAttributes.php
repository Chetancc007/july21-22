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

namespace Webkul\ProductTableAttribute\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeFactory;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\ResourceConnection;
use Webkul\ProductTableAttribute\Helper\Data;
use Magento\Catalog\Model\ProductFactory;

class TableAttributes extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    public $_template = 'catalog/product/edit/tab/tableAttributes.phtml';

    /**
     * @var ProductTableAttributeFactory $productTableAttributeFactory
     */
    private $productTableAttributeFactory;

    /**
     * @var ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     */
    private $productTableAttributeOptionsFactory;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var Webkul\ProductTableAttribute\Helper\Data
     */
    private $helper;

    /**
     * @var ProductFactory $productFactory
     */
    private $productFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductTableAttributeFactory $productTableAttributeFactory
     * @param ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     * @param ResourceConnection $resource
     * @param Data $helper
     * @param ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductTableAttributeFactory $productTableAttributeFactory,
        ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory,
        ResourceConnection $resource,
        Data $helper,
        ProductFactory $productFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->productTableAttributeFactory = $productTableAttributeFactory;
        $this->productTableAttributeOptionsFactory = $productTableAttributeOptionsFactory;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Table Attributes');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Table Attributes');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * get table attributes
     *
     * @return array
     */
    public function getTableAttributes()
    {
        return $this->helper->getTableAttributes($this->getCurrentAttributeSetId());
    }

    /**
     * get current attribute set id
     *
     * @return int
     */
    public function getCurrentAttributeSetId()
    {
        return $this->coreRegistry->registry('product')->getAttributeSetId();
    }

    /**
     * get columns of a table type attribute
     *
     * @return object
     */
    public function getTableColumns()
    {
        $collection = $this->getAttributesCollection();

        $table2 = $this->resource->getTableName('wk_table_columns_value');

        $collection->getSelect()->join(
            ['second' => $table2],
            'main_table.column_id = second.column_id'
        );
        $collection->addFieldToFilter('store_id', 0);
        return $collection;
    }

    /**
     * get table type attributes collection
     *
     * @return ProductTableAttributeFactory
     */
    public function getAttributesCollection()
    {
        $attributeIds = $this->getTableAttributes();
        $collection = $this->productTableAttributeFactory
                           ->create()
                           ->getCollection()
                           ->addFieldToFilter('attribute_id', [
                               'in' =>  $attributeIds
                           ]);
        return $collection;
    }

    /**
     * get attribute values
     *
     * @param int $attributeId
     * @return array
     */
    public function getAttributeValues($attributeId)
    {
        $collection = $this->getTableColumns();
        $collection->addFieldToFilter('attribute_id', $attributeId);
        return $collection->getData();
    }

    /**
     * load product
     *
     * @return ProductFactory
     */
    public function getProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        return $this->productFactory->create()->load($productId);
    }

    /**
     * get attributes data
     *
     * @return array
     */
    public function getAttributesData()
    {
        $rows = [];
        $product = $this->getProduct();
        $attributes = $this->getTableAttributes();

        foreach ($attributes as $attribute) {
            $code = 'wk_ta_'.$attribute['attribute_code'];
            $rows[] = [
                'attribute_id'   =>  $attribute['attribute_id'],
                'attribute_name'   =>  $attribute['attribute_name'],
                'attribute_code'   =>  $attribute['attribute_code'],
                'values' =>  $product->getData($code)
            ];
        }
        return $rows;
    }
}
