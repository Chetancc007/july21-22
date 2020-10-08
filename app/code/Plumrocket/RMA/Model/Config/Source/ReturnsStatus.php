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

namespace Plumrocket\RMA\Model\Config\Source;

class ReturnsStatus extends AbstractSource
{
    /**
     * After create by customer, no one is authorized
     */
    const STATUS_NEW                = 'new';

    /**
     * (Only for return) At least one item is rejected and no one is authorized
     */
    const STATUS_REJECTED_PART      = 'rejected_part';

    /**
     * All items are declined or are not authorized
     */
    const STATUS_REJECTED           = 'rejected';

    /**
     * At least one item is authorized
     */
    const STATUS_AUTHORIZED_PART    = 'authorized_part';

    /**
     * All items are authorized
     */
    const STATUS_AUTHORIZED         = 'authorized';

    /**
     * At least one item is received
     */
    const STATUS_RECEIVED_PART      = 'received_part';

    /**
     * All items are received
     */
    const STATUS_RECEIVED           = 'received';

    /**
     * At least one item is approved
     */
    const STATUS_APPROVED_PART      = 'approved_part';

    /**
     * At least one item is approved, all items are finished
     */
    const STATUS_PROCESSED_CLOSED   = 'processed_closed';

    /**
     * Return was cancelled
     */
    const STATUS_CLOSED             = 'closed';

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::STATUS_NEW                => __('Pending'),
            self::STATUS_REJECTED_PART      => __('Partially Rejected'),
            self::STATUS_AUTHORIZED_PART    => __('Partially Authorized'),
            self::STATUS_AUTHORIZED         => __('Authorized'),
            self::STATUS_RECEIVED_PART      => __('Partially Received'),
            self::STATUS_RECEIVED           => __('Received'),
            self::STATUS_APPROVED_PART      => __('Partially Resolved'),
            self::STATUS_PROCESSED_CLOSED   => __('Resolved'),
            self::STATUS_CLOSED             => __('Cancelled'),
            self::STATUS_REJECTED           => __('Rejected'),
        ];
    }

    /**
     * Retrieve final statuses
     *
     * @return array
     */
    public function getFinalStatuses()
    {
        return [
            self::STATUS_PROCESSED_CLOSED   => __('Resolved'),
            self::STATUS_CLOSED             => __('Cancelled'),
            self::STATUS_REJECTED           => __('Rejected'),
        ];
    }
}
