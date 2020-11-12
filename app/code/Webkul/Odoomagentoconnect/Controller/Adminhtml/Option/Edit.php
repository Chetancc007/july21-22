<?php
/**
 * Webkul Odoomagentoconnect Option Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Option;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Option
{
    /**
     * @return void
     */
    public function execute()
    {
        $optionId = (int)$this->getRequest()->getParam('id');
        $optionmodel = $this->_optionFactory->create();
        if ($optionId) {
            $optionmodel->load($optionId);
            if (!$optionmodel->getEntityId()) {
                $this->messageManager->addError(__('This Attribute Option no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $optionmodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('option_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_option', $optionmodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Attribute Option');
        } else {
            $breadcrumb = __('New Attribute Option');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('Attribute Option Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::option_view');
    }
}
