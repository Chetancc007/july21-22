<?php
/**
 * Webkul Odoomagentoconnect Product Update Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Product;

class Update extends \Magento\Backend\App\Action
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productModel,
        \Webkul\Odoomagentoconnect\Model\Product $productMapping,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_productMapping = $productMapping;
        $this->_productModel = $productModel;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }
    /**
     * @return void
     */
    public function execute()
    {
        $count = 0;
        $fail = 0;
        $response = 0;
        $params = $this->getRequest()->getParams();
        $productId = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        if ($productId) {
            $productObj = $this->_productMapping->load($productId);
            $response = $this->_productModel->updateNormalProduct($productObj);
        }
        if ($counter == $total) {
            if ($response['odoo_id'] > 0) {
                $success = ++$success;
            } else {
                $failure = ++$failure;
            }
            if ($failure > 0) {
                $this->messageManager
                    ->addError(__($failure.' Product(s) have not been updated at Odoo for more details check logs.'));
            }
            if ($success) {
                $this->messageManager
                    ->addSuccess(__("Total ".$success." Product(s) has been successfully updated at Odoo."));
            }
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode(['response'=>$response]));
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::product_new');
    }
}
