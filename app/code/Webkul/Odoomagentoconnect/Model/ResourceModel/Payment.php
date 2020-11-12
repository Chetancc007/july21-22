<?php
/**
 * Webkul Odoomagentoconnect Payment ResourceModel
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

class Payment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Webkul\Odoomagentoconnect\Helper\Connection $helper,
        \Magento\Payment\Model\Config $modelConfig,
        Connection $connection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $resourcePrefix = null
    ) {
        $this->_connection = $connection;
        $this->_modelConfig = $modelConfig;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_payment', 'entity_id');
    }

    public function getMagePaymentArray()
    {
        $payment = [];
        $payment[''] ='--Select Magento Payment Method--';
        $collection = $this->_modelConfig->getActiveMethods();
        foreach ($collection as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_scopeConfig->getValue('payment/'.$paymentCode.'/title');
            $payment[$paymentTitle] = $paymentTitle;
        }
        return $payment;
    }

    public function getErpPaymentArray()
    {
        $payment = [];
        $helper = $this->_helper;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0) {
            $payment[''] ='--Select Odoo Payment--';
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key1 = [  new xmlrpcval('bank', 'string'),
                            new xmlrpcval('cash', 'string') ];
            $key = [
                    new xmlrpcval(
                        [
                                new xmlrpcval('type', "string"),
                                new xmlrpcval('in', "string"),
                                new xmlrpcval($key1, "array")
                            ],
                        "array"
                    ),
                ];
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("account.journal", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);
            if ($resp0->faultCode()) {
                array_push($payment, ['label' => $helper->__('Not Available(Error in Fetching)'), 'value' => '']);
                return $payment;
            } else {
                $val = $resp0->value()->me['array'];
                $key1 = [new xmlrpcval('id', 'int'), new xmlrpcval('name', 'string')];
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("account.journal", "string"));
                $msgSer1->addParam(new xmlrpcval("read", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($key1, "array"));
                $msgSer1->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($payment, ['label' => $msg, 'value' => '']);
                    return $payment;
                } else {
                    $valueArray=$resp1->value()->scalarval();
                    $count = count($valueArray);
                    for ($x=0; $x<$count; $x++) {
                        $id = $valueArray[$x]->me['struct']['id']->me['int'];
                        $name = $valueArray[$x]->me['struct']['name']->me['string'];
                        $payment[$id] = $name;
                    }
                }
            }
            return $payment;
        } else {
            $payment['error'] = $errorMessage;
            return $payment;
        }
    }

    public function syncSpecificPayment($paymentMethod)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($paymentMethod) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $userId = $helper->getSession()->getUserId();
            
            $paymentArray = $arrayVal = [
                        'name'=>new xmlrpcval($paymentMethod, "string"),
                        'type'=>new xmlrpcval('cash', "string")
                    ];
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("bridge.backbone", "string"));
            $msg->addParam(new xmlrpcval("create_payment_method", "string"));
            $msg->addParam(new xmlrpcval($paymentArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Payment ".$paymentMethod." >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $response['error'] = $error;
                $helper->addError($error);
            } else {
                $odooId = $resp->value()->me["int"];
                if ($odooId > 0) {
                    $mappingData = [
                                'magento_id'=>$paymentMethod,
                                'odoo_id'=>$odooId,
                                'created_by'=>$helper::$mageUser
                            ];
                    $this->createMapping($mappingData);
                    $response['odoo_id'] = $odooId;
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
        $carrierModel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Payment');
        $carrierModel->setData($data);
        $carrierModel->save();
        return true;
    }
}
