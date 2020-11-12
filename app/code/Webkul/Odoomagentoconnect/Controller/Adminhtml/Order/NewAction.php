<?php
/**
 * Webkul Odoomagentoconnect Order NewAction Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Order;

class NewAction extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $response = 0;
        $params = $this->getRequest()->getParams();
        $OrderId = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        if ($OrderId) {
            $thisOrder = $this->_saleOrderModel->load($OrderId);
            $response = $this->_orderModel
                            ->exportOrder($thisOrder);
        }

        if ($counter == $total) {
            if ($response > 0) {
                $success = ++$success;
            } else {
                $failure = ++$failure;
            }
            if ($failure > 0) {
                $message = ' Order(s) have not been Exported at Odoo for more details check logs.';
                $this->messageManager->addError(__($failure.$message));
            }
            if ($success) {
                $message = " Order(s) has been successfully Exported at Odoo.";
                $this->messageManager->addSuccess(__("Total ".$success.$message));
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
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::order_new');
    }
}
