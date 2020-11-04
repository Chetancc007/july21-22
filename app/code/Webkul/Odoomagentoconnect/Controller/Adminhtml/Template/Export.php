<?php
/**
 * Webkul Odoomagentoconnect Template Export Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Template;

class Export extends \Magento\Backend\App\Action
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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_templateModel = $templateModel;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::template_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $response = 0;
        $params = $this->getRequest()->getParams();
        $productId = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        if ($productId) {
            $response = $this->_templateModel
                            ->exportSpecificConfigurable($productId);
            if ($response['odoo_id'] > 0) {
                $erpTemplateId = $response['odoo_id'];
                $this->_templateModel
                    ->syncConfigChildProducts($productId, $erpTemplateId);
            }
        }

        if ($counter == $total) {
            if ($response['odoo_id'] > 0) {
                $success = ++$success;
            } else {
                $failure = ++$failure;
            }
            if ($failure > 0) {
                $message = ' Configurable Product(s) have not been Exported at Odoo for more details check logs.';
                $this->messageManager
                    ->addError(__($failure.$message));
            }
            if ($success) {
                $message = " Configurable Product(s) has been successfully Exported at Odoo.";
                $this->messageManager
                    ->addSuccess(__("Total ".$success.$message));
            }
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode(['response'=>$response]));
    }
}
