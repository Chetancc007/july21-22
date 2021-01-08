<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Methods;

class Edit extends \Amasty\StorePickup\Controller\Adminhtml\AbstractMethods
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Amasty\StorePickup\Model\MethodFactory
     */
    private $methodFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\StorePickup\Model\ResourceModel\Method
     */
    private $methodResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\StorePickup\Model\MethodFactory $methodFactory,
        \Amasty\StorePickup\Model\ResourceModel\Method $methodResource,
        \Magento\Backend\Model\Session\Proxy $session
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->methodFactory = $methodFactory;
        $this->methodResource = $methodResource;
        $this->session = $session;
    }

    public function execute()
    {
        /** @var \Amasty\StorePickup\Model\Method $model */
        $model = $this->methodFactory->create();
        $methodId = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Page $pageResult */
        $pageResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        if ($methodId) {
            $this->methodResource->load($model, $methodId);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));

                return $this->_redirect('amstorepick/*');
            }
        }
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);

        if (!empty($data)) {
            $model->addData($data);
        }
        $this->coreRegistry->register('current_amasty_storepick_method', $model);

        if ($model->getId()) {
            $title = __('Edit Store Pickup Method `%1`', $model->getName());
        } else {
            $title = __("Add new Store Pickup Method");
        }

        $pageResult->getConfig()->getTitle()->prepend($title);

        return $pageResult;
    }
}
