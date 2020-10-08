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

namespace Plumrocket\RMA\Helper\Returns;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Main;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Item as ReturnsItem;
use Plumrocket\RMA\Model\Returns\ItemFactory;

class Item extends Main
{
    /**
     * @var int
     */
    const DEFAULT_VIRTUAL_DATA = 0;

    /**
     * Item Id
     */
    const ENTITY_ID     = 'entity_id';

    /**
     * Order Item Id
     */
    const ORDER_ITEM_ID     = 'order_item_id';

    /**
     * Return Reason
     */
    const REASON_ID         = 'reason_id';

    /**
     * Item Condition
     */
    const CONDITION_ID      = 'condition_id';

    /**
     * Resolution
     */
    const RESOLUTION_ID     = 'resolution_id';

    /**
     * Qty Purchased
     */
    const QTY_PURCHASED     = 'qty_purchased';

    /**
     * Qty Requested
     */
    const QTY_REQUESTED     = 'qty_requested';

    /**
     * Qty Authorized
     */
    const QTY_AUTHORIZED    = 'qty_authorized';

    /**
     * Qty Received
     */
    const QTY_RECEIVED      = 'qty_received';

    /**
     * Qty Approved
     */
    const QTY_APPROVED      = 'qty_approved';

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Returnrule
     */
    protected $returnruleHelper;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * Columns of item
     *
     * @var array
     */
    protected $cols = [
        self::ENTITY_ID     => 'Item Id',
        self::ORDER_ITEM_ID => 'Order Item Id',
        self::REASON_ID     => 'Return Reason',
        self::CONDITION_ID  => 'Item Condition',
        self::RESOLUTION_ID => 'Resolution',

        self::QTY_PURCHASED => 'Purchased Qty',
        self::QTY_REQUESTED => 'Return Qty',
        self::QTY_AUTHORIZED => 'Authorized Qty',
        self::QTY_RECEIVED  => 'Received Qty',
        self::QTY_APPROVED  => 'Approved Qty',
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Context                $context
     * @param ItemFactory            $itemFactory
     * @param Returnrule             $returnruleHelper
     * @param ImageFactory           $imageFactory
     * @param ProductHelper          $productHelper
     * @param ReturnsStatus          $status
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        ItemFactory $itemFactory,
        Returnrule $returnruleHelper,
        ImageFactory $imageFactory,
        ProductHelper $productHelper,
        ReturnsStatus $status
    ) {
        $this->itemFactory = $itemFactory;
        $this->returnruleHelper = $returnruleHelper;
        $this->imageFactory = $imageFactory;
        $this->productHelper = $productHelper;
        $this->status = $status;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
    }

    /**
     * Calculate status of one item
     *
     * @param  array|ReturnsItem $item
     * @return string|bool
     */
    public function getStatus(ReturnsItem $item, $prepareNumbers = true)
    {
        if ($prepareNumbers) {
            $this->prepareNumbers($item);
        }

        switch (true) {
            case $item->getQtyAuthorized() === 0:
            case $item->getQtyApproved() === 0 && $item->getQtyReceived() > 0:
                return ReturnsStatus::STATUS_REJECTED;

            case $item->getQtyReceived() === 0: // no break
            case $item->getQtyApproved() > 0 && $item->getQtyApproved() === $item->getQtyReceived():
                return ReturnsStatus::STATUS_PROCESSED_CLOSED;

            case $item->getQtyApproved() > 0 && $item->getQtyApproved() < $item->getQtyReceived():
                return ReturnsStatus::STATUS_APPROVED_PART;

            case $item->getQtyReceived() > 0 && $item->getQtyReceived() === $item->getQtyAuthorized():
                return ReturnsStatus::STATUS_RECEIVED;

            case $item->getQtyReceived() > 0 && $item->getQtyReceived() < $item->getQtyAuthorized():
                return ReturnsStatus::STATUS_RECEIVED_PART;

            case $item->getQtyAuthorized() > 0 && $item->getQtyAuthorized() === $item->getQtyRequested():
                return ReturnsStatus::STATUS_AUTHORIZED;

            case $item->getQtyAuthorized() > 0 && $item->getQtyAuthorized() < $item->getQtyRequested():
                return ReturnsStatus::STATUS_AUTHORIZED_PART;

            /*case - item cannot have this status -:
                return ReturnsStatus::STATUS_REJECTED_PART;*/

            case $item->getQtyAuthorized() === null:
                return ReturnsStatus::STATUS_NEW;
        }

        return false;
    }

    /**
     * Get item status label
     *
     * @param  ReturnsItem $item
     * @return string
     */
    public function getStatusLabel(ReturnsItem $item)
    {
        $status = $this->getStatus($item, false);
        $statusLabel = (string)$this->status->getByKey($status);

        switch (true) {
            case ReturnsStatus::STATUS_PROCESSED_CLOSED == $status && $item->getQtyReceived() === 0:
                $additional = __('Item was not received');
                break;

            case ReturnsStatus::STATUS_REJECTED == $status && $item->getQtyReceived() > 0:
                $additional = __('Item was not approved');
                break;

            case ReturnsStatus::STATUS_APPROVED_PART == $status && $item->getQtyApproved() > 0: // no break
            case ReturnsStatus::STATUS_PROCESSED_CLOSED == $status && $item->getQtyApproved() > 0:
                $additional = __('%1 processed', $item->getResolutionLabel());
                break;

            default:
                $additional = '';
        }

        if (ReturnsStatus::STATUS_CLOSED == $item->getReturns()->getStatus()
            && ! in_array($status, [
                ReturnsStatus::STATUS_REJECTED,
                ReturnsStatus::STATUS_PROCESSED_CLOSED,
                ReturnsStatus::STATUS_APPROVED_PART,
            ])
        ) {
            $statusLabel = $this->status->getByKey(ReturnsStatus::STATUS_PROCESSED_CLOSED);
            $additional = '';
        }

        return $statusLabel . ($additional ? ' (' . $additional . ')' : '');
    }

    /**
     * Prepare numeric values
     *
     * @param  ReturnsItem $item
     * @return void
     */
    private function prepareNumbers(ReturnsItem $item)
    {
        $cols = [
            self::QTY_REQUESTED,
            self::QTY_AUTHORIZED,
            self::QTY_RECEIVED,
            self::QTY_APPROVED,
        ];

        $declined = false;
        foreach ($cols as $col) {
            if (0 === $item->getData($col)) {
                $declined = true;
            }

            if ($declined) {
                // If any left column equils zero
                $item->setData($col, 0);
            } elseif (null === $item->getData($col)) {
                if ($item->getReturns()->isClosed()) {
                    // If return is closed then convert null values to 0
                    $item->setData($col, 0);
                }
            } else {
                $item->setData($col, (int)$item->getData($col));
            }
        }
    }

    /**
     * Retrieve returned qty
     *
     * @param OrderItem $item
     * @param array|int|null $excludeReturns
     * @return int
     */
    public function getQtyReturned(OrderItem $item, $excludeReturns = null)
    {
        $qty = $item->getQtyReturned() ?: 0;

        $collection = $this->itemFactory->create()
            ->getCollection()
            ->addFieldToFilter(self::ORDER_ITEM_ID, $item->getId())
            ->addExpressionFieldToSelect(
                'qty_requested_total',
                'SUM({{qty_requested}})',
                self::QTY_REQUESTED
            );

        if ($excludeReturns) {
            $collection->addFieldToFilter('parent_id', ['nin' => $excludeReturns]);
        }

        $qty += (int)$collection->getFirstItem()->getQtyRequestedTotal();

        return max(0, $qty);
    }

    /**
     * Retrieve qty that allowed to return
     *
     * @param OrderItem $item
     * @param array|int|null $excludeReturns
     * @return int
     */
    public function getQtyToReturn(OrderItem $item, $excludeReturns = null)
    {
        $qty = (int) $item->getQtyOrdered();
        // $qty = (int) $item->getQtyShipped();
        $qty -= $this->getQtyReturned($item, $excludeReturns);

        return max(0, $qty);
    }

    /**
     * Check if item can be returned by customer
     *
     * @param OrderItem $item
     * @return bool
     */
    public function canReturnCustomer(OrderItem $item)
    {
        if ($this->isExpired($item)) {
            return false;
        }

        if ($this->getQtyToReturn($item) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Check if item can be returned by admin
     *
     * @param OrderItem $item
     * @return bool
     */
    public function canReturnAdmin(OrderItem $item)
    {
        if ($this->getQtyToReturn($item) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Get is virtual
     *
     * @param OrderItem $item
     * @return bool
     */
    public function isVirtual(OrderItem $item)
    {
        return $item->getIsVirtual()
            || $item->getProduct() ? $item->getProduct()->getIsVirtual() : self::DEFAULT_VIRTUAL_DATA
            || $item->getProductType() == Downloadable::TYPE_DOWNLOADABLE;
    }

    /**
     * Get is expired
     *
     * @param OrderItem $item
     * @return bool
     */
    public function isExpired(OrderItem $item)
    {
        return ! $this->returnruleHelper
            ->getSelectOptions($item->getProduct(), [
                'exclude_expired' => true,
                'date' => $item->getCreatedAt(),
            ]);
    }

    /**
     * Get columns
     *
     * @param  null|string $key
     * @return array|string
     */
    public function getCols($key = null)
    {
        if (null !== $key) {
            return isset($this->cols[$key]) ? __($this->cols[$key]) : '';
        }

        return $this->cols;
    }

    /**
     * Get order item image url
     *
     * @param  OrderItem $orderItem
     * @param  int       $width
     * @param  int       $height
     * @param  string    $imageType
     * @return string
     */
    public function getImageUrl(OrderItem $orderItem, $width, $height, $imageType = 'product_page_image_small')
    {
        if (! $product = $orderItem->getProduct()) {
            return '';
        }

        $url = (string)$this->imageFactory->create()
            ->init($product, $imageType)
            ->keepTransparency(true)
            ->resize($width, $height)
            ->getUrl();

        if (! $url) {
            $url = $this->productHelper->getSmallImageUrl($product);
        }

        return $url;
    }
}
