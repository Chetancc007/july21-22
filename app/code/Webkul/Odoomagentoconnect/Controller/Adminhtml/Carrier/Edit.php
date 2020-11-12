<?php
/**
 * Webkul Odoomagentoconnect Carrier Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Carrier;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Carrier
{
    /**
     * @return void
     */
    public function execute()
    {
        $carrierId = (int)$this->getRequest()->getParam('id');
        $carriermodel=$this->_carrierFactory->create();
        if ($carrierId) {
            $carriermodel->load($carrierId);
            if (!$carriermodel->getEntityId()) {
                $this->messageManager->addError(__('This Carrier no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
        }
      
        $userId = $carriermodel->getId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        $data = $this->_session->getUserData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('carrier_user', $model);
        $this->_coreRegistry->register('odoomagentoconnect_carrier', $carriermodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit Carrier');
        } else {
            $breadcrumb = __('New Carrier');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend(__('Users'));
        $this->_view->getPage()->getConfig()
                                ->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('Carrier Manual Mapping'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::carrier_view');
    }
}
