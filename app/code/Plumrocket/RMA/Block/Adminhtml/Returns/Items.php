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

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Plumrocket\RMA\Block\Adminhtml\Returns\Template;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Condition;
use Plumrocket\RMA\Model\Data\Form\Element\Number;
use Plumrocket\RMA\Model\Reason;
use Plumrocket\RMA\Model\Resolution;
use Plumrocket\RMA\Model\Response;
use Plumrocket\RMA\Model\Returns\Item;
use Plumrocket\RMA\Model\Returns\ItemFactory;

class Items extends Template
{
    /**
     * Image size
     */
    const IMAGE_WIDTH = 75;
    const IMAGE_HEIGHT = 75;

    /**
     * @var Reason
     */
    protected $reason;

    /**
     * @var Condition
     */
    protected $condition;

    /**
     * @var Resolution
     */
    protected $resolution;

    /**
     * @var ReturnsHelper
     */
    protected $returnsHelper;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Cache of item elements
     *
     * @var array
     */
    protected $itemElements = [];

    /**
     * @var StockItem
     */
    protected $stockItem;

    /**
     * Info of elements
     *
     * @var array
     */
    protected $info = [
        ItemHelper::REASON_ID     => '"Return Reason" is why customers are returning an item. You can edit or add new "Return Reasons" in Plumrocket RMA -> Return Reasons.',
        ItemHelper::CONDITION_ID  => '"Item Condition" describes the physical condition of the returned item.  You can edit or add a new "Item Condition" in Plumrocket RMA -> Item Conditions.',
        ItemHelper::RESOLUTION_ID => '"Resolution" describes how customers want to resolve the return. You can edit or add a new "Resolution" in Plumrocket RMA -> Resolutions.',

        ItemHelper::QTY_PURCHASED => '"Purchased Qty" indicates the quantity of items purchased by the customer.',
        ItemHelper::QTY_REQUESTED => '"Return Qty" indicates the quantity of items the customer wants to return.',
        ItemHelper::QTY_AUTHORIZED => 'Indicates quantity of items approved for return by the RMA Manager. Once the RMA Manager has "authorized" all or some items for return, the customer will be automatically notified by email and can proceed to ship the authorized quantity of items back to store.<br /><br />Admin can configure the RMA extension to automatically authorize all returns created by customers from Plumrocket RMA -> Configuration -> Create New RMA Options -> Automatically Authorize Returns',
        ItemHelper::QTY_RECEIVED  => 'Indicates the quantity of items received by the store.',
        ItemHelper::QTY_APPROVED  => 'Indicates the quantity of items approved by the RMA Manager for resolution (refund, exchange, etc.)',

        'action'            => '"Split" allows you to break the item in multiple rows to specify different return reasons, item conditions, resolutions, etc.<br />Example: Customer received 5 broken chairs and created an RMA requesting to exchange all 5 of them. However, store owner has only 3 chairs available in the warehouse. Therefore, the store owner can "split" the item to return in two rows. First row will include 3 chairs for exchange, and second row will include 2 chairs for a full refund or store credit.',
    ];

    /**
     * @param Context             $context
     * @param Reason              $reason
     * @param Condition           $condition
     * @param Resolution          $resolution
     * @param ReturnsHelper       $returnsHelper
     * @param ItemHelper          $itemHelper
     * @param ItemFactory         $itemFactory
     * @param StockItem           $stockItem
     * @param array               $data
     */
    public function __construct(
        Context $context,
        Reason $reason,
        Condition $condition,
        Resolution $resolution,
        ReturnsHelper $returnsHelper,
        ItemHelper $itemHelper,
        ItemFactory $itemFactory,
        StockItem $stockItem,
        array $data = []
    ) {
        $this->reason = $reason;
        $this->condition = $condition;
        $this->resolution = $resolution;
        $this->returnsHelper = $returnsHelper;
        $this->itemHelper = $itemHelper;
        $this->itemFactory = $itemFactory;
        $this->stockItem = $stockItem;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of items
     *
     * @return Item[]
     */
    public function getItems()
    {
        $items = $this->returnsHelper->getItems($this->getEntity());

        // Autofill items.
        $itemsAutofill = $this->getDataHelper()->getFormData('items');
        if (is_array($itemsAutofill)) {
            $_items = [];
            $n = 1;
            foreach ($itemsAutofill as $data) {
                $item = $this->itemFactory->create(['data' => $data])
                    ->setReturns($this->getEntity())
                    ->setRowNumber($n++);

                if ('' !== $data[ItemHelper::ENTITY_ID]
                    && isset($items[$data[ItemHelper::ENTITY_ID]])
                ) {
                    $item->setQtyPurchased(
                        $items[$data[ItemHelper::ENTITY_ID]]->getQtyPurchased()
                    );
                }

                $_items[] = $item;
            }

            $items = $_items;
        }

        return $items;
    }

    /**
     * Retrieve list of return reasons
     *
     * @return Response[]
     */
    public function getReasonOptions()
    {
        $collection = $this->reason
            ->getCollection()
            ->addActiveFilter()
            ->addStoreFilter($this->getOrder()->getStoreId())
            ->setOrder('position', 'asc');

        $options = ['' => __('Please choose')];
        foreach ($collection as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * Get payer owner reasons
     *
     * @return array
     */
    public function getPayerOwnerReasons()
    {
        $reasons = $this->reason
            ->getCollection()
            ->addActiveFilter()
            ->addStoreFilter($this->getOrder()->getStoreId())
            ->addPayerOwnerFilter()
            ->toOptionHash();

        return array_keys($reasons);
    }

    /**
     * Retrieve list of return conditions
     *
     * @return Condition[]
     */
    public function getConditionOptions()
    {
        $collection = $this->condition
            ->getCollection()
            ->addActiveFilter()
            ->addStoreFilter($this->getOrder()->getStoreId())
            ->setOrder('position', 'asc');

        return ['' => __('Please choose')] + $collection->toOptionHash();
    }

    /**
     * Retrieve list of return resolutions
     *
     * @return Resolution[]
     */
    public function getResolutionOptions()
    {
        $collection = $this->resolution
            ->getCollection()
            ->addActiveFilter()
            ->addStoreFilter($this->getOrder()->getStoreId())
            ->setOrder('position', 'asc');

        return $collection->toOptionHash();
    }

    /**
     * Retrieve url for load template
     *
     * @return string
     */
    public function getLoadTemplateUrl()
    {
        return $this->getUrl('*/*/loadTemplate');
    }

    /**
     * Retrieve product url
     *
     * @param Item $item
     * @return string
     */
    public function getProductUrl(Item $item)
    {
        return $this->getUrl('catalog/product/edit', [
            'id' => $item->getOrderItem()->getProductId()
        ]);
    }

    /**
     * Retrieve qty allowed to return
     *
     * @param Item $item
     * @return int
     */
    public function getQtyToReturn(Item $item)
    {
        return $this->itemHelper->getQtyToReturn(
            $item->getOrderItem(),
            $this->getEntity()->getId()
        );
    }

    /**
     * Retrieve max item qty that allowed to return
     *
     * @param  Item   $item
     * @return int
     */
    public function getMaxQty(Item $item)
    {
        // $qty = $item->getQtyPurchased() ?: $this->getQtyToReturn($item);
        $qty = $this->getQtyToReturn($item);
        return max(0, (int)$qty);
    }

    /**
     * Get unique row key
     *
     * @param  Item   $item
     * @return string
     */
    public function getRowKey(Item $item)
    {
        if (is_numeric($item->getId())) {
            $rowKey = $item->getId();
        } else {
            $rowKey = 'new' . $item->getRowNumber();
        }

        return $rowKey;
    }

    /**
     * Get columns list
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = [
            ItemHelper::REASON_ID       => $this->itemHelper->getCols(ItemHelper::REASON_ID),
            ItemHelper::CONDITION_ID    => $this->itemHelper->getCols(ItemHelper::CONDITION_ID),
            ItemHelper::RESOLUTION_ID   => $this->itemHelper->getCols(ItemHelper::RESOLUTION_ID),
            ItemHelper::QTY_PURCHASED   => $this->itemHelper->getCols(ItemHelper::QTY_PURCHASED),
            ItemHelper::QTY_REQUESTED   => $this->itemHelper->getCols(ItemHelper::QTY_REQUESTED),
        ];

        if (! $this->isNewEntity()) {
            $columns = array_merge($columns, [
                ItemHelper::QTY_AUTHORIZED  => $this->itemHelper->getCols(ItemHelper::QTY_AUTHORIZED),
                ItemHelper::QTY_RECEIVED    => $this->itemHelper->getCols(ItemHelper::QTY_RECEIVED),
                ItemHelper::QTY_APPROVED    => $this->itemHelper->getCols(ItemHelper::QTY_APPROVED),
            ]);
        }

        return $columns;
    }

    /**
     * Get column info
     *
     * @param  string $name
     * @return string
     */
    public function getInfo($name)
    {
        return isset($this->info[$name]) ? __($this->info[$name]) : '';
    }

    /**
     * Get elements of item table
     *
     * @param  Item   $item
     * @param  string|null $name
     * @return \Magento\Framework\Data\Form\Element\AbstractElement|null
     */
    public function getElements(Item $item, $name = null)
    {
        $rowKey = $this->getRowKey($item);

        if (empty($this->itemElements[$rowKey])) {
            $elements = [];

            $form = $this->formFactory->create()
                ->setHtmlIdPrefix("items_{$rowKey}_");

            $qtyMax = $this->getMaxQty($item);

            // Item id.
            $key = ItemHelper::ENTITY_ID;
            $elements[$key] = $this->createElement($key, 'hidden', [
                'name'      => "items[$rowKey][$key]",
                'value'     => $item->getId(),
            ], $form);

            // Order item id.
            $key = ItemHelper::ORDER_ITEM_ID;
            $elements[$key] = $this->createElement($key, 'hidden', [
                'name'      => "items[$rowKey][$key]",
                'value'     => $item->getOrderItemId(),
            ], $form);

            // Return Reason.
            $key = ItemHelper::REASON_ID;
            $elements[$key] = $this->createElement($key, 'select', [
                'name'      => "items[$rowKey][$key]",
                'label'     => $this->itemHelper->getCols($key),
                'options'   => $this->getReasonOptions(),
            ], $form);

            // Item Condition.
            $key = ItemHelper::CONDITION_ID;
            $elements[$key] = $this->createElement($key, 'select', [
                'name'      => "items[$rowKey][$key]",
                'label'     => $this->itemHelper->getCols($key),
                'options'   => $this->getConditionOptions(),
            ], $form);

            // Resolution.
            $key = ItemHelper::RESOLUTION_ID;
            $elements[$key] = $this->createElement($key, 'select', [
                'name'      => "items[$rowKey][$key]",
                'label'     => $this->itemHelper->getCols($key),
                'options'   => $this->getResolutionOptions(),
            ], $form);

            // Return Purchased Qty.
            $key = ItemHelper::QTY_PURCHASED;
            $elements[$key] = $this->createElement($key, 'note', [
                'label'     => $this->itemHelper->getCols($key),
                'text'      => $qtyMax ?: '-',
            ], $form);

            // Requested Qty.
            $key = ItemHelper::QTY_REQUESTED;
            $elements[$key] = $this->createElement($key, Number::class, [
                'name'      => "items[$rowKey][$key]",
                'label'     => $this->itemHelper->getCols($key),
                'min'       => '0',
                'max'       => $qtyMax,
            ], $form);

            if (! $this->isNewEntity()) {
                // Authorized Qty.
                $key = ItemHelper::QTY_AUTHORIZED;
                $elements[$key] = $this->createElement($key, Number::class, [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'min'       => '0',
                    'max'       => $qtyMax
                ], $form);

                // Returned Qty.
                $key = ItemHelper::QTY_RECEIVED;
                $elements[$key] = $this->createElement($key, Number::class, [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'min'       => '0',
                    'max'       => $qtyMax
                ], $form);

                // Approved Qty.
                $key = ItemHelper::QTY_APPROVED;
                $elements[$key] = $this->createElement($key, Number::class, [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'min'       => '0',
                    'max'       => $qtyMax
                ], $form);
            }

            // Set values.
            foreach ($elements as $key => $element) {
                if (null === $element->getValue() || '' === $element->getValue()) {
                    $element->setValue($item->getData($key));
                }

                if ($this->getEntity()->isClosed() || ! $qtyMax) {
                    if ($element->getType() != 'hidden') {
                        $element->setDisabled(true);
                    }
                }
            }

            $this->itemElements = [];
            $this->itemElements[$rowKey] = $elements;
        }

        if (null !== $name) {
            return isset($this->itemElements[$rowKey][$name]) ?
                $this->itemElements[$rowKey][$name] :
                null;
        }

        return $this->itemElements[$rowKey];
    }

    /**
     * Get element html
     *
     * @param  Item $item
     * @param  string $name
     * @return string
     */
    public function getElementHtml(Item $item, $name)
    {
        $html = '';
        if ($element = $this->getElements($item, $name)) {
            $html = $element->getElementHtml();
        }

        return $html;
    }

    /**
     * Get stock item by return item
     *
     * @param  Item   $item
     * @return int
     */
    public function getStockItem(Item $item)
    {
        $productId = $item->getOrderItem()->getProductId();
        return $this->stockItem->load($productId, 'product_id');
    }

    /**
     * Get item product image url
     *
     * @param  Item $item
     * @return string
     */
    public function getImageUrl(Item $item)
    {
        return $this->itemHelper->getImageUrl(
            $item->getOrderItem(),
            self::IMAGE_WIDTH,
            self::IMAGE_HEIGHT,
            'product_listing_thumbnail'
        );
    }

    /**
     * Get item status label
     *
     * @param  Item $item
     * @return string
     */
    public function getStatusLabel(Item $item)
    {
        return $this->itemHelper->getStatusLabel($item);
    }
}
