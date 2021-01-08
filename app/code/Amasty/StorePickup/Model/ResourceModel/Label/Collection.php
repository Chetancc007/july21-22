<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Model\ResourceModel\Label;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init(
            'Amasty\StorePickup\Model\Label',
            'Amasty\StorePickup\Model\ResourceModel\Label'
        );
    }

    public function addFiltersByMethodIdStoreId($methodId, $storeId)
    {
        $this->getSelect()->reset('where');
        $this->clear()
            ->addFieldToFilter('method_id', $methodId)
            ->addFieldToFilter('store_id', $storeId);

        return $this;
    }
}
