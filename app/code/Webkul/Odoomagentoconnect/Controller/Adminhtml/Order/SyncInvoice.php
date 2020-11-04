<?php
/**
 * Webkul Odoomagentoconnect Order SyncInvoice Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class SyncInvoice extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderModel,
        \Webkul\Odoomagentoconnect\Model\Order $orderMapping,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection
    ) {

        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->_connection = $connection;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::synchronize');
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $countNonInvoicedOrder = 0;
            $countInvoicedOrder = 0;
            $orderIds = '';
            $notInvoicedOrderIds = '';
            foreach ($collection->getItems() as $order) {
                $orderId = $order->getId();
                if ($order->hasInvoices()) {
                    $mapping = $this->_orderMapping
                                    ->getCollection()
                                    ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
                    if ($mapping->getSize() > 0) {
    	                foreach ($mapping as $map) {
    						$odooId = $map->getOdooId();
    	                    $inv = $this->_orderModel->invoiceOdooOrder($order, $odooId, false);
    	                    if (!$inv) {
    	                        $countNonInvoicedOrder++;
    	                    } else {
    	                        $countInvoicedOrder++;
    	                    }
    					}
                    } else {
                        $orderIds = $orderIds.$order->getIncrementId().',';
                    }
                } else {
                    $notInvoicedOrderIds = $notInvoicedOrderIds.$order->getIncrementId().',';
                }
            }

            if ($countNonInvoicedOrder) {
                $this->messageManager->addError(__('%1 order(s) cannot be Invoiced at Odoo.', $countNonInvoicedOrder));
            }

            if ($countInvoicedOrder) {
                $this->messageManager->addSuccess(__('%1 order(s) Invoiced at Odoo.', $countInvoicedOrder));
            }
            if ($orderIds) {
                $this->messageManager->addSuccess(__('%1 order(s) are not yet synchronized at Odoo.', $orderIds));
            }
            if ($notInvoicedOrderIds) {
                $this->messageManager->addError(__('%1 order(s) are not yet invoiced at Magento.', $notInvoicedOrderIds));
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Selected order(s) cannot be synchronized at Odoo. !! Reason : ".$errorMessage
                )
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
