<?php
/**
 * Webkul Odoomagentoconnect Customer Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Customer;

class UpdateAllIds extends \Magento\Backend\App\Action
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
        \Webkul\Odoomagentoconnect\Model\Customer $customerMapping,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_customerMapping = $customerMapping;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::customer_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $updateIds = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $customerMappingCollection = $this->_customerMapping
                                                ->getCollection();
            if (count($customerMappingCollection) == 0) {
                $this->messageManager->addError(__("Sorry, No Magento Customers are found to updated at Odoo."));
            } else {
                $updateIds = $customerMappingCollection->addFieldToFilter('address_id', ['eq'=>'customer'])
                                                        ->addFieldToFilter('need_sync', ['eq'=>'yes'])->getAllIds();
                if (count($updateIds) == 0) {
                    $this->messageManager->addError(__("Magento Customers are already updated at Odoo."));
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Customer(s) have not been Updated at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($updateIds));
    }
}
