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

namespace Plumrocket\Base\Observer\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Plumrocket\Base\Model\System\Config\CurrentSection;

/**
 * Add handles for plumrocket sections
 *
 * @since 2.3.1
 */
class AddSystemConfigLayoutUpdateObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Plumrocket\Base\Model\System\Config\CurrentSection
     */
    private $currentSection;

    /**
     * AddSystemConfigLayoutUpdateObserver constructor.
     *
     * @param \Magento\Framework\App\RequestInterface             $request
     * @param \Plumrocket\Base\Model\System\Config\CurrentSection $currentSection
     */
    public function __construct(RequestInterface $request, CurrentSection $currentSection)
    {
        $this->request = $request;
        $this->currentSection = $currentSection;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ('adminhtml_system_config_edit' === $this->request->getFullActionName() &&
            $this->currentSection->isPlumrocketExtension()
        ) {
            /** @var \Magento\Framework\View\Layout\ProcessorInterface $update */
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle('pr_system_config_edit');
            $update->addHandle('pr_system_config_edit_' . $this->currentSection->getId());
        }
    }
}
