<?php
/**
 * Webkul Odoomagentoconnect SalesOrderPlaceAfterObserver Observer Model
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 *
 */
namespace Webkul\Odoomagentoconnect\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class SalesOrderCancelObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderModel,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_orderModel = $orderModel;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Cancel event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        $operationFrom = $this->_connection->getSession()->getOperationFrom();
        $this->_connection->getSession()->unsOperationFrom();
        if ($operationFrom == 'odoo') {
            return true;
        }
        $orderId = $observer->getOrder()->getId();

        $mappingcollection = $this->_orderModel->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
        if ($mappingcollection->getSize() > 0) {
            $helper = $this->_connection;
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $context = $helper->getOdooContext();
            if ($userId > 0) {
                foreach ($mappingcollection as $map) {
                    $erporderid = $map->getOdooId();
                    $orderName = $map->getOdooOrder();
                    if ($erporderid > 0) {
                        $state = $this->checkErpOrderStatus($erporderid);
                        if ($state != 'cancel') {
                            $client = $helper->getClientConnect();
                            $orderCancel = new xmlrpcmsg('execute');
                            $orderCancel->addParam(new xmlrpcval($helper::$odooDb, "string"));
                            $orderCancel->addParam(new xmlrpcval($userId, "int"));
                            $orderCancel->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                            $orderCancel->addParam(new xmlrpcval("wk.skeleton", "string"));
                            $orderCancel->addParam(new xmlrpcval("set_order_cancel", "string"));
                            $orderCancel->addParam(new xmlrpcval($erporderid, "int"));
                            $orderCancel->addParam(new xmlrpcval($context, "struct"));
                            $resp = $client->send($orderCancel);
                            if ($resp->faultcode()) {
                                $faultString = $resp->faultString();
                                $error = 'Sync Error, Odoo Order '.$orderName.', During Cancel: , '.$faultString;
                                $helper->addError($error);
                                $this->messageManager->addError(__($error));
                            } else {
                                $message = " Canceled Successfully.";
                                $this->messageManager->addSuccess(__("Odoo Order ".$orderName.$message));
                            }
                        }
                    }
                }
            } else {
                $message = "Odoo Order Cannot be canceled, Your Magento Is Not Connected with Odoo. Error, ";
                $this->messageManager->addError(__($message.$userId));
            }
        } else {
            $message = "Odoo Order Cannot be Canceled. Because, Order is Not Yet Synced at Odoo!!!";
            $this->messageManager->addError(__($message));
        }
    }

    public function checkErpOrderStatus($erpOrderId)
    {
        $state = false;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();

        $id = [new xmlrpcval($erpOrderId, 'int')];
        $key1 = [new xmlrpcval('state', 'string')];
        $msgSer = new xmlrpcmsg('execute');
        $msgSer->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msgSer->addParam(new xmlrpcval($userId, "int"));
        $msgSer->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msgSer->addParam(new xmlrpcval("sale.order", "string"));
        $msgSer->addParam(new xmlrpcval("read", "string"));
        $msgSer->addParam(new xmlrpcval($id, "array"));
        $msgSer->addParam(new xmlrpcval($key1, "array"));
        $resp = $client->send($msgSer);
        if ($resp->faultCode()) {
            $state = $resp->faultString();
        } else {
            $valueArray = $resp->value()->scalarval();
            $state = $valueArray[0]->me['struct']['state']->me['string'];
        }
        return $state;
    }
}
