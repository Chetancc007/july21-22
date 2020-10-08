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

namespace Plumrocket\Base\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @since 2.3.0
 */
class AddClassToBodyObserver implements ObserverInterface
{
    const THEME_CODE_DEFAULT = 'Magento/blank';
    const CSS_CLASS_PREFIX = 'pl-thm-';

    /** @var PageConfig */
    private $pageConfig;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var ThemeProviderInterface */
    private $themeProvider;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * AddClassToBody constructor.
     *
     * @param PageConfig             $pageConfig
     * @param ScopeConfigInterface   $scopeConfig
     * @param ThemeProviderInterface $themeProvider
     * @param StoreManagerInterface  $storeManager
     */
    public function __construct(
        PageConfig $pageConfig,
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->pageConfig = $pageConfig;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $themeId = $this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        $themeData = $this->themeProvider->getThemeById($themeId)->getData();
        $code = $themeData['code'] ?? self::THEME_CODE_DEFAULT;

        $vendorName = self::CSS_CLASS_PREFIX . mb_strstr(mb_strtolower($code), '/', true);
        $themeName = '' . self::CSS_CLASS_PREFIX . '' . str_replace('/', '-', mb_strtolower($code));

        $this->pageConfig
            ->addBodyClass($vendorName)
            ->addBodyClass($themeName);
    }
}
