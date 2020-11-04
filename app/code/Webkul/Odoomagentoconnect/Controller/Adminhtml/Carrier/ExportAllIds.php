<?php
/**
 * Webkul Odoomagentoconnect Carrier Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Carrier;

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
        \Webkul\Odoomagentoconnect\Model\Carrier $carrierMapping,
        \Magento\Shipping\Model\Config $carrierModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_carrierMapping = $carrierMapping;
        $this->_carrierModel = $carrierModel;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::carrier_save');
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
            $collection = $this->_carrierModel->getActiveCarriers();
            foreach ($collection as $shippigCode => $shippingModel) {
                $mapping = $this->_carrierMapping->getCollection()
                                             ->addFieldToFilter('carrier_code', ['eq'=>$shippigCode]);
                if ($mapping->getSize() == 0) {
                    array_push($exportIds, $shippigCode);
                }
            }
            
            if (count($collection) == 0) {
                $this->messageManager->addSuccess(__("No Attribute Carriers are exist at Magento."));
            } else {
                if (count($exportIds) == 0) {
                    $this->messageManager->addSuccess(
                        __(
                            "All Magento Attribute Carriers are already exported at Odoo."
                        )
                    );
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Carrier(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
