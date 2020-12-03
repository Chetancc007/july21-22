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

class SalesOrderShipmentAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderMapping,
        \Magento\Sales\Model\Order $salesModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_salesModel = $salesModel;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Shipment After event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        /** @var $orderInstance Order */
        $shipmentObj = $observer->getEvent()->getShipment();
        $orderId = $observer->getEvent()->getShipment()->getOrderId();

        $autoShipment = $this->_scopeConfig
                                ->getValue('odoomagentoconnect/order_settings/ship_order');
        $showMessages = $this->_scopeConfig
                            ->getValue('odoomagentoconnect/additional/show_messages');
        if ($autoShipment==1 && $orderId && in_array($route, ['order', 'order_shipment', 'order_invoice', 'manual', 'order_create', 'express'])) {
            $mappingcollection = $this->_orderModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
            if ($mappingcollection->getSize() > 0) {
                $thisOrder = $this->_salesModel->load($orderId);
                $helper = $this->_connection;
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                if ($userId > 0) {
                    foreach ($mappingcollection as $map) {
                        $odooId = $map->getOdooId();
                        if ($odooId > 0) {
                            $odooName = $map->getOdooOrder();
                            $status = $this->checkErpDoStatus($odooId);
                            if (!$status) {
                                $inv = $this->_orderMapping
                                            ->deliverOdooOrder($thisOrder, $odooId, $shipmentObj);
                                if ($showMessages){
                                    if (!$inv) {
                                        $message = "Odoo Order ".$odooName." Not Shipped, check Log for more details!!!";
                                        $this->messageManager->addError(__($message));
                                    } else {
                                        $message = "Odoo Order ".$odooName." Successfully Shipped.";
                                        $this->messageManager->addSuccess(__($message));
                                    }
                                }
                            } elseif ($status == true && $showMessages) {
                                $message = "Odoo Order ".$odooName." is Already Delivered.";
                                $this->messageManager->addNotice(__($message));
                            } elseif ($showMessages) {
                                $message = "Odoo Order ".$odooName." Not Shipped, check Log for more details!!!";
                                $this->messageManager->addError(__($message));
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkErpDoStatus($erpOrderId)
    {
        $ship = false;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();

        $id = [new xmlrpcval($erpOrderId, 'int')];
        $key1 = [new xmlrpcval('is_shipped', 'string')];
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
            $ship = $resp->faultString();
        } else {
            $valueArray = $resp->value()->scalarval();
            $ship = $valueArray[0]->me['struct']['is_shipped']->me['boolean'];
        }
        return $ship;
    }
}
