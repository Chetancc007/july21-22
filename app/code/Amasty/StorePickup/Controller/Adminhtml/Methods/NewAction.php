<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Controller\Adminhtml\Methods;

class NewAction extends \Amasty\StorePickup\Controller\Adminhtml\AbstractMethods
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
