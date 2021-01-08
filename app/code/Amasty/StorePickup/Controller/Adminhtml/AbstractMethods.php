<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml;

abstract class AbstractMethods extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = "Amasty_StorePickup::amstorepick";

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Amasty_StorePickup::amstorepick')->_addBreadcrumb(
            __('Store Pickup'),
            __('Store Pickup')
        );

        return $this;
    }
}
