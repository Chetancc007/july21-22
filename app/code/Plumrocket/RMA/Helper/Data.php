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
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Config\Model\Config as BaseConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\ResourceModel\Returnrule\CollectionFactory;
use Plumrocket\RMA\Model\ReturnruleFactory;
use Plumrocket\RMA\Model\Returnrule\SpaceFactory;

class Data extends Main
{
    /**
     * Config section id
     */
    const SECTION_ID = 'prrma';

    /**
     * Configuration path to enable module
     */
    const MODULE_ENABLED_PATH = 'general/enabled';

    /**
     * Param name for store form data in session
     */
    const FORM_DATA_PARAM = 'prrma_form_data';

    /**
     * @var Session
     */
    protected $session;

    /**
     * Backend Url
     *
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var ReturnruleFactory
     */
    protected $returnruleFactory;

    /**
     * @var SpaceFactory
     */
    protected $spaceFactory;

    /**
     * @var CollectionFactory
     */
    protected $returnruleCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ResourceConnection
     */
    protected $config;

    /**
     * Storage of form data
     *
     * @var mixed
     */
    protected $formData = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Context                $context
     * @param Session                $session
     * @param UrlInterface           $backendUrl
     * @param ReturnruleFactory      $returnruleFactory
     * @param SpaceFactory           $spaceFactory
     * @param CollectionFactory      $returnruleCollectionFactory
     * @param StoreManagerInterface  $storeManager
     * @param ResourceConnection     $resourceConnection
     * @param BaseConfig             $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        Session $session,
        UrlInterface $backendUrl,
        ReturnruleFactory $returnruleFactory,
        SpaceFactory $spaceFactory,
        CollectionFactory $returnruleCollectionFactory,
        StoreManagerInterface $storeManager,
        State $state,
        ResourceConnection $resourceConnection,
        BaseConfig $config
    ) {
        $this->session = $session;
        $this->backendUrl = $backendUrl;
        $this->returnruleFactory = $returnruleFactory;
        $this->spaceFactory = $spaceFactory;
        $this->returnruleCollectionFactory = $returnruleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = self::SECTION_ID;
    }

    /**
     * Is module enabled
     *
     * @param  int $store store id
     * @return boolean
     */
    public function moduleEnabled($store = null)
    {
        return (bool)$this->getConfig(
            $this->_configSectionId . '/' . self::MODULE_ENABLED_PATH,
            $store
        );
    }

    /**
     * Retrieve store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        $address = $this->storeManager->getStore()->getFormattedAddress();
        if (! preg_match('#[A-Za-z0-9]+#', strip_tags($address))) {
            $address = '';
        }

        return strip_tags($address);
    }

    /**
     * Get store identifiers
     *
     * @return  array
     */
    public function getStoreIds()
    {
        $stores = $this->storeManager->getStores();
        $ids = [];

        foreach ($stores as $store) {
            $ids[] = $store['store_id'];
        }

        return $ids;
    }

    /**
     * Check if module exists
     *
     * @return bool
     */
    public function moduleCheckoutspageEnabled()
    {
        return (bool)$this->moduleExists('Checkoutspage');
    }

    /**
     * @return void
     */
    public function disableExtension()
    {
        $connection = $this->resourceConnection->getConnection('core_write');
        $connection->delete(
            $this->resourceConnection->getTableName('core_config_data'),
            [$connection->quoteInto('path = ?', $this->_configSectionId . '/general/enabled')]
        );

        $this->config->setDataByPath($this->_configSectionId . '/general/enabled', 0);
        $this->config->save();
    }

    /**
     * Store form data
     *
     * @param mixed $data
     * @return void
     */
    public function setFormData($data = null)
    {
        if (null === $data) {
            $data = $this->_getRequest()->getParams();
        }

        $this->session->setData(self::FORM_DATA_PARAM, $data);
    }

    /**
     * Store form data with other data
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addFormData($key, $value)
    {
        $formData = $this->getFormData();
        $formData[$key] = $value;
        $this->setFormData($formData);
    }

    /**
     * Retrieve stored form data
     *
     * @param  null|string $key
     * @return mixed
     */
    public function getFormData($key = null)
    {
        if (null === $this->formData) {
            $this->formData = $this->session
                ->getData(self::FORM_DATA_PARAM, false);
        }

        if (null !== $key) {
            return isset($this->formData[$key]) ? $this->formData[$key] : null;
        }

        return $this->formData;
    }

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreName()
    {
        if (! $name = $this->storeManager->getStore()->getFrontendName()) {
            $name = __('Store Owner');
        }

        return $name;
    }

    /**
     * Check if current request is backend
     *
     * @return boolean
     */
    public function isBackend()
    {
        return $this->state->getAreaCode() === Area::AREA_ADMINHTML;
    }

    /**
     * Get color class of return status
     *
     * @param  string $status
     * @param  bool   $isAdminHtml
     * @return string
     */
    public function getStatusColor($status, $isAdminHtml = false)
    {
        $class = 'prrma-status ';
        switch (true) {
            case ReturnsStatus::STATUS_PROCESSED_CLOSED == $status:
            case ReturnsStatus::STATUS_APPROVED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-processed_closed';
                break;

            case ReturnsStatus::STATUS_REJECTED == $status:
            case ReturnsStatus::STATUS_REJECTED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-rejected';
                break;

            case ReturnsStatus::STATUS_CLOSED == $status:
                $class .= 'prrma-status-closed';
                break;

            case ReturnsStatus::STATUS_RECEIVED == $status && $isAdminHtml:
            case ReturnsStatus::STATUS_RECEIVED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-received';
                break;

            case ReturnsStatus::STATUS_AUTHORIZED == $status && $isAdminHtml:
            case ReturnsStatus::STATUS_AUTHORIZED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-authorized';
                break;

            default:
                $class .= 'prrma-status-' . $status;
        }

        return $class;
    }
}