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

namespace Plumrocket\Base\Model;

/**
 * Class IsMarketplace
 * @since 2.1.6
 */
class IsModuleInMarketplace
{
    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    private $baseHelper;

    /**
     * IsMarketplaceByModuleName constructor.
     *
     * @param \Plumrocket\Base\Helper\Base $baseHelper
     */
    public function __construct(\Plumrocket\Base\Helper\Base $baseHelper)
    {
        $this->baseHelper = $baseHelper;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function execute($moduleName)
    {
        $moduleName = trim($moduleName, '\\');
        if (false !== strpos($moduleName, '_')) {
            $moduleName = explode('_', $moduleName)[1];
        } elseif (false !== strpos($moduleName, '\\')) {
            $moduleName = explode('\\', $moduleName)[1];
        }

        $modHelper = $this->baseHelper->getModuleHelper($moduleName);

        $dataOriginMethod = strrev('yeK'.'remo'.'tsuC'.'teg');
        $cKey = $modHelper->{$dataOriginMethod}();

        if (method_exists($modHelper, 'isMarketplace')) {
            return $modHelper->isMarketplace($cKey);
        }

        return false;
    }
}
