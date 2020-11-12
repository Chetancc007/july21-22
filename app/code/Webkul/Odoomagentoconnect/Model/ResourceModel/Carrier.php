<?php
/**
 * Webkul Odoomagentoconnect Carrier ResourceModel
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

class Carrier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_objectManager;
    protected $_scopeConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Connection $connection,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_connection = $connection;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_carrier', 'entity_id');
    }

    public function getMageCarrierArray()
    {
        $carrier = [];
        $carrier[''] ='--Select Magento Carrier--';
        $collection = $this->_objectManager->create('\Magento\Shipping\Model\Config')->getActiveCarriers();

        foreach ($collection as $shippigCode => $shippingModel) {
            $shippingTitle = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            if (!$shippingTitle) {
                $shippingTitle = $shippigCode;
            }
            $carrier[$shippigCode] = $shippingTitle;
        }
        
        return $carrier;
    }

    public function getErpCarrierArray()
    {
        $carrier = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0) {
            $carrier[''] ='--Select Odoo Carrier--';
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = [];
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("delivery.carrier", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);
            if ($resp0->faultCode()) {
                array_push($carrier, ['label' => $helper->__('Not Available(Error in Fetching)'), 'value' => '']);
                return $carrier;
            } else {
                $val = $resp0->value()->me['array'];
                $key1 = [new xmlrpcval('id', 'string'), new xmlrpcval('name', 'string')];
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("delivery.carrier", "string"));
                $msgSer1->addParam(new xmlrpcval("read", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($key1, "array"));
                $msgSer1->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($carrier, ['label' => $msg, 'value' => '']);
                    return $carrier;
                } else {
                    $valueArray=$resp1->value()->scalarval();
                    $count = count($valueArray);
                    for ($x=0; $x<$count; $x++) {
                        $id = $valueArray[$x]->me['struct']['id']->me['int'];
                        $name = $valueArray[$x]->me['struct']['name']->me['string'];
                        $carrier[$id] = $name;
                    }
                }
            }
            return $carrier;
        } else {
            $carrier['error'] = $errorMessage;
            return $carrier;
        }
    }

    public function checkSpecificCarrier($shippigCode)
    {
        $collection =  $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Carrier')->getCollection()
                                                ->addFieldToFilter('carrier_code', ['eq'=>$shippigCode]);
        if ($collection->getSize() > 0) {
            foreach ($collection as $check) {
                $response = $check->getOdooId();
            }
        } else {
            $response = $this->createCarrierAtOdoo($shippigCode);
        }
        return $response;
    }

    public function createCarrierAtOdoo($shippigCode)
    {
        $response = [];
        if ($shippigCode) {
            $helper = $this->_connection;
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $errorMessage = $helper->getSession()->getErrorMessage();

            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            
            $shippingTitle = $this->_scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            if (!$shippingTitle) {
                $shippingTitle = $shippigCode;
            }
            $carrierArray = $arrayVal = [
                        'name'=>new xmlrpcval($shippingTitle, "string")
                    ];
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("delivery.carrier", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($carrierArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Shipping ".$shippigCode." >>".$resp->faultString();
                $helper->addError($error);
                $response = [
                    'odoo_id'=>0,
                    'success'=> false,
                    'message'=>$error
                    ];
            } else {
                $odooId = $resp->value()->me["int"];
                if ($odooId > 0) {
                    $mappingData = [
                                'carrier_code'=>$shippigCode,
                                'carrier_name'=>$shippingTitle,
                                'odoo_id'=>$odooId,
                                'created_by'=>$helper::$mageUser
                            ];
                    $model = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Carrier');
                    if (!empty($mappingData)) {
                        $model->setData($mappingData);
                    }
                    $this->createMapping($mappingData);
                    $response = [
                        'success'=> true,
                        'odoo_id'=>$odooId,
                    ];
                }
            }
        }
        return $response;
    }

    public function createMapping($data)
    {
        $createdBy = 'Magento';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $carrierModel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Carrier');
        $carrierModel->setData($data);
        $carrierModel->save();
        return true;
    }
}
