<?php
/**
 * Webkul Odoomagentoconnect Set Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Set;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Set
{
    /**
     * @return void
     */
    public function execute()
    {
        $setId = (int)$this->getRequest()->getParam('id');
        $setmodel = $this->_setFactory->create();
        if ($setId) {
            $setmodel->load($setId);
            if (!$setmodel->getEntityId()) {
                $this->messageManager->addError(__('This Attribute Set no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
        $userId = $setmodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('set_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_set', $setmodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Attribute Set');
        } else {
            $breadcrumb = __('New Attribute Set');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('Attribute Set Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::set_view');
    }
}
