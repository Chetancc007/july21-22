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

use Webkul\ProductTableAttribute\Logger\Logger;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeFactory;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;

class AttributeAfterDelete implements ObserverInterface
{
    /**
     * Logger $logger
     */
    private $logger;

    /**
     * @var ProductTableAttributeFactory $productTableAttributeFactory
     */
    private $productTableAttributeFactory;

    /**
     * @var ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     */
    private $productTableAttributeOptionsFactory;

    /**
     * @var AttributeRepositoryInterface $attributeRepository
     */
    private $attributeRepository;

    /**
     * @param Logger $logger
     * @param ProductTableAttributeFactory $productTableAttributeFactory
     * @param ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Logger $logger,
        ProductTableAttributeFactory $productTableAttributeFactory,
        ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->logger = $logger;
        $this->productTableAttributeFactory = $productTableAttributeFactory;
        $this->productTableAttributeOptionsFactory = $productTableAttributeOptionsFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $attribute = $observer->getEvent()->getAttribute();
            if ($attribute->getFrontendInput() == "table") {
                $this->deleteAttribute($attribute->getAttributeCode());
                $this->deleteFromTables($attribute->getId());
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error in observer=AttributeAfterDelete =".$e->getMessage());
        }
    }

    /**
     * delete attribute
     *
     * @param string $attributeCode
     * @return void
     */
    private function deleteAttribute($attributeCode)
    {
        $entityTypeCode = \Magento\Catalog\Model\Product::ENTITY;
        $attributeCode = 'wk_ta_'.$attributeCode;

        $attributeData = $this->attributeRepository->get($entityTypeCode, $attributeCode);
        $this->attributeRepository->delete($attributeData);
    }

    /**
     * delete attribute
     *
     * @param string $attributeCode
     * @return void
     */
    private function deleteFromTables($attributeId)
    {
        $columns = [];
        $collection = $this->productTableAttributeFactory
                           ->create()
                           ->getCollection()
                           ->addFieldToFilter('attribute_id', $attributeId);
        foreach ($collection as $attribute) {
            $columns[] = $attribute->getColumnId();
        }

        $valuesCollection = $this->productTableAttributeOptionsFactory
                           ->create()
                           ->getCollection()
                           ->addFieldToFilter('column_id', ['in' => $columns]);

        foreach ($valuesCollection as $values) {
            $this->deleteObj($values);
        }

        foreach ($collection as $attribute) {
            $this->deleteObj($attribute);
        }
    }

    /**
     * delete
     *
     * @param object $attribute
     * @return void
     */
    private function deleteObj($attribute)
    {
        $attribute->delete();
    }
}
