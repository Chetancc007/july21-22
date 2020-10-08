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

use Plumrocket\Base\Api\GetExtensionInformationInterface;
use Plumrocket\Base\Api\UsageStatisticCollectorInterface;
use Plumrocket\Base\Helper\Base;
use Plumrocket\Base\Helper\Config;

/**
 * Class for integrate and merge other collectors data
 *
 * @since 2.3.0
 */
class CompositeCollector implements UsageStatisticCollectorInterface
{
    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    private $baseHelper;

    /**
     * @var \Plumrocket\Base\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Base\Api\GetExtensionInformationInterface
     */
    private $getExtensionInformation;

    /**
     * @var \Plumrocket\Base\Api\ModuleUsageStatisticCollectorInterface[]
     */
    private $moduleCollectors;

    /**
     * CompositeCollector constructor.
     *
     * @param \Plumrocket\Base\Helper\Base                          $baseHelper
     * @param \Plumrocket\Base\Helper\Config                        $config
     * @param \Plumrocket\Base\Api\GetExtensionInformationInterface $getExtensionInformation
     * @param array                                                 $moduleCollectors
     */
    public function __construct(
        Base $baseHelper,
        Config $config,
        GetExtensionInformationInterface $getExtensionInformation,
        array $moduleCollectors = []
    ) {
        $this->baseHelper = $baseHelper;
        $this->config = $config;
        $this->getExtensionInformation = $getExtensionInformation;
        $this->moduleCollectors = $moduleCollectors;
    }

    /**
     * @inheritDoc
     */
    public function collect(): array
    {
        $modulesData = [];
        foreach ($this->moduleCollectors as $moduleName => $moduleCollector) {
            if (! $moduleCollector->hasNewData()) {
                continue;
            }

            $extensionInformation = $this->getExtensionInformation->execute($moduleName);
            $serialKey = (string) $this->config->getConfig(
                "{$extensionInformation->getConfigSection()}/general/serial"
            );

            $moduleHelper = $this->baseHelper->getModuleHelper($moduleName);

            $modulesData[$moduleName] = [
                'license_key' => $serialKey,
                'customer_hash'=> $moduleHelper->getCustomerKey(),
                'statistic' => $moduleCollector->collect()
            ];
        }

        return $modulesData;
    }
}
