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
 * @package     Plumrocket_magento2.3.5
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Base\Model\System\Config;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Retrieve current section in system config and allow check if it's one of plumrocket sections
 *
 * @since 2.3.1
 */
class CurrentSection
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $configStructure;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Config\Model\Config\Structure  $configStructure
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Structure $configStructure,
        RequestInterface $request
    ) {
        $this->configStructure = $configStructure;
        $this->request = $request;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        if ($section = $this->get()) {
            return $section->getId();
        }

        return null;
    }

    /**
     * @return \Magento\Config\Model\Config\Structure\Element\Section|null
     */
    public function get()
    {
        $current = $this->request->getParam('section', '');

        if (! $current) {
            try {
                $section = $this->configStructure->getFirstSection();
            } catch (LocalizedException $e) {
                $section = null;
            }
        } else {
            $section = $this->configStructure->getElement($current);
            if (! $section instanceof Section) {
                $section = null;
            }
        }

        return $section;
    }

    /**
     * @return bool
     */
    public function isPlumrocketExtension(): bool
    {
        $section = $this->get();
        if ($section && isset($section->getData()['tab'])) {
            return 'plumrocket' === $section->getData()['tab'];
        }

        return false;
    }
}
