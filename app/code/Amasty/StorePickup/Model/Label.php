<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Model;

use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel
{

    protected function _construct()
    {
        $this->_init('Amasty\StorePickup\Model\ResourceModel\Label');
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\Context $context
    ) {
        parent::__construct($context, $coreRegistry);
    }
}
