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

namespace Plumrocket\RMA\Model;

class Returnrule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Conbine factory
     * @var \Plumrocket\RMA\Model\Returnrule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * Action collection factory
     * @var \Magento\Rule\Model\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * Json Helper
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serialize;

    /**
     * Constructor
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Plumrocket\RMA\Model\Returnrule\Condition\CombineFactory $combineFactory
     * @param \Magento\Rule\Model\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serialize
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Plumrocket\RMA\Model\Returnrule\Condition\CombineFactory $combineFactory,
        \Magento\Rule\Model\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->serialize = $serialize;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\Plumrocket\RMA\Model\ResourceModel\Returnrule');
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Retrieve label
     * @return string
     */
    public function getLabel()
    {
        if ($this->getData('label')) {
            return $this->getData('label');
        }

        return $this->getTitle();
    }

    /**
     * Retrieve name
     * @return string
     */
    public function getName()
    {
        if (null !== $this->getData('name')) {
            return $this->getData('name');
        }

        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        // parent::afterLoad();
        $websiteId = $this->getWebsiteId();

        if ($websiteId && is_string($websiteId)) {
            $this->setWebsiteid(explode(',', $websiteId));
        }

        $customerGroupId = $this->getCustomerGroupId();
        if ($customerGroupId && is_string($customerGroupId)) {
            $this->setCustomerGroupId(explode(',', $customerGroupId));
        }

        $resolution = $this->getResolution();
        if ($resolution && is_string($resolution)) {
            $resolution = $this->jsonHelper->jsonDecode($resolution);
            $_res = [];
            foreach ($resolution as $id => $item) {
                $_res[$id] = $item;
            }
            $this->setResolution($_res);
        }

        return $this;
    }

    /**
     * Retrieve Serialized Conditions
     * Deprecated and Need to be removed in future releases
     * added for compatibility with 2.1.x and 2.2.x
     * @return string
     */
    public function getConditionsSerialized()
    {
        $value = $this->getData('conditions_serialized');

        if (isset($this->serializer)) {
            try {
                $uv = $this->serialize->unserialize($value);
                $value = $this->serializer->serialize($uv);
            } catch (\Exception $e) {}
        }

        return $value;
    }

    /**
     * Retrieve Serialized Actions
     * Deprecated and Need to be removed in future releases
     * added for compatibility with 2.1.x and 2.2.x
     * @return string
     */
    public function getActionsSerialized()
    {
        $value = $this->getData('actions_serialized');

        if (isset($this->serializer)) {
            try {
                $uv = $this->serialize->unserialize($value);
                $value = $this->serializer->serialize($uv);
            } catch (\Exception $e) {}
        }

        return $value;
    }
}
