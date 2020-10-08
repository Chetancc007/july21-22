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

namespace Plumrocket\Base\Api;

/**
 * Collect statistic for specific module
 * Must be added to composite collector for send
 * @see \Plumrocket\Base\Model\Statistic\Usage\CompositeCollector
 *
 * @since 2.3.0
 */
interface ModuleUsageStatisticCollectorInterface extends UsageStatisticCollectorInterface
{
    /**
     * Must return array
     *  [
     *      'config' => [
     *          'path' => [
     *              'label' => '',
     *              'value' => '',
     *              'options' => [
     *                  ['value' => value_1, 'label' => label_1],
     *                  ['value' => value_1, 'label' => label_1],
     *              ],
     *              'default' => ''
     *          ]
     *      ]
     *  ]
     *
     * @return array
     */
    public function collect(): array;

    /**
     * Check if something changed after last send
     *
     * @return bool
     */
    public function hasNewData(): bool;
}
