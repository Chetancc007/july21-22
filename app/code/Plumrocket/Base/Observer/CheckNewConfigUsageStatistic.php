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
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Base\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Change status of config statistic for specific module after save config
 *
 * @since 2.3.0
 */
class CheckNewConfigUsageStatistic implements ObserverInterface
{
    /**
     * @var \Plumrocket\Base\Model\Statistic\Usage\StatusInterface[]
     */
    private $statuses;

    /**
     * CheckNewConfigStatistic constructor.
     *
     * @param \Plumrocket\Base\Model\Statistic\Usage\StatusInterface[] $statuses
     */
    public function __construct(array $statuses = [])
    {
        $this->statuses = $statuses;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $section = $observer->getRequest()->getParam('section', '');

        if (isset($this->statuses[$section])) {
            $this->statuses[$section]->switchToCollect();
        }
    }
}
