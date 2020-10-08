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

use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionTrait;

class Collection extends AbstractCollection
{
    use CollectionTrait;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\Returns', 'Plumrocket\RMA\Model\ResourceModel\Returns');
    }

    /**
     * Add filter for not archive returns
     *
     * @return $this
     */
    public function addNotArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', false);
        return $this;
    }

    /**
     * Add filter for archive returns
     *
     * @return $this
     */
    public function addArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', true);
        return $this;
    }
}
