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

use Magento\Sales\Model\Order\Item as OrderItem;
use Plumrocket\RMA\Model\Returns;

class Item extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var Returns
     */
    protected $returns = null;

    /**
     * @var OrderItem
     */
    protected $orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Plumrocket\RMA\Model\ReasonFactory
     */
    protected $reasonFactory;

    /**
     * @var \Plumrocket\RMA\Model\ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var \Plumrocket\RMA\Model\ResolutionFactory
     */
    protected $resolutionFactory;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Sales\Model\Order\ItemFactory                       $orderItemFactory
     * @param \Plumrocket\RMA\Model\ReasonFactory                          $reasonFactory
     * @param \Plumrocket\RMA\Model\ConditionFactory                       $conditionFactory
     * @param \Plumrocket\RMA\Model\ResolutionFactory                      $resolutionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Plumrocket\RMA\Model\ReasonFactory $reasonFactory,
        \Plumrocket\RMA\Model\ConditionFactory $conditionFactory,
        \Plumrocket\RMA\Model\ResolutionFactory $resolutionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderItemFactory = $orderItemFactory;
        $this->reasonFactory = $reasonFactory;
        $this->conditionFactory = $conditionFactory;
        $this->resolutionFactory = $resolutionFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns\Item');
    }

    /**
     * Get return entity
     *
     * @return Returns
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * Set return entity
     *
     * @param Returns $returns
     * @return $this
     */
    public function setReturns(Returns $returns)
    {
        $this->returns = $returns;
        $this->setReturnsId($returns->getId());
        return $this;
    }

    /**
     * Get order item
     *
     * @return OrderItem
     */
    public function getOrderItem()
    {
        if (null === $this->orderItem && ($id = $this->getOrderItemId())) {
            $this->orderItem = $this->orderItemFactory->create()->load($id);
        }

        return $this->orderItem;
    }

    /**
     * Set order item
     *
     * @param OrderItem $item
     * @return $this
     */
    public function setOrderItem(OrderItem $item)
    {
        $this->orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Get parent order item
     *
     * @return OrderItem
     */
    public function getParentOrderItem()
    {
        $orderItem = $this->getOrderItem();
        $parentOrderItem = $orderItem->getParentItem();

        if ($orderItem->getParentItemId()
            && (! $parentOrderItem || ! $parentOrderItem->getId())
        ) {
            $parentOrderItem = $this->orderItemFactory->create()
                ->load($orderItem->getParentItemId());
            $this->setParentOrderItem($parentOrderItem);
        }

        return $parentOrderItem;
    }

    /**
     * Set parent order item
     *
     * @param OrderItem $item
     * @return $this
     */
    public function setParentOrderItem(OrderItem $item)
    {
        $this->getOrderItem()->setParentItem($item);
        return $this;
    }

    /**
     * Retrieve reason label
     *
     * @return \Plumrocket\RMA\Model\Reason $reason
     */
    public function getReason()
    {
        if (null === $this->getData('reason')
            && $id = $this->getReasonId()
        ) {
            $this->setData('reason', $this->reasonFactory->create()->load($id));
        }

        return $this->getData('reason');
    }

    /**
     * Retrieve reason label
     *
     * @return string
     */
    public function getReasonLabel()
    {
        return $this->getReason() ? $this->getReason()->getLabel() : '';
    }

    /**
     * Retrieve condition
     *
     * @return \Plumrocket\RMA\Model\Condition $condition
     */
    public function getCondition()
    {
        if (null === $this->getData('condition')
            && $id = $this->getConditionId()
        ) {
            $this->setData('condition', $this->conditionFactory->create()->load($id));
        }

        return $this->getData('condition');
    }

    /**
     * Retrieve condition label
     *
     * @return string
     */
    public function getConditionLabel()
    {
        return $this->getCondition() ? $this->getCondition()->getLabel() : '';
    }

    /**
     * Retrieve resolution
     *
     * @return \Plumrocket\RMA\Model\Resolution $resolution
     */
    public function getResolution()
    {
        if (null === $this->getData('resolution')
            && $id = $this->getResolutionId()
        ) {
            $this->setData('resolution', $this->resolutionFactory->create()->load($id));
        }

        return $this->getData('resolution');
    }

    /**
     * Retrieve resolution label
     *
     * @return string
     */
    public function getResolutionLabel()
    {
        return $this->getResolution() ? $this->getResolution()->getLabel() : '';
    }
}
