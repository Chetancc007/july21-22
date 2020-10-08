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

namespace Plumrocket\RMA\Block\Returns\PrintPage;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Items extends \Plumrocket\RMA\Block\Returns\Items
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];
        foreach (parent::getItems() as $item) {
            $status = $this->itemHelper->getStatus($item);
            if (in_array($status, array_keys($this->status->getFinalStatuses()))
                || $status === ReturnsStatus::STATUS_NEW
            ) {
                continue;
            }

            if ($this->itemHelper->isVirtual($item->getOrderItem())) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}
