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

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\View\Layout;
use Plumrocket\Base\Api\GetExtensionInformationInterface;
use Plumrocket\Base\Api\ModuleInformationInterface;
use Plumrocket\Base\Helper\Base;

/**
 * @since 2.3.0
 */
class GetInformation implements GetExtensionInformationInterface
{
    /**
     * @var string[]
     */
    private $services = [
        'Base',
        'Token',
        'AmpEmailApi',
    ];

    /**
     * @var \Plumrocket\Base\Api\ModuleInformationInterface[]
     */
    private $extensions;

    /**
     * @var \Plumrocket\Base\Model\Extensions\EmptyInformationFactory
     */
    private $emptyInformationFactory;

    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    private $baseHelper;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $configStructure;

    /**
     * @var \Magento\Framework\View\Layout
     */
    private $layout;

    /**
     * GetInformation constructor.
     *
     * @param \Plumrocket\Base\Model\Extensions\EmptyInformationFactory $emptyInformationFactory
     * @param \Plumrocket\Base\Helper\Base                              $baseHelper
     * @param \Magento\Config\Model\Config\Structure                    $configStructure
     * @param \Magento\Framework\View\Layout                            $layout
     * @param array                                                     $extensions
     */
    public function __construct(
        EmptyInformationFactory $emptyInformationFactory,
        Base $baseHelper,
        Structure $configStructure,
        Layout $layout,
        array $extensions = []
    ) {
        $this->extensions = $extensions;
        $this->emptyInformationFactory = $emptyInformationFactory;
        $this->baseHelper = $baseHelper;
        $this->configStructure = $configStructure;
        $this->layout = $layout;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $moduleName): ModuleInformationInterface
    {
        $moduleName = $this->extractName($moduleName);

        if (! isset($this->extensions[$moduleName])) {
            /** @var \Plumrocket\Base\Model\Extensions\EmptyInformation $emptyInformation */
            $emptyInformation = $this->emptyInformationFactory->create();

            /**
             * All helpers must extend base helper
             *
             * @var \Plumrocket\Base\Helper\Base $helper
             */
            $helper = $this->baseHelper->getModuleHelper($moduleName);

            if (method_exists($helper, 'getConfigSectionId')) {
                $emptyInformation->setConfigSection($helper->getConfigSectionId());
            } else {
                $emptyInformation->setConfigSection('');
            }

            $emptyInformation->setModuleName($moduleName);
            $emptyInformation->setIsService(in_array($moduleName, $this->services, true));

            list($wiki, $officialName) = $this->getDataFromVersionField($helper->getConfigSectionId());

            if ($wiki && $officialName) {
                $emptyInformation->setOfficialName($officialName);
                $emptyInformation->setWikiLink($wiki);
            }

            $this->extensions[$moduleName] = $emptyInformation;
        }

        return $this->extensions[$moduleName];
    }

    /**
     * @param string $maybeModuleFullName
     * @return string
     */
    private function extractName(string $maybeModuleFullName): string
    {
        if (false === strpos($maybeModuleFullName, '_')) {
            return $maybeModuleFullName;
        }

        return explode('_', $maybeModuleFullName)[1];
    }

    /**
     * @param string $configSectionId
     * @return array
     */
    private function getDataFromVersionField(string $configSectionId): array
    {
        $wikiLink = '';
        $officialName = '';

        $versionField = $this->configStructure->getElementByConfigPath("{$configSectionId}/general/version");
        if ($versionField instanceof Field && $versionField->getFrontendModel()) {
            /** @var \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version $versionBlock */
            $versionBlock = $this->layout->createBlock($versionField->getFrontendModel());
            if ($versionBlock) {
                $wikiLink = $versionBlock->getWikiLink();
                $officialName = $versionBlock->getModuleTitle();
            }

        }

        return [$wikiLink, $officialName];
    }
}
