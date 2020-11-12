<?php
/**
 * Webkul Odoomagentoconnect Order Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Order;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('id');
        $ordermodel = $this->_orderFactory->create();
        if ($orderId) {
            $ordermodel->load($orderId);
            if (!$ordermodel->getEntityId()) {
                $this->messageManager->addError(__('This Order no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $ordermodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('order_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_order', $ordermodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Order');
        } else {
            $breadcrumb = __('New Order');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('New Order'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::order_view');
    }
}
