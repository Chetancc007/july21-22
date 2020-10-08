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

namespace Plumrocket\Base\Model;

use Magento\AdminNotification\Model\Feed;

/**
 * Plumrocket Base admin notification feed model
 */
class AdminNotificationFeed extends Feed
{
    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var \Plumrocket\Base\Model\ProductFactory
     */
    protected $baseProductFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Plumrocket\Base\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Backend\App\ConfigInterface                    $backendConfig
     * @param \Magento\AdminNotification\Model\InboxFactory           $inboxFactory
     * @param \Plumrocket\Base\Helper\Base                            $baseHelper
     * @param \Plumrocket\Base\Model\ProductFactory $baseProductFactory
     * @param \Magento\Backend\Model\Auth\Session                     $backendAuthSession
     * @param \Magento\Framework\Module\ModuleListInterface           $moduleList
     * @param \Magento\Framework\Module\Manager                       $moduleManager,
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory             $curlFactory
     * @param \Magento\Framework\App\DeploymentConfig                 $deploymentConfig
     * @param \Magento\Framework\App\ProductMetadataInterface         $productMetadata
     * @param \Magento\Framework\UrlInterface                         $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Plumrocket\Base\Helper\Base $baseHelper,
        \Plumrocket\Base\Model\ProductFactory $baseProductFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Plumrocket\Base\Helper\Config $config,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $backendConfig,
            $inboxFactory,
            $curlFactory,
            $deploymentConfig,
            $productMetadata,
            $urlBuilder,
            $resource,
            $resourceCollection,
            $data
        );

        $this->baseHelper = $baseHelper;
        $this->baseProductFactory = $baseProductFactory;
        $this->backendAuthSession  = $backendAuthSession;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->productMetadata = $productMetadata;
        $this->config = $config;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl(): string
    {
        if ($this->_feedUrl === null) {
            $this->_feedUrl = 'https://st' . 'ore.plumrocket'
             . '.c' . 'om/notifica' . 'tionma' . 'nager/feed' . '/' . 'index/';
        }

        $domain = parse_url($this->urlBuilder->getBaseUrl(), PHP_URL_HOST) ?: '';

        $url = $this->_feedUrl . 'domain/' . urlencode($domain);

        $modulesParams = [];
        foreach ($this->getAllPlumrocketModules() as $key => $module) {
            $key = str_replace('Plumrocket_', '', $key);
            $modulesParams[] = $key . ',' . $module['setup_version'] . ',' . $this->getNotificationKey($key);
        }

        if (count($modulesParams)) {
            $url .= '/modules/' . base64_encode(implode(';', $modulesParams));
        }

        if ($this->config->isEnabledNotifications() && $this->config->getEnabledNotificationLists()) {
            $url .= '/lists/' . implode('|', $this->config->getEnabledNotificationLists());
        } else {
            $url .= '/lists/none';
        }

        if ($this->config->isEnabledStatistic()) {
            $ed = $this->productMetadata->getEdition();
            $url .= '/platform/' . (($ed === 'Comm'.'unity') ? 'm2ce' : 'm2ee');
            $url .= '/edition/' . $ed;
            $url .= '/magento_version/' . $this->baseHelper->getMagento2Version();
        }

        return $url;
    }

    /**
     * Get Plumrocket extension info
     *
     * @return array[]
     */
    protected function getAllPlumrocketModules(): array
    {
        $modules = [];
        foreach ($this->moduleList->getAll() as $moduleName => $module) {
            if (strpos($moduleName, 'Plumrocket_') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $modules[$moduleName] = $module;
            }
        }
        return $modules;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        $session = $this->backendAuthSession;
        $time = time();
        $frequency = $this->getFrequency();
        if (($frequency + $session->getMfBaseNoticeLastUpdate() > $time)
            || ($frequency + $this->getLastUpdate() > $time)
        ) {
            return $this;
        }

        $session->setPANLastUpdate($time);
        parent::checkUpdate();
        return $this;
    }

    /**
     * Retrieve update frequency
     *
     * @return int
     */
    public function getFrequency(): int
    {
        return 86400;
    }

    /**
     * Retrieve last update time
     *
     * @return int
     */
    public function getLastUpdate(): int
    {
        return (int) $this->_cacheManager->load('plumrocket_admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'plumrocket_admin_notifications_lastcheck');
        return $this;
    }

    /**
     * Receive key
     *
     * @param string $name
     * @return string
     */
    public function getNotificationKey($name): string
    {
        $product = $this->baseProductFactory->create()
            ->setName($name);

        if ($product) {
            return implode(',', [
                $product->getCustomer(),
                $product->getSession()
            ]);
        }

        return '';
    }
}
