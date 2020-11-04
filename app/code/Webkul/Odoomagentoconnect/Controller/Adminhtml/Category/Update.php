<?php
/**
 * Webkul Odoomagentoconnect Category Update Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Category;

class Update extends \Magento\Backend\App\Action
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
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\Odoomagentoconnect\Model\Category $categoryMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Category $categoryModel
    ) {
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_categoryMapping = $categoryMapping;
        $this->_categoryModel = $categoryModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::category_save');
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
        $categoryId = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        if ($categoryId) {
            $categoryObj = $this->_categoryMapping->load($categoryId);
            $mageId = $categoryObj->getMagentoId();
            $odooId = $categoryObj->getOdooId();
            $response = $this->_categoryModel
                            ->createSpecificCategory($mageId, 'write', $odooId, $categoryObj);
        }
        if ($counter == $total) {
            if ($response['odoo_id'] > 0) {
                $success = ++$success;
            } else {
                $failure = ++$failure;
            }
            if ($failure > 0) {
                $message = ' Category(s) have not been updated at Odoo for more details check logs.';
                $this->messageManager->addError(__($failure.$message));
            }
            if ($success) {
                $message = " Category(s) has been successfully updated at Odoo.";
                $this->messageManager->addSuccess(__("Total ".$success.$message));
            }
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode(['response'=>$response]));
    }
}
