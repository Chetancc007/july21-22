<?php
/**
 * Webkul Odoomagentoconnect Order ExportAllIds Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Order;

class ExportAllIds extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderMapping,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {

        $this->_orderMapping = $orderMapping;
        $this->_orderModel = $orderModel;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::order_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $exportIds = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $mapOrders = [];
            $orderCollection = $this->_orderMapping->getCollection()
                                         ->addFieldToSelect('magento_id')->getData();
            foreach ($orderCollection as $value) {
                array_push($mapOrders, $value['magento_id']);
            }
            if ($mapOrders) {
                $collection = $this->_orderModel->getCollection()
                                        ->addAttributeToFilter('entity_id', ['nin' => $mapOrders])
                                        ->addAttributeToFilter('status', ['neq' => 'canceled'])
                                        ->addAttributeToSelect('entity_id');
            } else {
                $collection = $this->_orderModel->getCollection()
                                        ->addAttributeToFilter('status', ['neq' => 'canceled'])
                                        ->addAttributeToSelect('entity_id');
            }
            $exportIds = $collection->getAllIds();

            if (count($collection) == 0) {
                $this->messageManager->addSuccess(__("All Magento Orders are already exported at Odoo."));
            } else {
                if (count($exportIds) == 0) {
                    $this->messageManager->addSuccess(
                        __(
                            "All Magento Orders are already exported at Odoo."
                        )
                    );
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Order(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
