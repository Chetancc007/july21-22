<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Methods;

use Magento\Backend\App\Action;

class Delete extends \Amasty\StorePickup\Controller\Adminhtml\AbstractMethods
{
    /**
     * @var \Amasty\StorePickup\Model\MethodFactory
     */
    private $methodFactory;

    /**
     * @var \Amasty\StorePickup\Model\ResourceModel\Method
     */
    private $methodResource;

    public function __construct(
        Action\Context $context,
        \Amasty\StorePickup\Model\MethodFactory $methodFactory,
        \Amasty\StorePickup\Model\ResourceModel\Method $methodResource
    ) {
        parent::__construct($context);

        $this->methodFactory = $methodFactory;
        $this->methodResource = $methodResource;
    }

    public function execute()
    {
        $methodId = $this->getRequest()->getParam('id');
        /** @var \Amasty\StorePickup\Model\Method $model */
        $model = $this->methodFactory->create();
        $this->methodResource->load($model, $methodId);

        if ($methodId && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('Record does not exist'));

            return $this->_redirect('*/*/');
        }

        try {
            $this->methodResource->delete($model);
            $this->messageManager->addSuccessMessage(
                __('Shipping method has been successfully deleted')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/');
    }
}
