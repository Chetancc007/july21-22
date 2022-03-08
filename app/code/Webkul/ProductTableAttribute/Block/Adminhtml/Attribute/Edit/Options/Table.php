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
namespace Webkul\ProductTableAttribute\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeRepository;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsRepository;

/**
 * Block Class for Table Attribute
 */
class Table extends Options
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_ProductTableAttribute::catalog/product/attribute/table.phtml';

    /**
     * @var ProductTableAttributeRepository
     */
    private $productTableAttributeRepository;

    /**
     * @var ProductTableAttributeOptionsRepository
     */
    private $productTableAttributeOptionsRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param array $data
     * @param ProductTableAttributeRepository $productTableAttributeRepository
     * @param ProductTableAttributeOptionsRepository $productTableAttributeOptionsRepository
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        ProductTableAttributeRepository $productTableAttributeRepository,
        ProductTableAttributeOptionsRepository $productTableAttributeOptionsRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $attrOptionCollectionFactory, $universalFactory, $data);
        $this->productTableAttributeRepository = $productTableAttributeRepository;
        $this->productTableAttributeOptionsRepository = $productTableAttributeOptionsRepository;
    }

    /**
     * get json options data
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $allValues = [];
        if ($this->getAttributeObject()->getFrontendInput() == "table") {
            $attributeId = $this->getRequest()->getParam('attribute_id');
            $collection = $this->productTableAttributeRepository->getCollectionByAttributeId($attributeId);
            $values = [];
            foreach ($collection as $columns) {
                $columnsCollection = $this->productTableAttributeOptionsRepository
                                          ->getCollectionByColumnId($columns->getColumnId());
                foreach ($columnsCollection as $columnsValue) {
                    $values['id'] = $columnsValue->getColumnId();
                    $values['table'.$columnsValue->getStoreId()] = $columnsValue->getColumnName();
                }
                $allValues[] = $values;
            }
            $data = [
                'attributesData' => $allValues,
                'isSortable' => (int)(!$this->getReadOnly() && !$this->canManageOptionDefaultOnly()),
                'isReadOnly' => (int)$this->getReadOnly()
            ];
        } else {
            $values = [];
            foreach ($this->getOptionValues() as $value) {
                $values[] = $value->getData();
            }

            $data = [
                'attributesData' => $values,
                'isSortable' => (int)(!$this->getReadOnly() && !$this->canManageOptionDefaultOnly()),
                'isReadOnly' => (int)$this->getReadOnly()
            ];
        }
        return json_encode($data);
    }
}
