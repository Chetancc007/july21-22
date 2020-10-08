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

/**
 * Base class fot extension Helper/Config
 *
 * @since 2.3.0
 */
abstract class AbstractConfig extends ConfigUtils
{
    const SECTION_ID = '';
    const GROUP_GENERAL = 'general';

    /**
     * All modules must return his status.
     * As not all modules have same config path or can be without config settings each module must define this method.
     * For simplify in general modules, you can use @see \Plumrocket\Base\Helper\AbstractConfig::isModuleEnabledInConfig
     *
     * @param null $store
     * @param null $scope
     * @return bool
     */
    abstract public function isModuleEnabled($store = null, $scope = null): bool;

    /**
     * Can be used for certain modules with config path like "SECTION_ID/general/enabled"
     *
     * @param null $store
     * @param null $scope
     * @return bool
     */
    protected function isModuleEnabledInConfig($store = null, $scope = null): bool
    {
        return (bool) $this->getConfigByGroup(static::GROUP_GENERAL, 'enabled', $store, $scope);
    }

    /**
     * Receive magento config value
     *
     * @param  string      $group second part of the path, e.g. "general"
     * @param  string      $path third part of the path, e.g. "enabled"
     * @param  string|int  $scopeCode
     * @param  string|null $scope
     * @return mixed
     */
    public function getConfigByGroup($group, $path, $scopeCode = null, $scope = null)
    {
        return $this->getConfig(
            implode('/', [static::SECTION_ID, $group, $path]),
            $scopeCode,
            $scope
        );
    }

    /**
     * Receive magento config value
     * Used for deep paths, like "pr_base/statistic/usage/enabled"
     *
     * @param  string      $group second part of the path
     * @param  string      $subGroup third part of the path
     * @param  string      $path fourth part of the path
     * @param  string|int  $scopeCode
     * @param  string|null $scope
     * @return mixed
     */
    public function getConfigBySubGroup($group, $subGroup, $path, $scopeCode = null, $scope = null)
    {
        return $this->getConfigByGroup($group . '/' . $subGroup, $path, $scopeCode, $scope);
    }
}
