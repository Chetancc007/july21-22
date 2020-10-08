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

namespace Plumrocket\RMA\Model;

class Reason extends AbstractModel
{
    /**
     * Reason payers
     */
    const PAYER_OWNER = 1;
    const PAYER_CUSTOMER = 2;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\Plumrocket\RMA\Model\ResourceModel\Reason');
    }

    /**
     * Prepare reason's payers
     * @return array
     */
    public function getAvailablePayers()
    {
        return [self::PAYER_OWNER => __('Store Owner'), self::PAYER_CUSTOMER => __('Customer')];
    }
}
