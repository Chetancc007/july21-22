<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Rates;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addError(__('Unable to find a rate to delete'));
            $this->_redirect('amstorepick/methods/index');
            return;
        }

        try {
            /**
             * @var \Amasty\StorePickup\Model\Rate $rate
             */
            $rate = $this->_objectManager->create('Amasty\StorePickup\Model\Rate')->load($id);
            $methodId = $rate->getMethodId();
            $rate->delete();

            $this->messageManager->addSuccess(__('Rate has been deleted'));
            $this->_redirect('amstorepick/methods/edit',
                [
                    'id' => $methodId,
                    'tab' => 'rates_section'
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('amstorepick/methods/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_StorePickup::amstorepick');
    }
}
