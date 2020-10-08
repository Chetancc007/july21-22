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

namespace Plumrocket\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class LayoutLoadBeforeObserver
 * @since 2.1.6
 */
class LayoutLoadBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Plumrocket\Base\Model\IsModuleInMarketplace
     */
    private $isModuleInMarketplace;

    /**
     * LayoutLoadBeforeObserver constructor.
     *
     * @param \Magento\Framework\App\RequestInterface      $request
     * @param \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace
    ) {
        $this->request = $request;
        $this->isModuleInMarketplace = $isModuleInMarketplace;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ('adminhtml_system_config_edit' === $this->request->getFullActionName()
            && $this->isModuleInMarketplace->execute('Plumrocket_Base')
        ) {
            /** @var \Magento\Framework\View\Layout\ProcessorInterface $update */
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addUpdate('<head><css src="Plumrocket_Base::css/system/config.css"/></head>');
        }
    }
}
