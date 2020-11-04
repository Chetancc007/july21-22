<?php
/**
 * Webkul Odoomagentoconnect Payment Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Payment;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Payment
{
    /**
     * @return void
     */
    public function execute()
    {
        $paymentId = (int)$this->getRequest()->getParam('id');
        $paymentmodel = $this->_paymentFactory->create();
        if ($paymentId) {
            $paymentmodel->load($paymentId);
            if (!$paymentmodel->getEntityId()) {
                $this->messageManager->addError(__('This Payment no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $paymentmodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('payment_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_payment', $paymentmodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Payment');
        } else {
            $breadcrumb = __('New Payment');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()
                    ->getConfig()
                    ->getTitle()
                    ->prepend(__('Users'));
        $this->_view->getPage()
                    ->getConfig()
                    ->getTitle()
                    ->prepend($model->getId() ? $model->getName() : __('Payment Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::payment_view');
    }
}
