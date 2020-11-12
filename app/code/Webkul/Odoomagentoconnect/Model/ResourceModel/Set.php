<?php
/**
 * Webkul Odoomagentoconnect Set ResourceModel
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

class Set extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setInterface,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attributeObj,
        \Magento\Catalog\Model\Product $catalogModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resourcePrefix = null
    ) {
        $this->_catalogModel = $catalogModel;
        $this->_attributeObj = $attributeObj;
        $this->_connection = $connection;
        $this->_setInterface = $setInterface;
        parent::__construct($context, $resourcePrefix);
        $this->_objectManager = $objectManager;
    }

    public function getOdooAttributeSetId($setId)
    {
        $odooAttributeSetId = 0;
        $model = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Set');
        $mappingcollection =  $model->getCollection()
                                    ->addFieldToFilter('magento_id', $setId);
        if ($mappingcollection->getSize() > 0) {
            foreach ($mappingcollection as $map) {
                $odooAttributeSetId = $map->getOdooId();
            }
        } else {
            $attributeSet = $this->_setInterface->get($setId);
            $setName = $attributeSet->getAttributeSetName();
            $response = $this->exportAttributeSet($setName, $setId);
            if ($response['success']) {
                $result = $this->syncConfigurableAttributes($setName, $setId);
            }
            if (isset($response['odoo_id'])) {
                $odooAttributeSetId = $response['odoo_id'];
            }
        }
        return $odooAttributeSetId;
    }

    public function getMappedAttributeSetIds()
    {
        $mappedIds = [];

        $mappingCollection = $this->_objectManager
                                    ->create('\Webkul\Odoomagentoconnect\Model\Set')
                                    ->getCollection()
                                    ->addFieldToSelect('magento_id');
        
        foreach ($mappingCollection as $mapping) {
            array_push($mappedIds, $mapping->getMagentoId());
        }
        return $mappedIds;
    }

    public function exportAttributeSet($setName, $setId)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $mappedIds = $this->getMappedAttributeSetIds();
        if (!in_array($setId, $mappedIds)) {
            $userId = $helper->getSession()->getUserId();
            $attributesetArray = [
                'name'=>new xmlrpcval($setName, "string"),
                'set_id'=>new xmlrpcval($setId, "int"),
                'created_by'=>new xmlrpcval("magento", "string"),
            ];

            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("magento.attribute.set", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($attributesetArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $errorMessage = $resp->faultString();
                return [
                    'odoo_id'=>0,
                    'success'=> false,
                    'message'=>$errorMessage
                    ];
            } else {
                $odooId = $resp->value()->me["int"];
                $mappingData = [
                        'name'=>$setName,
                        'magento_id'=>$setId,
                        'odoo_id'=>$odooId,
                        'created_by'=>$helper::$mageUser
                    ];
                $this->setmapping($mappingData);
                return [
                        'success'=> true,
                        'odoo_id'=>$odooId,
                    ];
            }
        }
        return [
            'success'=> true,
            'odoo_id'=>$setId
        ];
    }

    public function setmapping($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $setModel = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Set');
        $setModel->setData($data);
        $setModel->save();
    }

    public function syncConfigurableAttributes($setName, $setId)
    {
        $attr = 0;
        $fails = '';
        $optcount = 0;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $attributeArray = [];
        $attributeModel = $this->_attributeObj;
        $attributes = $this->_catalogModel->getResource()
                                                       ->loadAllAttributes()
                                                       ->getSortedAttributes($setId);
        
        foreach ($attributes as $attribute) {
            if ($attribute->getId()
                && $attribute->isInSet($setId)
                && $attribute->getIsGlobal()
                && $attribute->getIsUserDefined()) {
                if ($attribute['frontend_input']=='select') {
                    $attributeId = $attribute->getAttributeId();
                    $response = $attributeModel->syncAttribute($attributeId);
                    if (isset($response['error']) && $response['error']) {
                        $fails .= $attributeId.'('.$response['error'].')';
                    }
                    if (isset($response['optcount']) && $response['optcount']) {
                        $optcount += $response['optcount'];
                    }
                    if (isset($response['erp_attribute_id']) && $response['erp_attribute_id']) {
                        $attr ++;
                        array_push($attributeArray, new xmlrpcval($response['erp_attribute_id'], "int"));
                    }
                }
            }
        }
        $attributeModel->syncAttributeSets($setName, $setId, $attributeArray);

        return ['success'=>$attr, 'options'=>$optcount, 'failure'=>$fails];
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_set', 'entity_id');
    }
}
