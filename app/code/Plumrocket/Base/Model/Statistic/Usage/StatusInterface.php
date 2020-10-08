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

/**
 * @since 2.3.0
 */
interface StatusInterface
{
    /**
     * @return bool
     */
    public function check(): bool;

    /**
     * @return \Plumrocket\Base\Model\Statistic\Usage\StatusInterface
     */
    public function switchToCollect(): StatusInterface;

    /**
     * @return \Plumrocket\Base\Model\Statistic\Usage\StatusInterface
     */
    public function switchToMiss(): StatusInterface;
}
