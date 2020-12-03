<?php
/**
 * Webkul Odoomagentoconnect Option ResourceModel
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

class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $resourcePrefix);
        $this->_connection = $connection;
    }

    public function attributeOptionMapping($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $categorymodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Option');
        $categorymodel->setData($data);
        $categorymodel->save();
        return true;
    }

    public function syncAllAttributeOptions($attributeId, $erpAttributeId)
    {
        $count = 0;
        $helper = $this->_connection;
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $context = ['context' => new xmlrpcval($context, "struct")];
        $userId = $helper->getSession()->getUserId();
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\Product')->getResource()
                                                            ->getAttribute($attributeId);
        $options = $collection->getSource()->getAllOptions(false);
        foreach ($options as $key) {
            $label = $key['label'];
            $optionId = $key['value'];
            $mappingcollection =  $this->_objectManager
                                        ->create('\Webkul\Odoomagentoconnect\Model\Option')
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', $optionId);
            if ($mappingcollection->getSize() == 0) {
                $optionArray = [
                                'name'=>new xmlrpcval($label, "string"),
                                'attribute_id'=>new xmlrpcval($erpAttributeId, "int"),
                            ];
                $optionArray = [new xmlrpcval($optionArray, "struct")];
                $msg = new xmlrpcmsg('execute_kw');
                $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
                $msg->addParam(new xmlrpcval($userId, "int"));
                $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                $msg->addParam(new xmlrpcval("product.attribute.value", "string"));
                $msg->addParam(new xmlrpcval("create", "string"));
                $msg->addParam(new xmlrpcval($optionArray, "array"));
                $msg->addParam(new xmlrpcval($context, "struct"));
                $resp = $client->send($msg);
                if ($resp->faultCode()) {
                    $errorMessage = $resp->faultString();
                    $message = 'Option '.$optionId.' Sync Error '.$resp->faultString();
                    $helper->addError($message);
                } else {
                    $erpOptionId = $resp->value()->me["int"];
                    $mappingData = [
                            'name'=>$label,
                            'odoo_id'=>$erpOptionId,
                            'magento_id'=>$optionId,
                            'created_by'=>$helper::$mageUser
                        ];
                    $this->mapAttributeOption($mappingData);
                    $this->attributeOptionMapping($mappingData);
                    $count = ++$count;
                }
            }
        }
        return $count;
    }

    public function mapAttributeOption($data)
    {
        $helper = $this->_connection;
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $mappingArray = [
                        'name'=>new xmlrpcval($data['odoo_id'], "int"),
                        'odoo_id'=>new xmlrpcval($data['odoo_id'], "int"),
                        'ecomm_id'=>new xmlrpcval($data['magento_id'], "int"),
                        'created_by'=>new xmlrpcval($helper::$mageUser, "string"),
                        'instance_id'=>$context['instance_id'],
                    ];
        $mappingArray = [new xmlrpcval($mappingArray, "struct")];
        $context = ['context' => new xmlrpcval($context, "struct")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("connector.option.mapping", "string"));
        $msg->addParam(new xmlrpcval("create", "string"));
        $msg->addParam(new xmlrpcval($mappingArray, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultCode()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_option', 'entity_id');
    }
}
