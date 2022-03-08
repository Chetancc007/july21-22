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
use Webkul\ProductTableAttribute\Model\ProductTableAttributeFactory;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsFactory;
use Magento\Framework\Serialize\Serializer\FormData;
use Webkul\ProductTableAttribute\Model\Storage\DbInsert;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Entity as EavEntity;
use Webkul\ProductTableAttribute\Helper\Data;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttrGroupCollection;

class AttributeAfterSave implements ObserverInterface
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
     * @var ProductTableAttributeFactory $productTableAttributeFactory
     */
    private $productTableAttributeFactory;

    /**
     * @var ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     */
    private $productTableAttributeOptionsFactory;

    /**
     * @var FormData $formDataSerializer
     */
    private $formDataSerializer;

    /**
     * @var DbInsert $dbInsert
     */
    private $dbInsert;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $attributeFactory;

    /**
     * @var Webkul\ProductTableAttribute\Helper\Data
     */
    private $helper;

    /**
     * @var AttributeManagementInterface $attributeManagement
     */
    private $attributeManagement;

    /**
     * @var AttrGroupCollection $attrGroupCollection
     */
    private $attrGroupCollection;

    /**
     * @param Logger $logger
     * @param RequestInterface $request
     * @param ProductTableAttributeFactory $productTableAttributeFactory
     * @param ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     * @param FormData $formDataSerializer
     * @param DbInsert $dbInsert
     * @param StoreManagerInterface $storeManager
     * @param EavEntity $eavEntity
     * @param AttributeFactory $attributeFactory
     * @param Data $helper
     * @param AttributeManagementInterface $attributeManagement
     * @param Repository $productAttributeRepository
     * @param AttrGroupCollection $attrGroupCollection
     */
    public function __construct(
        Logger $logger,
        RequestInterface $request,
        ProductTableAttributeFactory $productTableAttributeFactory,
        ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory,
        FormData $formDataSerializer,
        DbInsert $dbInsert,
        StoreManagerInterface $storeManager,
        EavEntity $eavEntity,
        AttributeFactory $attributeFactory,
        Data $helper,
        AttributeManagementInterface $attributeManagement,
        Repository $productAttributeRepository,
        AttrGroupCollection $attrGroupCollection
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->productTableAttributeFactory = $productTableAttributeFactory;
        $this->productTableAttributeOptionsFactory = $productTableAttributeOptionsFactory;
        $this->formDataSerializer = $formDataSerializer;
        $this->dbInsert = $dbInsert;
        $this->storeManager = $storeManager;
        $this->eavEntity = $eavEntity;
        $this->attributeFactory = $attributeFactory;
        $this->helper = $helper;
        $this->attributeManagement = $attributeManagement;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attrGroupCollection = $attrGroupCollection;
    }

    /**
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $wholedata = [];
            $attribute = $observer->getEvent()->getAttribute();
            if ($attribute->getFrontendInput() == "table") {
                $attributeGroupId = $this->getAttributeGroupId(
                    'Content',
                    $this->helper->getDefaultAttributeSet()
                );

                $this->setAttributeSet($attribute->getAttributeCode(), $attributeGroupId);

                $optionData = $this->formDataSerializer
                    ->unserialize($this->request->getParam('serialized_options', '[]'));

                $count = count($optionData['tableColumnOption']['value']);

                $delete = $this->removeRow($optionData['optiontext']['delete']);

                for ($i = 0; $i < $count; $i++) {
                    if (isset($optionData['tableColumnOption']['value']['option_'.$i][0])) {
                        $model = $this->productTableAttributeFactory
                                    ->create();
                        $id = $model->getCollection()->getLastItem()->getId();
                        $data['column_id'] = $id + 1;
                        $data['attribute_id'] = $attribute->getId();

                        $model->setData($data);
                        $this->saveModel($model);
                        
                        // for admin store
                        $wholedata[] = [
                            'column_id'     => $data['column_id'],
                            'column_name'   => $optionData['tableColumnOption']['value']['option_'.$i][0],
                            'store_id'      => 0
                        ];

                        foreach ($this->storeManager->getStores() as $store) {
                            $wholedata[] = [
                                'column_id'     => $data['column_id'],
                                'column_name'   =>
                                $optionData['tableColumnOption']['value']['option_'.$i][$store->getId()],
                                'store_id'      => $store->getId()
                            ];
                        }
                    }
                }

                $this->updateColumns($optionData['tableColumnOption']['value'], $delete);

                if (!empty($wholedata)) {
                    $this->dbInsert->insertMultiple('wk_table_columns_value', $wholedata);
                }

                $this->createAttribute($attribute, $attributeGroupId);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error in observer=AttributeAfterSave =".$e->getMessage());
        }
    }

    /**
     * remove rows
     *
     * @param array $wholedata
     * @return array
     */
    private function removeRow($wholedata)
    {
        $delete = [];
        foreach ($wholedata as $key => $value) {
            if ($value == 1) {
                $delete[] = $key;
                $tableCollection = $this->productTableAttributeFactory
                                        ->create()
                                        ->getCollection()
                                        ->addFieldToFilter('column_id', $key);
                foreach ($tableCollection as $table) {
                    $this->deleteObj($table);
                }

                $tableValuesCollection = $this->productTableAttributeOptionsFactory
                                              ->create()
                                              ->getCollection()
                                              ->addFieldToFilter('column_id', $key);
                foreach ($tableValuesCollection as $tableValues) {
                    $this->deleteObj($tableValues);
                }
            }
        }
        return $delete;
    }

    /**
     * delete object
     *
     * @param object $obj
     * @return void
     */
    private function deleteObj($obj)
    {
        $obj->delete();
    }

    /**
     * update columns
     *
     * @param array $wholedata
     * @return void
     */
    private function updateColumns($wholedata, $delete)
    {
        foreach ($wholedata as $tableKey => $tableValues) {
            if (strpos($tableKey, "option_") === false && !in_array($tableKey, $delete)) {
                foreach ($tableValues as $key => $tableValue) {
                    $data = [];
                    $model = $this->loadModel($tableKey, $key);
                    if ($model->getEntityId()) {
                        $model->setColumnName($tableValue);
                        $this->saveModel($model);
                    }
                }
            }
        }
    }

    /**
     * load model
     *
     * @return ProductTableAttributeOptionsFactory
     */
    private function loadModel($tableKey, $key)
    {
        $model = $this->productTableAttributeOptionsFactory
                      ->create()
                      ->getCollection()
                      ->addFieldToFilter('column_id', $tableKey)
                      ->addFieldToFilter('store_id', $key)
                      ->setPageSize(1)
                      ->setCurPage(1)
                      ->getFirstItem();
        return $model;
    }

    /**
     * save model
     *
     * @param object $model
     * @param array $data
     * @return void
     */
    private function saveModel($model)
    {
        $model->save();
    }

    /**
     * create attribute
     *
     * @param [type] $attribute
     * @return void
     */
    private function createAttribute($attributeData, $attributeGroupId)
    {
        try {
            $mageAttrCode = 'wk_ta_'.$attributeData->getAttributeCode();
            $existAttribute = $this->productAttributeRepository->get($mageAttrCode);
        } catch (\Exception $e) {
            $attrData = [
                'attribute_group_id' => $attributeGroupId,
                'attribute_set_id' =>  $this->helper->getDefaultAttributeSet(),
                'entity_type_id' => $this->eavEntity->setType(
                    \Magento\Catalog\Model\Product::ENTITY
                )->getTypeId(),
                'attribute_code' => $mageAttrCode,
                'backend_type' => 'text',
                'frontend_input' => 'textarea',
                'backend' => '',
                'frontend' => '',
                'source' => '',
                'is_global' => $attributeData->getIsGlobal(),
                'is_visible' => false,
                'required' => false,
                'is_user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'is_html_allowed_on_front' => false,
                'visible_in_advanced_search' => false,
                'unique' => false,
                'is_used_in_grid' => 0,
                'is_filterable_in_grid' => 0
            ];

            $attribute = $this->attributeFactory->create();

            $attribute->addData($attrData)->save();
        }
    }

    /**
     * set attribute set for attribute
     *
     * @param string $attributeCode
     * @return void
     */
    private function setAttributeSet($attributeCode, $attributeGroupId)
    {
        $this->attributeManagement->assign(
            'catalog_product',
            $this->helper->getDefaultAttributeSet(),
            $attributeGroupId,
            $attributeCode,
            999
        );
    }

    /**
     * getAttributeGroupId
     * @param $groupName
     */
    private function getAttributeGroupId($groupName, $attributeSetId)
    {
        $group = $this->attrGroupCollection->create()
                                        ->addFieldToFilter('attribute_group_name', $groupName)
                                        ->addFieldToFilter('attribute_set_id', $attributeSetId)
                                        ->setPageSize(1)->getFirstItem();
        return $group->getId();
    }
}
