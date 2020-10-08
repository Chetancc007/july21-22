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

namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;

class Item extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_returns_item', 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /**@var $object \Plumrocket\RMA\Model\Returns\Item */
        if (! $object->getParentId() && $object->getReturns()) {
            $object->setParentId($object->getReturns()->getId());
        }

        if (! $object->getOrderItemId() && $object->getOrderItem()) {
            $object->setOrderItemId($object->getOrderItem()->getId());
        }

        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($cols as $col) {
            if ('' === $object->getData($col)) {
                $object->unsetData($col);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($cols as $col) {
            if (null !== $object->getData($col)) {
                $object->setData($col, (int)$object->getData($col));
            }
        }

        return parent::_afterLoad($object);
    }
}
