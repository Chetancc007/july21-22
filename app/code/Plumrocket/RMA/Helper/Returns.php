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

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Plumrocket\RMA\Helper\Config;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Main;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus as Status;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory;
use Plumrocket\RMA\Model\ResourceModel\Returns\Item\CollectionFactory as ItemCollectionFactory;
use Plumrocket\RMA\Model\Returns as ReturnsModel;
use Plumrocket\RMA\Model\Returns\ItemFactory;

class Returns extends Main
{
    /**
     * Length of code
     */
    const CODE_LENGTH = 50;

    /**
     * Salt of code for guest login
     */
    const CODE_SALT_GUEST = 'guest';

    /**
     * Salt of code for print page
     */
    const CODE_SALT_PRINT = 'print';

    /**
     * Salt of code for file action
     */
    const CODE_SALT_FILE = 'file';

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Context                $context
     * @param Data                   $dataHelper
     * @param Config                 $configHelper
     * @param ItemHelper             $itemHelper
     * @param ItemFactory            $itemFactory
     * @param Random                 $mathRandom
     * @param CollectionFactory      $collectionFactory
     * @param ItemCollectionFactory  $itemCollectionFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        Data $dataHelper,
        Config $configHelper,
        ItemHelper $itemHelper,
        ItemFactory $itemFactory,
        Random $mathRandom,
        CollectionFactory $collectionFactory,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->itemHelper = $itemHelper;
        $this->itemFactory = $itemFactory;
        $this->mathRandom = $mathRandom;
        $this->collectionFactory = $collectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
    }

    /**
     * Check if order can be returned by customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canReturnCustomer(Order $order)
    {
        // Stop if extension is disabled
        if (! $this->dataHelper->moduleEnabled()) {
            return false;
        }

        // Stop if disabled on backend
        if (! $this->configHelper->allowCreateOnFrontend()) {
            return false;
        }

        // Stop if not complete
        if ($order->getState() !== Order::STATE_COMPLETE) {
            return false;
        }

        // Stop if all items were returned or can't be returned
        $hasActiveItems = false;
        foreach ($this->getOrderItems($order) as $item) {
            if ($this->itemHelper->canReturnCustomer($item)) {
                $hasActiveItems = true;
                break;
            }
        }

        return $hasActiveItems;
    }

    /**
     * Check if order can be returned by customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canReturnAdmin(Order $order)
    {
        // Stop if extension is disabled
        if (! $this->dataHelper->moduleEnabled()) {
            return false;
        }

        // Stop if canceled
        if ($order->isCanceled()) {
            return false;
        }

        // Stop if all items were returned or can't be returned
        $hasActiveItems = false;
        foreach ($this->getOrderItems($order) as $item) {
            if ($this->itemHelper->canReturnAdmin($item)) {
                $hasActiveItems = true;
                break;
            }
        }

        return $hasActiveItems;
    }

    /**
     * Check if least one item is authorized and no one is new
     *
     * @param  ReturnsModel $returns
     * @return boolean
     */
    public function hasAuthorized(ReturnsModel $returns)
    {
        $items = $this->getItems($returns);

        $hasNew = false;
        $hasAuthorized = false;
        foreach ($items as $item) {
            if (null === $item->getQtyAuthorized()) {
                $hasNew = true;
            } elseif ($item->getQtyAuthorized()) {
                $hasAuthorized = true;
            }
        }

        return (! $hasNew) && $hasAuthorized;
    }

    /**
     * Calculate status of return
     *
     * @param  ReturnsModel $returns
     * @return string|bool
     */
    public function getStatus(ReturnsModel $returns)
    {
        $statuses = [];
        foreach ($returns->getItems() as $item) {
            if ($status = $this->itemHelper->getStatus($item)) {
                $statuses[$status] = $status;
            }
        }

        $_ = & $statuses;
        switch (true) {

            /**
             * All items are declined
             */
            case isset($_[Status::STATUS_REJECTED]) && count($statuses) == 1:
                return Status::STATUS_REJECTED;

            /**
             * At least one item is approved, all items are finished
             */
            // If $statuses contains only STATUS_REJECTED, STATUS_PROCESSED_CLOSED, STATUS_APPROVED_PART - Return has STATUS_PROCESSED_CLOSED
            case ! array_diff($statuses, [Status::STATUS_REJECTED, Status::STATUS_PROCESSED_CLOSED, Status::STATUS_APPROVED_PART]):
                return Status::STATUS_PROCESSED_CLOSED;

            /**
             * At least one item is approved
             */
            case isset($_[Status::STATUS_APPROVED_PART]): // no break
            case isset($_[Status::STATUS_PROCESSED_CLOSED]) && count($statuses) > 1:
                return Status::STATUS_APPROVED_PART;

            /**
             * All items are received
             */
            case isset($_[Status::STATUS_RECEIVED]) && count($statuses) == 1:
                return Status::STATUS_RECEIVED;

            /**
             * At least one item is received
             */
            case isset($_[Status::STATUS_RECEIVED_PART]): // no break
            case isset($_[Status::STATUS_RECEIVED]) && count($statuses) > 1:
                return Status::STATUS_RECEIVED_PART;

            /**
             * All items are authorized
             */
            case isset($_[Status::STATUS_AUTHORIZED]) && count($statuses) == 1:
                return Status::STATUS_AUTHORIZED;

            /**
             * At least one item is authorized
             */
            case isset($_[Status::STATUS_AUTHORIZED_PART]): // no break
            case isset($_[Status::STATUS_AUTHORIZED]) && count($statuses) > 1:
                return Status::STATUS_AUTHORIZED_PART;

            /**
             * At least one item is rejected and no one is authorized
             */
            // case isset($_[Status::STATUS_REJECTED]) && isset($_[Status::STATUS_NEW]) && count($statuses) == 2: // no break
            case isset($_[Status::STATUS_REJECTED]) && count($statuses) > 1:
                return Status::STATUS_REJECTED_PART;

            /**
             * After create by customer, no one is authorized
             */
            case isset($_[Status::STATUS_NEW]) && count($statuses) == 1:
                return Status::STATUS_NEW;
        }

        return false;
    }

    /**
     * Retrieve order items for return
     *
     * @param  Order  $order
     * @return \Magento\Sales\Model\Order\Item[]
     */
    public function getOrderItems(Order $order)
    {
        // $items = $order->getItemsCollection();
        // $items = $order->getAllItems(); returns items without keys
        $items = $order->getItems();
        foreach ($items as $key => $item) {
            // Hide configurable product item. Need to show only configurable child items.
            if (Configurable::TYPE_CODE === $item->getProductType()) {
                unset($items[$key]);
            }

            // Hide bundle product item. Need to show only bundle child items.
            if (Bundle::TYPE_CODE === $item->getProductType()) {
                unset($items[$key]);
            }
        }

        return $items;
    }

    /**
     * Get items for return
     *
     * @param  ReturnsModel $returns
     * @return \Plumrocket\RMA\Model|Returns\Item[]
     */
    public function getItems(ReturnsModel $returns)
    {
        if (! $items = $returns->getItems()) {
            $orderItems = $this->getOrderItems(
                $returns->getOrder()
            );

            $items = [];
            $n = 1;
            foreach ($orderItems as $orderItem) {
                $item = $this->itemFactory->create()
                    ->setReturns($returns)
                    ->setOrderItem($orderItem)
                    // Row number is used in the items table as unique index for new items
                    ->setRowNumber($n++);

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Check if return is virtual
     *
     * @param  ReturnsModel $returns
     * @return boolean
     */
    public function isVirtual(ReturnsModel $returns)
    {
        // Check by order.
        $order = $returns->getOrder();
        if ($order && $order->getIsVirtual()) {
            return true;
        }

        // Check by items.
        $isVirtual = true;
        $items = $this->getItems($returns);
        foreach ($items as $item) {
            if (! $this->itemHelper->isVirtual($item->getOrderItem())
                && $this->itemHelper->getQtyToReturn($item->getOrderItem(), $returns->getId())
            ) {
                $isVirtual = false;
                break;
            }
        }

        return $isVirtual;
    }

    /**
     * Retrieve all returns by order
     *
     * @param  Order|int $orderId
     * @return ReturnsModel[]
     * @note Is used on order view tab
     */
    public function getAllByOrder($orderId)
    {
        if (is_object($orderId)) {
            $orderId = $orderId->getId();
        }

        $returns = $this->collectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->addOrder('entity_id');

        return $returns;
    }

    /**
     * Retrieve all returns by order item
     *
     * @param  int $orderItemId
     * @return ReturnsModel[]
     */
    public function getAllByOrderItem($orderItemId)
    {
        return $this->itemCollectionFactory->create()
            ->addReturnsData()
            ->addFieldToFilter(ItemHelper::ORDER_ITEM_ID, $orderItemId)
            ->addOrder('parent_id')
            ->getItems();
    }

    /**
     * Generate random code for quick login
     *
     * @return string
     */
    public function generateCode()
    {
        return $this->mathRandom->getRandomString(self::CODE_LENGTH);
    }

    /**
     * Generate random code for quick login
     *
     * @param  ReturnsModel|string $code
     * @param  string $salt
     * @return string
     */
    public function getCode($code, $salt)
    {
        if (is_object($code)) {
            $code = $code->getCode();
        }
        return sha1($salt . $code . $salt);
    }

    /**
     * Retrieve create action url
     *
     * @param  Order|int|null $orderId
     * @return string
     */
    public function getCreateUrl($orderId)
    {
        if (null === $orderId) {
            return $this->_getUrl(Data::SECTION_ID . '/returns/new');
        } elseif (is_object($orderId)) {
            $orderId = $orderId->getId();
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/create', [
            'order_id' => $orderId
        ]);
    }

    /**
     * Retrieve view action url
     *
     * @param  ReturnsModel|int $id
     * @return string
     */
    public function getViewUrl($id)
    {
        if (is_object($id)) {
            $id = $id->getId();
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/view', [
            'id' => $id
        ]);
    }

    /**
     * Retrieve url to quick login action
     *
     * @param  ReturnsModel
     * @return string
     */
    public function getQuickViewUrl(ReturnsModel $returns)
    {
        if (! $returns->getCode()) {
            return $this->getViewUrl($returns);
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/view_quicklogin', [
            'id'    => $returns->getId(),
            'code'  => $this->getCode($returns, self::CODE_SALT_GUEST),
            '_nosid'=> true
        ]);
    }

    /**
     * Retrieve cancel action url
     *
     * @param  ReturnsModel|int $id
     * @return string
     */
    public function getCancelUrl($id)
    {
        if (is_object($id)) {
            $id = $id->getId();
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/cancel', [
            'id' => $id
        ]);
    }

    /**
     * Retrieve print action url
     *
     * @param  ReturnsModel|int $id
     * @param  bool $withCode Secret code for admin print page
     * @return string
     */
    public function getPrintUrl($returns, $withCode = false)
    {
        if (is_object($returns)) {
            $id = $returns->getId();
            $code = $returns->getCode();
        } else {
            $id = $returns;
        }

        if ($withCode) {
            if (empty($code)) {
                throw new \Exception('Return code is empty');
            }

            $code = $this->getCode($code, self::CODE_SALT_PRINT);
        } else {
            $code = null;
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/print', [
            'id' => $id,
            'code' => $code,
            '_nosid' => true,
        ]);
    }

    /**
     * Retrieve file action url
     *
     * @param  ReturnsModel|int $id
     * @param  string $filename
     * @param  bool $withCode Secret code for admin file action
     * @return string
     */
    public function getFileUrl($returns, $filename, $withCode = false)
    {
        if (is_object($returns)) {
            $id = $returns->getId();
            $code = $returns->getCode();
        } else {
            $id = $returns;
        }

        if ($withCode) {
            if (empty($code)) {
                throw new \Exception('Return code is empty');
            }

            $code = $this->getCode($code, self::CODE_SALT_FILE);
        } else {
            $code = null;
        }

        return $this->_getUrl(Data::SECTION_ID . '/returns/file', [
            'id' => $id,
            'code' => $code,
            'storage' => urlencode($filename),
            '_nosid' => true,
        ]);
    }

    /**
     * Retrieve history action url
     *
     * @return string
     */
    public function getHistoryUrl()
    {
        return $this->_getUrl(Data::SECTION_ID . '/returns');
    }
}
