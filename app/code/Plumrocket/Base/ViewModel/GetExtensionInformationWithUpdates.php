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

namespace Plumrocket\Base\ViewModel;

use Magento\Framework\Exception\NotFoundException;
use Plumrocket\Base\Api\GetExtensionInformationInterface;
use Plumrocket\Base\Model\Extensions\GetListOfInstalled;
use Plumrocket\Base\Model\Extensions\GetUpdates;

/**
 * @since 2.3.0
 */
class GetExtensionInformationWithUpdates
{
    /**
     * @var \Plumrocket\Base\Model\Extensions\GetListOfInstalled
     */
    private $getPlumrocketInstalledExtensions;

    /**
     * @var \Plumrocket\Base\Model\Extensions\GetUpdates
     */
    private $getUpdates;

    /**
     * @var \Plumrocket\Base\Api\GetExtensionInformationInterface
     */
    private $getExtensionInformation;

    /**
     * GetExtensionInformationWithUpdates constructor.
     *
     * @param \Plumrocket\Base\Model\Extensions\GetListOfInstalled  $getPlumrocketInstalledExtensions
     * @param \Plumrocket\Base\Model\Extensions\GetUpdates          $getUpdates
     * @param \Plumrocket\Base\Api\GetExtensionInformationInterface $getExtensionInformation
     */
    public function __construct(
        GetListOfInstalled $getPlumrocketInstalledExtensions,
        GetUpdates $getUpdates,
        GetExtensionInformationInterface $getExtensionInformation
    ) {
        $this->getPlumrocketInstalledExtensions = $getPlumrocketInstalledExtensions;
        $this->getUpdates = $getUpdates;
        $this->getExtensionInformation = $getExtensionInformation;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $extensions = $this->getPlumrocketInstalledExtensions->execute();
        try {
            $updates = $this->getUpdates->execute($extensions);
        } catch (NotFoundException $e) {
            $updates = false;
        }

        $result = [];
        foreach ($extensions as $extensionName) {
            $extensionInfo = $this->getExtensionInformation->execute($extensionName);
            if ($extensionInfo->isService()) {
                continue;
            }

            $extensionData = [
                'name' => $extensionInfo->getOfficialName(),
                'wiki' => $extensionInfo->getWikiLink(),
                'installedVersion' => $extensionInfo->getInstalledVersion(),
            ];

            if (false !== $updates) {
                $extensionData['successFetchUpdates'] = true;
                $extensionData['updates'] = $updates[$extensionName];
            } else {
                $extensionData['successFetchUpdates'] = false;
                $extensionData['updates'] = [];
            }

            $result[] = $extensionData;
        }

        usort(
            $result,
            static function ($extension1, $extension2) {
                $hasUpdates1 = empty($extension1['updates']);
                $hasUpdates2 = empty($extension2['updates']);
                if ($hasUpdates1 !== $hasUpdates2) {
                    return $hasUpdates1 > $hasUpdates2;
                }

                return $extension1['name'] > $extension2['name'];
            }
        );

        return $result;
    }
}
