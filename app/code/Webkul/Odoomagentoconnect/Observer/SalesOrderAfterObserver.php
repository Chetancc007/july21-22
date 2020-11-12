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

class SalesOrderAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderMapping,
        \Webkul\Odoomagentoconnect\Model\Order $orderModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_orderMapping = $orderMapping;
        $this->_orderModel = $orderModel;
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Save After event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $lastOrderId = $observer->getEvent()->getData('order');
        $route = $this->_requestInterface->getControllerName();
        if ($route == "order" || $route == "order_shipment" || $route == "order_invoice") {
            return true;
        }

        $mapping = $this->_orderModel
                        ->getCollection()
                        ->addFieldToFilter('magento_id', ['eq'=>$lastOrderId->getEntityId()]);

        /** @var $orderInstance Order */
        if (count($mapping) == 0) {
            $autoOrder = $this->_scopeConfig
                                ->getValue('odoomagentoconnect/order_settings/auto_order');
            $showMessages = $this->_scopeConfig
                                ->getValue('odoomagentoconnect/additional/show_messages');
            if ($autoOrder == 1) {
                if (!$lastOrderId) {
                    return;
                }
                $helper = $this->_connection;
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                if ($userId > 0) {
                    $odooName = $this->_orderMapping->exportOrder($lastOrderId);
                    if ($odooName && $showMessages) {
                        $this->messageManager->addSuccess(__("Odoo Order ".$odooName[1]." Successfully Create."));
                    }
                }
            }
        }
    }
}
