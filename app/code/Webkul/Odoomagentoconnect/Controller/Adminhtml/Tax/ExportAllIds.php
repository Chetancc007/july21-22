<?php
/**
 * Webkul Odoomagentoconnect Tax Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Tax;

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
        \Webkul\Odoomagentoconnect\Model\Tax $taxMapping,
        \Magento\Tax\Model\Calculation\Rate $taxModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_taxMapping = $taxMapping;
        $this->_taxModel = $taxModel;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::tax_save');
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
            $taxColl = $this->_taxModel
                            ->getCollection()
                            ->getAllIds();
            foreach ($taxColl as $tax) {
                $mapping = $this->_taxMapping->getCollection()
                                             ->addFieldToFilter('magento_id', ['eq'=>$tax]);
                if ($mapping->getSize() == 0) {
                    array_push($exportIds, $tax);
                }
            }
            
            if (count($taxColl) == 0) {
                $this->messageManager->addSuccess(__("No Taxes are exist at Magento."));
            } else {
                if (count($exportIds) == 0) {
                    $this->messageManager->addSuccess(__("All Magento Taxes are already exported at Odoo."));
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Tax(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
