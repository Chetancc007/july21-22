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

declare(strict_types=1);

namespace Plumrocket\Base\Model\Statistic\Usage;

use Plumrocket\Base\Api\ModuleUsageStatisticCollectorInterface;

/**
 * @since 2.3.0
 */
class Collector implements ModuleUsageStatisticCollectorInterface
{
    /**
     * @var \Plumrocket\Base\Model\Statistic\Usage\StatusInterface
     */
    private $status;

    /**
     * @var \Plumrocket\Base\Api\UsageStatisticCollectorInterface[]
     */
    private $collectors;

    /**
     * Collector constructor.
     *
     * @param \Plumrocket\Base\Model\Statistic\Usage\StatusInterface $status
     * @param array                                                  $collectors
     */
    public function __construct(
        StatusInterface $status,
        array $collectors = []
    ) {
        $this->status = $status;
        $this->collectors = $collectors;
    }

    /**
     * @inheritDoc
     */
    public function collect(): array
    {
        $result = [];
        foreach ($this->collectors as $type => $collector) {
            $result[$type] = $collector->collect();
        }

        $this->status->switchToMiss();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function hasNewData(): bool
    {
        return $this->status->check();
    }
}
