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

namespace Plumrocket\RMA\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * @param ReturnsStatus $status
     */
    public function __construct(ReturnsStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->status->toOptionArray();
    }
}
