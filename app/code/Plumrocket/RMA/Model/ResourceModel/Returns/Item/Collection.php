<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Plumrocket\RMA\Model\Returns\Item',
            'Plumrocket\RMA\Model\ResourceModel\Returns\Item'
        );
    }

    /**
     * Add filter by returns
     *
     * @param int $returnsId
     * @return $this
     */
    public function addReturnsFilter($returnsId)
    {
        $this->addFieldToFilter('parent_id', (int)$returnsId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($this->_items as $item) {
            foreach ($cols as $col) {
                if (null !== $item->getData($col)) {
                    $item->setData($col, (int)$item->getData($col));
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Add return data to collection
     *
     * @return $this
     */
    public function addReturnsData()
    {
        $this->join(
            ['r' => $this->getTable('plumrocket_rma_returns')],
            'r.entity_id = main_table.parent_id',
            ['*']
        );
        return $this;
    }

    /**
     * Add filter by order
     *
     * @param int $orderId
     * @return $this
     */
    /*public function addFilterByOrder($orderId)
    {
        $this->join(
            ['i' => 'mage_sales_order_item'],
            'i.item_id = main_table.order_item_id',
            []
        );
        $this->addFieldToFilter('order_id', (int)$orderId);
        return $this;
    }*/
}
