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

namespace Plumrocket\Base\Model\Extensions;

use Plumrocket\Base\Api\GetModuleVersionInterface;
use Plumrocket\Base\Api\ModuleInformationInterface;

/**
 * @since 2.3.0
 */
class Information implements ModuleInformationInterface
{
    const IS_SERVICE = true;
    const NAME = 'Base';
    const WIKI = '';
    const CONFIG_SECTION = 'plumbase';
    const MODULE_NAME = 'Base';
    const VENDOR_NAME = 'Plumrocket';

    /**
     * @var \Plumrocket\Base\Api\GetModuleVersionInterface
     */
    private $getModuleVersion;

    /**
     * ModuleInformation constructor.
     *
     * @param \Plumrocket\Base\Api\GetModuleVersionInterface $getModuleVersion
     */
    public function __construct(GetModuleVersionInterface $getModuleVersion)
    {
        $this->getModuleVersion = $getModuleVersion;
    }

    /**
     * @inheritDoc
     */
    public function isService(): bool
    {
        return static::IS_SERVICE;
    }

    /**
     * @inheritDoc
     */
    public function getOfficialName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getWikiLink(): string
    {
        return static::WIKI;
    }

    /**
     * @inheritDoc
     */
    public function getConfigSection(): string
    {
        return static::CONFIG_SECTION;
    }

    /**
     * @inheritDoc
     */
    public function getModuleName(): string
    {
        return static::MODULE_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getVendorAndModuleName(): string
    {
        return static::VENDOR_NAME . '_' . $this->getModuleName();
    }

    /**
     * @inheritDoc
     */
    public function getInstalledVersion(): string
    {
        return $this->getModuleVersion->execute($this->getVendorAndModuleName());
    }
}
