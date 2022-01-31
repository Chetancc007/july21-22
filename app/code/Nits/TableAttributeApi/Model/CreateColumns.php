<?php

namespace Nits\TableAttributeApi\Model;

use Nits\TableAttributeApi\Api\CreateColumnsInterface;

use Webkul\ProductTableAttribute\Model\ProductTableAttributeFactory;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsFactory;
use Webkul\ProductTableAttribute\Helper\Data;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttrGroupCollection;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\ProductTableAttribute\Model\Storage\DbInsert;

use Webkul\ProductTableAttribute\Model\ProductTableAttributeRepository;
use Webkul\ProductTableAttribute\Model\ProductTableAttributeOptionsRepository;

class CreateColumns implements CreateColumnsInterface
{

    protected $_logger;

    protected $_attributeRepository;

    protected $_attributeOptionManagement;

    protected $_option;

    protected $_attributeOptionLabel;

    //protected $_productTableAttributeRepository;

    /**
     * @var ProductTableAttributeFactory $productTableAttributeFactory
     */
    private $productTableAttributeFactory;

    /**
     * @var ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory
     */
    private $productTableAttributeOptionsFactory;

    /**
     * @var AttributeManagementInterface $attributeManagement
     */
    private $attributeManagement;

    /**
     * @var AttrGroupCollection $attrGroupCollection
     */
    private $attrGroupCollection;

    /**
     * @var Webkul\ProductTableAttribute\Helper\Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var DbInsert $dbInsert
     */
    private $dbInsert;

    /**
     * @var ProductTableAttributeRepository $productTableAttributeRepository
     */
    private $productTableAttributeRepository;

    /**
     * @var ProductTableAttributeOptionsRepository
     */
    private $productTableAttributeOptionsRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterface $attributeOptionLabel,
        \Magento\Eav\Model\Entity\Attribute\Option $option,
        ProductTableAttributeFactory $productTableAttributeFactory,
        ProductTableAttributeOptionsFactory $productTableAttributeOptionsFactory,
        Data $helper,
        AttributeManagementInterface $attributeManagement,
        Repository $productAttributeRepository,
        AttrGroupCollection $attrGroupCollection,
        StoreManagerInterface $storeManager,
        DbInsert $dbInsert,
        ProductTableAttributeRepository $productTableAttributeRepository,
        ProductTableAttributeOptionsRepository $productTableAttributeOptionsRepository
    ){
        $this->_logger = $logger;
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->_option = $option;
        $this->_attributeOptionLabel = $attributeOptionLabel;
        $this->helper = $helper;
        $this->attributeManagement = $attributeManagement;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attrGroupCollection = $attrGroupCollection;
        $this->productTableAttributeFactory = $productTableAttributeFactory;
        $this->productTableAttributeOptionsFactory = $productTableAttributeOptionsFactory;
        $this->storeManager = $storeManager;
        $this->dbInsert = $dbInsert;
        $this->productTableAttributeRepository = $productTableAttributeRepository;
        $this->productTableAttributeOptionsRepository = $productTableAttributeOptionsRepository;
    }

    /**
    * Create Columns
    * @param string $attribute_code
    * @param mixed $column_values
    * @return string
    **/

    public function createColumns($attribute_code, $column_values){

        $attribute_id = $this->_attributeRepository->get('catalog_product', $attribute_code)->getAttributeId();

        try {
            $attributeGroupId = $this->getAttributeGroupId(
                'Content',
                $this->helper->getDefaultAttributeSet()
            );

            $this->setAttributeSet($attribute_code, $attributeGroupId);

            $count = count($column_values);
            if($count > 0){
                for ($i = 0; $i < $count; $i++) {
                    if (isset($column_values[$i]['id']) && $column_values[$i]['id']>0){

                        $this->updateColumns($column_values[$i]['value'], $column_values[$i]['id'], 0);
                        foreach ($this->storeManager->getStores() as $store) {
                            if ($store->getId() > 0)
                                $this->updateColumns($column_values[$i]['value'], $column_values[$i]['id'], $store->getId());
                        }

                    } else {

                        $model = $this->productTableAttributeFactory->create();
                        $id = $model->getCollection()->getLastItem()->getId();
                        $data['column_id'] = $id + 1;
                        $data['attribute_id'] = $attribute_id;

                        $model->setData($data);
                        $this->saveModel($model);

                        $wholedata[] = [
                            'column_id'     => $data['column_id'],
                            'column_name'   => $column_values[$i]['value'],
                            'store_id'      => 0
                        ];
                        foreach ($this->storeManager->getStores() as $store) {
                                if ($store->getId() > 0) {
                                $wholedata[] = [
                                    'column_id'     => $data['column_id'],
                                    'column_name'   => $column_values[$i]['value'],
                                    'store_id'      => $store->getId()
                                ];
                                }
                        }

                        if (!empty($wholedata)) {
                            $logger->info("Info wholedata :: ".print_r($wholedata, true) );
                            $this->dbInsert->insertMultiple('wk_table_columns_value', $wholedata);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $logger->info("Info getMessage :: ".print_r($e->getMessage(), true) );
            $this->logger->addError("Error in observer=AttributeAfterSave =".$e->getMessage());
        }
        
        $columnModel = $this->productTableAttributeRepository->getCollectionByAttributeId($attribute_id);
        $values = [];
        foreach ($columnModel as $columns) {
            $columnsCollection = $this->productTableAttributeOptionsRepository
                                      ->getCollectionByColumnId($columns->getColumnId());
            foreach ($columnsCollection as $columnsValue) {
                $values['id'] = $columnsValue->getColumnId();
                $values['table'.$columnsValue->getStoreId()] = $columnsValue->getColumnName();
            }
            $allValues[] = $values;
        }
        $data = [
            'attributesData' => $allValues
        ];

        //$logger->info("Info CREATE ::".print_r(json_encode($data), true));

        return json_encode($data);
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
     * update columns
     *
     * @param array $value
     * @return void
     */
    private function updateColumns($value, $id, $storeId)
    {
        $data = [];
        $model = $this->loadModel($id, $storeId);
        if ($model->getEntityId()) {
            $model->setColumnName($value);
            $this->saveModel($model);
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
}
