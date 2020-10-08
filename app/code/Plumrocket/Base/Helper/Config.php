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

namespace Plumrocket\Base\Helper;

use Plumrocket\Base\Model\Extensions\Information;

/**
 * @since 2.3.0
 */
class Config extends AbstractConfig
{
    const SECTION_ID = Information::CONFIG_SECTION;
    const GROUP_NOTIFICATIONS = 'notifications';
    const GROUP_SYSTEM = 'system';

    /**
     * @param null $store
     * @param null $scope
     * @return bool
     */
    public function isModuleEnabled($store = null, $scope = null): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabledNotifications(): bool
    {
        return (bool) $this->getConfigByGroup(self::GROUP_NOTIFICATIONS, 'enabled');
    }

    /**
     * @return array
     */
    public function getEnabledNotificationLists(): array
    {
        return $this->prepareMultiselectValue(
            (string) $this->getConfigByGroup(self::GROUP_NOTIFICATIONS, 'subscribed_to')
        );
    }

    /**
     * @return bool
     */
    public function isEnabledStatistic(): bool
    {
        return (bool) $this->getConfigByGroup(self::GROUP_SYSTEM, 'enabled_statistic');
    }
}
