<?php
/**
 * Webkul Odoomagentoconnect Attribute ResourceModel
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resourcePrefix = null
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $resourcePrefix);
    }

    public function attributeMapping($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $categorymodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Attribute');
        $categorymodel->setData($data);
        $categorymodel->save();
        return true;
    }

    public function syncAttributeSets($setName, $setId, $erpAttributeIds)
    {
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $setArray = [
                        'name'=>new xmlrpcval($setName, "string"),
                        'set_id'=>new xmlrpcval($setId, "int"),
                    ];
        if (!empty($erpAttributeIds)) {
            $setArray['attribute_ids']= new xmlrpcval($erpAttributeIds, "array");
        }
        $context = ['context' => new xmlrpcval($context, "struct")];
        $setArray = [new xmlrpcval($setArray, "struct")];
        $msg1 = new xmlrpcmsg('execute_kw');
        $msg1->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg1->addParam(new xmlrpcval($userId, "int"));
        $msg1->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg1->addParam(new xmlrpcval("connector.snippet", "string"));
        $msg1->addParam(new xmlrpcval("sync_attribute_set", "string"));
        $msg1->addParam(new xmlrpcval($setArray, "array"));
        $msg1->addParam(new xmlrpcval($context, "struct"));
        $resp1 = $client->send($msg1);
        return true;
    }

    public function updateAttribute()
    {
        $attributemodel = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Attribute');
        $collection = $attributemodel->getCollection();
        $updatedAttribute = 0;
        $notUpdatedAttribute = 0;
        foreach ($collection as $attributeMapModel) {
            $mageId = $attributeMapModel->getMagentoId();
            $odooId = $attributeMapModel->getOdooId();
            $response = $this->syncAttribute($mageId);
            if ($response['erp_attribute_id'] == 0) {
                $notUpdatedAttribute++;
            } else {
                $updatedAttribute++;
            }
        }
        return [$updatedAttribute, $notUpdatedAttribute];
    }

    public function syncAttribute($attributeId)
    {
        $erpAttrId = 0;
        $optionCount = 0;
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $errorMessage = $helper->getSession()->getErrorMessage();

        $mappingcollection =  $this->_objectManager
                                    ->create('\Webkul\Odoomagentoconnect\Model\Attribute')
                                    ->getCollection()
                                    ->addFieldToFilter('magento_id', $attributeId);
        if ($mappingcollection->getSize() > 0) {
            foreach ($mappingcollection as $map) {
                $erpAttrId = $map->getOdooId();
            }
        } else {
            $client = $helper->getClientConnect();
            $context = $helper->getOdooContext();
            $userId = $helper->getSession()->getUserId();
            $collection = $this->_objectManager->create('\Magento\Catalog\Model\Product')->getResource()
                                                            ->getAttribute($attributeId);
            $code = $collection->getAttributeCode();
            $label = $collection->getFrontend()->getLabel();
            $attributeArray = [
                        'name'=>new xmlrpcval($label, "string"),
                    ];
            $attributeArray = [new xmlrpcval($attributeArray, "struct")];
            $context = ['context' => new xmlrpcval($context, "struct")];
            $msg = new xmlrpcmsg('execute_kw');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("product.attribute", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($attributeArray, "array"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $errorMessage = $resp->faultString();
                return ['error'=>$attributeId.'>>'.$errorMessage, 'erp_attribute_id'=>0];
            }
            $erpAttrId = $resp->value()->me["int"];
            $mappingData = [
                            'name'=>$code,
                            'odoo_id'=>$erpAttrId,
                            'magento_id'=>$attributeId,
                            'created_by'=>$helper::$mageUser
                        ];
            $this->attributeMapping($mappingData);
            $mappingData['ecomm_attribute_code']=$code;
            $this->mapAttribute($mappingData);
        }
        if ($erpAttrId) {
            $optionModel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\ResourceModel\Option');
            $optionCount = $optionModel->syncAllAttributeOptions($attributeId, $erpAttrId);
        }
        return ['optcount'=>$optionCount, 'erp_attribute_id'=>$erpAttrId];
    }

    public function mapAttribute($data)
    {
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $attrMappingArray = [
                        'name'=>new xmlrpcval($data['odoo_id'], "int"),
                        'odoo_id'=>new xmlrpcval($data['odoo_id'], "int"),
                        'ecomm_id'=>new xmlrpcval($data['magento_id'], "int"),
                        'ecomm_attribute_code'=>new xmlrpcval($data['ecomm_attribute_code'], "string"),
                        'created_by'=>new xmlrpcval($helper::$mageUser, "string"),
                        'instance_id'=>$context['instance_id'],
                    ];
        $context = ['context' => new xmlrpcval($context, "struct")];
        $attrMappingArray = [new xmlrpcval($attrMappingArray, "struct")];
        $attributeMap = new xmlrpcmsg('execute_kw');
        $attributeMap->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $attributeMap->addParam(new xmlrpcval($userId, "int"));
        $attributeMap->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $attributeMap->addParam(new xmlrpcval("connector.attribute.mapping", "string"));
        $attributeMap->addParam(new xmlrpcval("create", "string"));
        $attributeMap->addParam(new xmlrpcval($attrMappingArray, "array"));
        $attributeMap->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($attributeMap);
        if ($resp->faultCode()) {
            return false;
        }
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_attribute', 'entity_id');
    }
}
