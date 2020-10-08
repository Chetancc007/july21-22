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

use Magento\Framework\DataObject;
use Plumrocket\Base\Api\GetModuleVersionInterface;
use Plumrocket\Base\Api\ModuleInformationInterface;

/**
 * Container for information about extension
 * Use in case when module hasn't \Plumrocket\Base\Api\ModuleInformationInterface realise
 * @since 2.3.0
 */
class EmptyInformation extends DataObject implements ModuleInformationInterface
{
    /**
     * @var \Plumrocket\Base\Api\GetModuleVersionInterface
     */
    private $getModuleVersion;

    /**
     * EmptyInformation constructor.
     *
     * @param \Plumrocket\Base\Api\GetModuleVersionInterface $getModuleVersion
     * @param array                                          $data
     */
    public function __construct(
        GetModuleVersionInterface $getModuleVersion,
        array $data = []
    ) {
        parent::__construct($data);
        $this->getModuleVersion = $getModuleVersion;
    }

    /**
     * @inheritDoc
     */
    public function isService(): bool
    {
        return (bool) $this->_getData('is_service');
    }

    /**
     * @inheritDoc
     */
    public function getOfficialName(): string
    {
        return (string) $this->_getData('official_name');
    }

    /**
     * @inheritDoc
     */
    public function getWikiLink(): string
    {
        return (string) $this->_getData('wiki');
    }

    /**
     * @inheritDoc
     */
    public function getConfigSection(): string
    {
        return (string) $this->_getData('config_section');
    }

    /**
     * @inheritDoc
     */
    public function getModuleName(): string
    {
        return (string) $this->_getData('module_name');
    }

    /**
     * @inheritDoc
     */
    public function getVendorAndModuleName(): string
    {
        return (string) $this->_getData('full_module_name') ?: 'Plumrocket_' . $this->getModuleName();
    }

    /**
     * @inheritDoc
     */
    public function getInstalledVersion(): string
    {
        return $this->getModuleVersion->execute($this->getVendorAndModuleName());
    }

    /**
     * @param bool $isService
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setIsService(bool $isService): EmptyInformation
    {
        return $this->setData('is_service', $isService);
    }

    /**
     * @param string $officialName
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setOfficialName(string $officialName): EmptyInformation
    {
        return $this->setData('official_name', $officialName);
    }

    /**
     * @param string $configSection
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setConfigSection(string $configSection): EmptyInformation
    {
        return $this->setData('config_section', $configSection);
    }

    /**
     * @param string $wikiLink
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setWikiLink(string $wikiLink): EmptyInformation
    {
        return $this->setData('wiki', $wikiLink);
    }

    /**
     * @param string $moduleName
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setVendorAndModuleName(string $moduleName): EmptyInformation
    {
        return $this->setData('full_module_name', $moduleName);
    }

    /**
     * @param string $moduleName
     * @return \Plumrocket\Base\Model\Extensions\EmptyInformation
     */
    public function setModuleName(string $moduleName): EmptyInformation
    {
        return $this->setData('module_name', $moduleName);
    }
}
