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
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Reason;

use Plumrocket\RMA\Model\Reason;
use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 * @method Reason|null getItemById($idValue)
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Reason::class, \Plumrocket\RMA\Model\ResourceModel\Reason::class);
    }

    /**
     * Add filter by owner payer
     *
     * @return $this
     */
    public function addPayerOwnerFilter()
    {
        $this->addFieldToFilter('payer', Reason::PAYER_OWNER);
        return $this;
    }
}
