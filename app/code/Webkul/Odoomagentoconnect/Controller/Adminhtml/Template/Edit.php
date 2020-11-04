<?php
/**
 * Webkul Odoomagentoconnect Template Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Template;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Template
{
    /**
     * @return void
     */
    public function execute()
    {
        $templateId = (int)$this->getRequest()->getParam('id');
        $templatemodel = $this->_templateFactory->create();
        if ($templateId) {
            $templatemodel->load($templateId);
            if (!$templatemodel->getEntityId()) {
                $this->messageManager->addError(__('This Template no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $templatemodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('template_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_template', $templatemodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Template');
        } else {
            $breadcrumb = __('New Template');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()->getTitle()
                                            ->prepend($model->getId() ? $model->getName() : __('New Template'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::template_view');
    }
}
