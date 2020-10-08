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

namespace Plumrocket\RMA\Model\Returns;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Plumrocket\RMA\Model\ReturnsFactory;
use Plumrocket\RMA\Model\Returns;

class Address extends \Magento\Sales\Model\Order\Address
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'prrma_returns_address';

    /**
     * @var string
     */
    protected $_eventObject = 'address';

    /**
     * @var \Plumrocket\RMA\Model\ReturnsFactory
     */
    protected $returnsFactory;

    /**
     * @var \Plumrocket\RMA\Model\Returns|null
     */
    protected $returns = null;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                 $customAttributeFactory
     * @param \Magento\Sales\Model\OrderFactory                            $orderFactory
     * @param \Magento\Directory\Model\RegionFactory                       $regionFactory
     * @param \Plumrocket\RMA\Model\ReturnsFactory                         $returnsFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Plumrocket\RMA\Model\ReturnsFactory $returnsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->returnsFactory = $returnsFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $orderFactory,
            $regionFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns\Address');
    }

    /**
     * Set returns
     *
     * @param Returns $returns
     * @return $this
     */
    public function setReturns(Returns $returns)
    {
        $this->returns = $returns;
        return $this;
    }

    /**
     * Get returns entity
     *
     * @return \Plumrocket\RMA\Model\Returns
     */
    public function getReturns()
    {
        if (!$this->returns) {
            $this->returns = $this->returnsFactory->create()->load($this->getParentId());
        }
        return $this->returns;
    }

    /**
     * Find an unassigned address
     *
     * @param  Order|int $orderId
     * @return $this|null
     */
    public function getUnassigned($orderId)
    {
        if (is_object($orderId)) {
            $orderId = $orderId->getId();
        }

        if (! $orderId || ! is_numeric($orderId)) {
            return null;
        }

        $address = $this->getCollection()
            ->addFieldToFilter('parent_id', ['null' => true])
            ->addFieldToFilter('order_id', $orderId)
            ->setPageSize(1)
            ->getFirstItem();

        if ($address->getId()) {
            return $address;
        }

        return null;
    }

    /**
     * Sets the ID for the returns address.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(OrderAddressInterface::ENTITY_ID, $entityId);
    }
}
