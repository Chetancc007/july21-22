<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Rates;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    /**
     * @var \Amasty\StorePickup\Helper\Data
     */
    private $helperSTR;

    /**
     * @var \Amasty\StorePickup\Model\RateFactory
     */
    private $rateFactory;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        Context $context,
        \Amasty\StorePickup\Helper\Data $helperSTR,
        \Amasty\StorePickup\Model\RateFactory $rateFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->helperSTR = $helperSTR;
        $this->rateFactory = $rateFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Amasty\StorePickup\Model\Rate $model */
        $model = $this->rateFactory->create();

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->messageManager->addError(__('Unable to find a rate to save'));
            $this->_redirect('adminhtml/amasty_storepick_method/index');

            return;
        }

        try {
            $methodId = $model->getMethodId();
            if (!$methodId) {
                $methodId = $data['method_id'];
            }

            $fullZipFrom = $this->helperSTR->getDataFromZip($data['zip_from']);
            $fullZipTo = $this->helperSTR->getDataFromZip($data['zip_to']);
            $data['num_zip_from'] = $fullZipFrom['district'];
            $data['num_zip_to'] = $fullZipTo['district'];
            $model->setData($data)->setId($id);
            $model->save();

            $msg = __('Rate has been successfully saved');
            $this->messageManager->addSuccess($msg);

            //fix for save and continue of new rates
            if (is_null($id)) {
                $id = $model->getId();
            }

            if ($this->getRequest()->getParam('to_method')) {
                $this->_redirect('amstorepick/methods/edit', ['id' => $methodId]);
            } else {
                $this->_redirect(
                    'amstorepick/rates/newAction',
                    [
                        'method_id' => $methodId,
                    ]
                );
            }

        } catch (\Exception $e) {
            $this->messageManager->addError(__('This rate already exist!'));
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/edit', ['id' => $id, 'method_id' => $methodId]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_StorePickup::amstorepick');
    }
}
