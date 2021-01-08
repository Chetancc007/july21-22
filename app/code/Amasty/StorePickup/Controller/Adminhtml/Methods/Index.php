<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Methods;

class Index extends \Amasty\StorePickup\Controller\Adminhtml\AbstractMethods
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $pageResult */
        $pageResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('Amasty_StorePickup::amstorepick');
        $pageResult->addBreadcrumb(__('Store Pickup'), __('Store Pickup'));
        $pageResult->getConfig()->getTitle()->prepend(__('Store Pickups Methods'));

        return $pageResult;
    }
}
