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

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Tax
{
    /**
     * @return void
     */
    public function execute()
    {
        $taxId = (int)$this->getRequest()->getParam('id');
        $taxmodel = $this->_taxFactory->create();
        if ($taxId) {
            $taxmodel->load($taxId);
            if (!$taxmodel->getEntityId()) {
                $this->messageManager->addError(__('This Tax no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $taxmodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('tax_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_tax', $taxmodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Tax');
        } else {
            $breadcrumb = __('New Tax');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                    ->getTitle()
                    ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                    ->getTitle()
                    ->prepend($model->getId() ? $model->getName() : __('Tax Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::tax_view');
    }
}
