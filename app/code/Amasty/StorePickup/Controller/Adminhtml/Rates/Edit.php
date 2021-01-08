<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Rates;

use Magento\Backend\App\Action;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Amasty_StorePickup::amstorepick';

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\StorePickup\Model\RateFactory
     */
    private $rateFactory;

    /**
     * @var \Amasty\StorePickup\Model\ResourceModel\Rate
     */
    private $rateResource;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Session\Proxy $session,
        \Amasty\StorePickup\Model\RateFactory $rateFactory,
        \Amasty\StorePickup\Model\ResourceModel\Rate $rateResource
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        $this->rateFactory = $rateFactory;
        $this->rateResource = $rateResource;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $pageResult */
        $pageResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $rateId = $this->getRequest()->getParam('id');
        $methodId = $this->getRequest()->getParam('method_id');

        /** @var \Amasty\StorePickup\Model\Rate $objectRate */
        $objectRate = $this->rateFactory->create();
        $this->rateResource->load($objectRate, $rateId);

        $data = $this->session->getPageData(true);

        if (!empty($data)) {
            $objectRate->setData($data);
        }

        if ($methodId && !$objectRate->getId()) {
            $objectRate->setMethodId($methodId);
            $objectRate->setWeightFrom('0');
            $objectRate->setQtyFrom('0');
            $objectRate->setPriceFrom('0');
            $objectRate->setWeightTo($objectRate::MAX_VALUE);
            $objectRate->setQtyTo($objectRate::MAX_VALUE);
            $objectRate->setPriceTo($objectRate::MAX_VALUE);
        }

        $this->coreRegistry->register('amasty_storepick_rate', $objectRate);

        $pageResult->setActiveMenu('Amasty_StorePickup::amstorepick');
        $pageResult->addBreadcrumb(__('Store Pickup'), __('Store Pickup'));
        $pageResult->getConfig()->getTitle()->prepend('Store Configuration');

        return $pageResult;
    }
}
