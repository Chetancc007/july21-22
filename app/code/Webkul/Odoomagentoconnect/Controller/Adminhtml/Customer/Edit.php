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

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Customer
{
    /**
     * @return void
     */
    public function execute()
    {
        $customerId=(int)$this->getRequest()->getParam('id');
        $customermodel=$this->_customerFactory->create();
        if ($customerId) {
            $customermodel->load($customerId);
            if (!$customermodel->getEntityId()) {
                $this->messageManager->addError(__('This Customer no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
   
        $userId = $customermodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('customer_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_customer', $customermodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Customer');
        } else {
            $breadcrumb = __('New Customer');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('New Customer'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::customers_view');
    }
}
