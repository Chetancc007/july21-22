<?php
/**
 * Webkul Odoomagentoconnect Currency Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Currency;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Currency
{
    /**
     * @return void
     */
    public function execute()
    {
        $currencyId = (int)$this->getRequest()->getParam('id');
        $currencymodel = $this->_currencyFactory->create();
        if ($currencyId) {
            $currencymodel->load($currencyId);
            if (!$currencymodel->getEntityId()) {
                $this->messageManager->addError(__('This Currency no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $currencymodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('currency_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_currency', $currencymodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Currency');
        } else {
            $breadcrumb = __('New Currency');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('Currency Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::currency_view');
    }
}
