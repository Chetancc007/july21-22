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

class SalesOrderInvoiceAfterObserver implements ObserverInterface
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
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_salesModel = $salesModel;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Invoice After event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        /** @var $orderInstance Order */
        $invoice = $observer->getEvent()->getInvoice();
        $orderId = $invoice->getOrderId();
        $invoiceNumber = $invoice->getIncrementId();

        $autoInvoice = $this->_scopeConfig->getValue('odoomagentoconnect/order_settings/invoice_order');
        $showMessages = $this->_scopeConfig
                            ->getValue('odoomagentoconnect/additional/show_messages');
        if ($autoInvoice == 1 && $route == 'order_invoice') {
            $mappingcollection = $this->_orderModel
                                    ->getCollection()
                                    ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
            
            if ($mappingcollection->getSize() > 0) {
                $thisOrder = $this->_salesModel->load($orderId);
                $helper = $this->_connection;
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                $context = $helper->getOdooContext();
                $client = $helper->getClientConnect();
                if ($userId > 0) {
                    foreach ($mappingcollection as $map) {
                        $odooId = $map->getOdooId();
                        if ($odooId > 0) {
                            $partnerId = $map->getOdooCustomerId();
                            $odooName = $map->getOdooOrder();
                            $status = $this->checkErpInvoiceStatus($odooId);
                            $inv = $this->_orderMapping
                                        ->invoiceOdooOrder($thisOrder, $odooId, $invoiceNumber);
                            if($showMessages) {
                                if (!$inv) {
                                    $message = "Odoo Order ".$odooName." Not Invoiced, check Log for more details!!!";
                                    $this->messageManager->addError(__($message));
                                } else {
                                    $message = "Odoo Order ".$odooName." Successfully Invoiced.";
                                    $this->messageManager->addSuccess(__($message));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkErpInvoiceStatus($erpOrderId)
    {
        $invoice = false;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();

        $id = [new xmlrpcval($erpOrderId, 'int')];
        $key1 = [new xmlrpcval('is_invoiced', 'string')];
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
            $invoice = $resp->faultString();
        } else {
            $valueArray = $resp->value()->scalarval();
            
            $invoice = $valueArray[0]->me['struct']['is_invoiced']->me['boolean'];
        }
        return $invoice;
    }
}
