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

namespace Plumrocket\RMA\Model\ResourceModel\Returns\Track;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Plumrocket\RMA\Model\Returns\Track',
            'Plumrocket\RMA\Model\ResourceModel\Returns\Track'
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
        $this->addFieldToFilter('parent_id', $returnsId);
        return $this;
    }
}
