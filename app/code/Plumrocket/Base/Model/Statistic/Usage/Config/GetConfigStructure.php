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

namespace Plumrocket\Base\Model\Statistic\Usage\Config;

use Magento\Config\Model\Config\Structure;

/**
 * @since 2.3.0
 */
class GetConfigStructure
{
    /**
     * @var \Magento\Config\Model\Config\StructureFactory
     */
    private $configStructureFactory;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $configStructure;

    /**
     * GetConfigStructure constructor.
     *
     * @param \Magento\Config\Model\Config\StructureFactory $configStructureFactory
     */
    public function __construct(\Magento\Config\Model\Config\StructureFactory $configStructureFactory)
    {
        $this->configStructureFactory = $configStructureFactory;
    }

    /**
     * @return \Magento\Config\Model\Config\Structure
     */
    public function execute(): Structure
    {
        if (null === $this->configStructure) {
            $this->configStructure = $this->configStructureFactory->create();
        }

        return $this->configStructure;
    }
}
