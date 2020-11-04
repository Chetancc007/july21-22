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

class CustomerSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerMapping,
        \Webkul\Odoomagentoconnect\Model\Customer $customerModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->messageManager = $messageManager;
        $this->_requestInterface = $requestInterface;
        $this->_customerMapping = $customerMapping;
        $this->_customerModel = $customerModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_connection = $connection;
    }

    /**
     * Customer save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        if ($route == "account" || $route == "index") {
            $customerId = $observer->getCustomer()->getEntityId();
            $mappingId = 0;
            $odooCustomerId = 0;
            if ($customerId) {
                $helper = $this->_connection;
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                $autoSync = $this->_scopeConfig->getValue('odoomagentoconnect/automatization_settings/auto_customer');
                $showMessages = $this->_scopeConfig
                                    ->getValue('odoomagentoconnect/additional/show_messages');
                if ($userId > 0 && $autoSync) {
                    $collection = $this->_customerModel
                                        ->getCollection()
                                        ->addFieldToFilter('address_id', ['eq'=>'customer'])
                                        ->addFieldToFilter('magento_id', ['eq'=>$customerId]);
                    $map = $collection->getFirstItem();
                    $mappingId = $map->getEntityId();
                    $odooCustomerId = $map->getOdooId();
                    if ($mappingId) {
                        $response = $this->_customerMapping
                                ->updateSpecificCustomer($mappingId, $customerId, $odooCustomerId);
                        if ($showMessages){
                            if ($response) {
                                $this->messageManager->addSuccess(__("Odoo Customer Successfully Updated."));
                            } else {
                                $this->messageManager->addError(__("Unable to update customer at Odoo."));
                            }
                        }
                    } else {
                        $response = $this->_customerMapping
                                ->exportSpecificCustomer($customerId);
                        if ($showMessages){
                            if ($response['odoo_id']) {
                                $this->messageManager->addSuccess(__("Odoo Customer Successfully created."));
                            } else {
                                $this->messageManager->addError(__("Unable to create customer at Odoo."));
                            }
                        }
                    }
                }
            }
        }
    }
}
