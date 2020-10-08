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

use Magento\Framework\DataObject;
use Plumrocket\RMA\Block\Adminhtml\Returns\ItemsFactory as ItemsAdminBlockFactory;
use Plumrocket\RMA\Block\Returns\ItemsFactory as ItemsBlockFactory;
use Plumrocket\RMA\Helper\Config as ConfigHelper;
use Plumrocket\RMA\Helper\Returnrule as ReturnruleHelper;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Returns;

class Validator extends DataObject
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ReturnsHelper
     */
    protected $returnsHelper;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var ItemsBlockFactory
     */
    protected $itemsBlockFactory;

    /**
     * @var ItemsAdminBlockFactory
     */
    protected $itemsAdminBlockFactory;

    /**
     * @var Returns
     */
    protected $returns = null;

    /**
     * Validation error messages
     *
     * @var array
     */
    private $messages = [];

    /**
     * @param ConfigHelper           $configHelper
     * @param ReturnsHelper          $returnsHelper
     * @param ItemHelper             $itemHelper
     * @param ItemsBlockFactory      $itemsBlockFactory
     * @param ItemsAdminBlockFactory $itemsAdminBlockFactory
     * @param array                  $data
     */
    public function __construct(
        ConfigHelper $configHelper,
        ReturnsHelper $returnsHelper,
        ItemHelper $itemHelper,
        ItemsBlockFactory $itemsBlockFactory,
        ItemsAdminBlockFactory $itemsAdminBlockFactory,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->returnsHelper = $returnsHelper;
        $this->itemHelper = $itemHelper;
        $this->itemsBlockFactory = $itemsBlockFactory;
        $this->itemsAdminBlockFactory = $itemsAdminBlockFactory;
        parent::__construct($data);
    }

    /**
     * Set returns entity
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
     * @return Returns
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * Check if is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return empty($this->messages);
    }

    /**
     * Validate comment
     *
     * @param  string $text
     * @param  array|null $files
     * @param  bool $textRequired
     * @return $this
     */
    public function validateMessage($text, $files, $textRequired = true)
    {
        if ($textRequired
            && (! is_string($text) || ! \Zend_Validate::is(trim($text), 'NotEmpty'))
        ) {
            return $this->error(__('Comment field cannot be empty'));
        }

        if (! \Zend_Validate::is($text, 'StringLength', [0, 10000])) {
            return $this->error(__('Comment is too long'));
        }

        /*if (! empty($files) && empty($text)) {
            return $this->error(__('Comment field  cannot be empty, if you have attached files'));
        }*/

        return $this;
    }

    /**
     * Validate items for customer
     * - item exists
     * - can return item
     * - correct qty
     * - correct reason
     * - correct condition
     * - correct and doesn't expired resolution
     *
     * @param  array $value
     * @return $this
     */
    public function validateItemsCustomer($value)
    {
        $orderItems = $this->returnsHelper->getOrderItems(
            $this->getReturns()->getOrder()
        );
        $block = $this->itemsBlockFactory->create();

        $validItems = [];
        $qty = [];
        $hasActive = false;
        if (is_array($value)) {
            foreach ($value as $data) {
                $data = new DataObject($data);

                // Ignore row with wrong order item id.
                if (! isset($orderItems[$data->getOrderItemId()])) {
                    continue;
                }
                $orderItem = $orderItems[$data->getOrderItemId()];

                // Ignore non-requested new item.
                if ($data->getQtyRequested() < 1 || empty($data['active'])) {
                    continue;
                }

                // Check if item can be returned.
                if (! $this->itemHelper->canReturnAdmin($orderItem)) {
                    $this->error(__('"%1" cannot be returned', $orderItem->getName()));
                    continue;
                }

                $hasActive = true;

                // Validate lists.
                $cols = [
                    ItemHelper::QTY_REQUESTED,
                    ItemHelper::REASON_ID,
                    ItemHelper::CONDITION_ID,
                    ItemHelper::RESOLUTION_ID,
                ];
                foreach ($cols as $col) {
                    if (! $this->isValidColumnValue($col, $data->getData($col), $orderItem, $block)) {
                        continue(2);
                    }
                }

                $validItems[] = [
                    ItemHelper::ORDER_ITEM_ID => $data->getData(ItemHelper::ORDER_ITEM_ID),
                    ItemHelper::REASON_ID => $data->getData(ItemHelper::REASON_ID),
                    ItemHelper::CONDITION_ID => $data->getData(ItemHelper::CONDITION_ID),
                    ItemHelper::RESOLUTION_ID => $data->getData(ItemHelper::RESOLUTION_ID),
                    ItemHelper::QTY_REQUESTED => $data->getData(ItemHelper::QTY_REQUESTED),
                ];
            }
        }

        if (! $hasActive) {
            $this->error(__('You need to choose at least one item from order'));
        }

        // Set valid items.
        $this->setValidItems($validItems);

        return $this;
    }

    /**
     * Validate items for admin
     * - order item exists
     * - item exists
     * - can return order item
     * - correct qty's
     * - correct reason
     * - correct condition
     * - correct resolution
     *
     * @param  array $value
     * @return $this
     */
    public function validateItemsAdmin($value)
    {
        $orderItems = $this->returnsHelper->getOrderItems(
            $this->getReturns()->getOrder()
        );
        $items = $this->getReturns()->getItemsCollection();
        $block = $this->itemsAdminBlockFactory->create();

        $validItems = [];
        $qty = [];
        $hasActive = false;
        if (is_array($value)) {
            foreach ($value as $data) {
                $data = new DataObject($data);

                // Ignore row with wrong order item id.
                if (! isset($orderItems[$data->getOrderItemId()])) {
                    continue;
                }
                $orderItem = $orderItems[$data->getOrderItemId()];

                // Item Id.
                if ('' === $data->getEntityId()) {
                    // If items are exists, check if they contain an order id.
                    if ($items->count()
                        && ! $items->getItemByColumnValue(
                            ItemHelper::ORDER_ITEM_ID,
                            $data->getOrderItemId()
                        )
                    ) {
                        continue;
                    }

                    // Ignore non-requested new item.
                    if ($data->getQtyRequested() < 1) {
                        continue;
                    }

                    // Check if item can be returned.
                    if (! $this->itemHelper->canReturnAdmin($orderItem)) {
                        $this->error(__('"%1" cannot be returned', $orderItem->getName()));
                        continue;
                    }
                } else {
                    // Ignore row with wrong item id.
                    if (! $item = $items->getItemById($data->getEntityId())) {
                        continue;
                    }

                    // Check min requested value.
                    if ($data->getQtyRequested() < 1) {
                        $this->error(__('"%1" has incorrect return qty', $orderItem->getName()));
                        continue;
                    }
                }

                $hasActive = true;

                // Validate lists.
                $cols = [
                    ItemHelper::REASON_ID,
                    ItemHelper::CONDITION_ID,
                    ItemHelper::RESOLUTION_ID,
                ];
                foreach ($cols as $col) {
                    if (! $this->isValidColumnValue($col, $data->getData($col), $orderItem, $block)) {
                        continue(2);
                    }
                }

                // Check requested qty.
                if (! isset($qty[$orderItem->getId()])) {
                    $qty[$orderItem->getId()] = 0;
                }
                $qty[$orderItem->getId()] += $data->getQtyRequested();

                if (empty($item) || ! $qtyPurchased = $item->getQtyPurchased()) {
                    $qtyPurchased = $this->itemHelper
                        ->getQtyToReturn($orderItem, $this->getReturns()->getId());
                }

                if ($qtyPurchased < $qty[$orderItem->getId()]) {
                    $this->error(__('"%1" has incorrect return qty', $orderItem->getName()));
                    continue;
                }

                // Check format and max qty of numeric fields.
                $cols = [
                    ItemHelper::QTY_REQUESTED,
                    ItemHelper::QTY_AUTHORIZED,
                    ItemHelper::QTY_RECEIVED,
                    ItemHelper::QTY_APPROVED,
                ];

                foreach ($cols as $col) {
                    if ($data->getData($col)
                        && (! is_numeric($data->getData($col))
                            || $data->getData($col) < 1)
                    ) {
                        $this->error(__('"%1" has incorrect numeric values', $orderItem->getName()));
                        continue (2);
                    }

                    $nextCol = next($cols);
                    if ($nextCol && $data->getData($nextCol) > $data->getData($col)) {
                        $this->error(__('"%1" has incorrect numeric values', $orderItem->getName()));
                        continue (2);
                    }
                }

                $validItems[] = [
                    ItemHelper::ENTITY_ID => $data->getData(ItemHelper::ENTITY_ID),
                    ItemHelper::ORDER_ITEM_ID => $data->getData(ItemHelper::ORDER_ITEM_ID),
                    ItemHelper::REASON_ID => $data->getData(ItemHelper::REASON_ID),
                    ItemHelper::CONDITION_ID => $data->getData(ItemHelper::CONDITION_ID),
                    ItemHelper::RESOLUTION_ID => $data->getData(ItemHelper::RESOLUTION_ID),
                    ItemHelper::QTY_REQUESTED => $data->getData(ItemHelper::QTY_REQUESTED),
                    ItemHelper::QTY_AUTHORIZED => $data->getData(ItemHelper::QTY_AUTHORIZED),
                    ItemHelper::QTY_RECEIVED => $data->getData(ItemHelper::QTY_RECEIVED),
                    ItemHelper::QTY_APPROVED => $data->getData(ItemHelper::QTY_APPROVED),
                ];
            }
        }

        if (! $hasActive) {
            $this->error(__('You need to choose at least one item from order'));
        }

        // Set valid items.
        $this->setValidItems($validItems);

        return $this;
    }

    /**
     * Validate column value
     *
     * @param  string $colName
     * @param  mixed $value
     * @param  \Magento\Sales\Model\Order\Item $orderItem
     * @param  ItemsBlock $block
     * @return bool
     */
    private function isValidColumnValue($colName, $value, $orderItem, $block)
    {
        $haystack = [];
        switch ($colName) {
            case ItemHelper::QTY_REQUESTED:
                // $haystack = range(1, $this->itemHelper->getQtyToReturn());
                $haystack = $block->getQtyOptions(
                    $this->itemHelper->getQtyToReturn($orderItem)
                );
                $haystack = array_keys($haystack);
                break;

            case ItemHelper::REASON_ID:
                $haystack = $block->getReasonOptions();
                unset($haystack['']);
                $haystack = array_keys($haystack);
                break;

            case ItemHelper::CONDITION_ID:
                $haystack = $block->getConditionOptions();
                $haystack = array_keys($haystack);
                break;

            case ItemHelper::RESOLUTION_ID:
                /*$resolution = $this->returnruleHelper
                    ->getSelectOptions($orderItem->getProduct());*/
                $haystack = $block->getResolutionOptions($orderItem, true);
                $haystack = array_keys($haystack);
                break;
        }

        if ($haystack) {
            if (! \Zend_Validate::is($value, 'InArray', [$haystack])) {
                $this->error(__(
                    '"%1" has incorrect %2.',
                    $orderItem->getName(),
                    $this->itemHelper->getCols($colName)
                ));

                return false;
            }
        }

        return true;
    }

    /**
     * Validate agree checkbox
     *
     * @param bool $value
     * @return $this
     */
    public function validateAgree($value)
    {
        if (empty($value)) {
            $this->error(__('You need to agree to return policy'));
        }

        return $this;
    }

    /**
     * Validate tracking carrier and number
     *
     * @param string $carrier
     * @param string $number
     * @return $this
     */
    public function validateTrack($carrier, $number)
    {
        // Validate carrier code
        $carriers = $this->configHelper->getShippingCarriers();
        if (! is_string($carrier)
            || ! \Zend_Validate::is($carrier, 'InArray', [$carriers])
        ) {
            $this->error(__('Tracking carrier is incorrect'));
        }

        // Validate number
        if (! is_string($number)
            || ! \Zend_Validate::is(trim($number), 'NotEmpty')
        ) {
            $this->error(__('Tracking number field cannot be empty'));
        }

        return $this;
    }

    /**
     * Add error message
     *
     * @param  string $message
     * @return $this
     */
    public function error($message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Return error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return array_unique($this->messages);
    }
}
