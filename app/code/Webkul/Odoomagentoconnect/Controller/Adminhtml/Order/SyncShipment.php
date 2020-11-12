<?php
/**
 * Webkul Odoomagentoconnect Order SyncShipment Controller
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

class SyncShipment extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
            $countNonShippedOrder = 0;
            $countShippedOrder = 0;
            $orderIds = '';
            $notShippedOrderIds = '';
            foreach ($collection->getItems() as $order) {
                $orderId = $order->getId();
                if ($order->hasShipments()) {
                    $mapping = $this->_orderMapping
                                    ->getCollection()
                                    ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
                    if ($mapping->getSize() > 0) {
    	                foreach ($mapping as $map) {
    						$odooId = $map->getOdooId();
    	                    $inv = $this->_orderModel->deliverOdooOrder($order, $odooId, false);
    	                    if (!$inv) {
    	                        $countNonShippedOrder++;
    	                    } else {
    	                        $countShippedOrder++;
    	                    }
    					}
                    } else {
                        $orderIds = $orderIds.$order->getIncrementId().',';
                    }
                }  else {
                    $notShippedOrderIds = $notShippedOrderIds.$order->getIncrementId().',';
                }
            }

            if ($countNonShippedOrder) {
                $this->messageManager->addError(__('%1 order(s) cannot be Delivered at Odoo.', $countNonShippedOrder));
            }

            if ($countShippedOrder) {
                $this->messageManager->addSuccess(__('%1 order(s) Delivered at Odoo.', $countShippedOrder));
            }
            if ($orderIds) {
                $this->messageManager->addSuccess(__('%1 order(s) are not yet synchronized at Odoo.', $orderIds));
            }
            if ($notShippedOrderIds) {
                $this->messageManager->addError(__('%1 order(s) are not yet shipped at Magento.', $notShippedOrderIds));
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
