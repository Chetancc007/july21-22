<?php
/**
 * Webkul Odoomagentoconnect Attribute Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Attribute;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Attribute
{
    /**
     * @return void
     */
    public function execute()
    {
        $attributeId=(int)$this->getRequest()->getParam('id');
        $attributemodel=$this->_attributeFactory->create();
        if ($attributeId) {
            $attributemodel->load($attributeId);
            if (!$attributemodel->getEntityId()) {
                $this->messageManager->addError(__('This Attribute no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
   
        $userId = $attributemodel->getId();
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('attribute_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_attribute', $attributemodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Attribute');
        } else {
            $breadcrumb = __('New Attribute');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('Attribute Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::attribute_view');
    }
}
