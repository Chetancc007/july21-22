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

namespace Plumrocket\RMA\Block\Returns;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Helper\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Item as OrderItem;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Condition;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Reason;
use Plumrocket\RMA\Model\Resolution;
use Plumrocket\RMA\Model\Returns\Item;

class Items extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * Image size
     */
    const IMAGE_WIDTH = 130;
    const IMAGE_HEIGHT = 130;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var Returnrule
     */
    protected $returnruleHelper;

    /**
      * @var ReturnsStatus
      */
     protected $status;

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
     * @var Product
     */
    protected $productHelper;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * Cache for current item fields
     *
     * @var array
     */
    protected $itemElements = [];

    /**
     * @param Context       $context
     * @param ItemHelper    $itemHelper
     * @param Returnrule    $returnruleHelper
     * @param ReturnsStatus $status
     * @param Reason        $reason
     * @param Condition     $condition
     * @param Resolution    $resolution
     * @param Product       $productHelper
     * @param ImageFactory  $imageFactory
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ItemHelper $itemHelper,
        Returnrule $returnruleHelper,
        ReturnsStatus $status,
        Reason $reason,
        Condition $condition,
        Resolution $resolution,
        Product $productHelper,
        ImageFactory $imageFactory,
        array $data = []
    ) {
        $this->itemHelper = $itemHelper;
        $this->returnruleHelper = $returnruleHelper;
        $this->status = $status;
        $this->reason = $reason;
        $this->condition = $condition;
        $this->resolution = $resolution;
        $this->productHelper = $productHelper;
        $this->imageFactory = $imageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of items
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->returnsHelper->getItems($this->getEntity());
    }

    /**
     * Retrieve product options
     *
     * @param Item $item
     * @return array
     */
    public function getItemOptions(Item $item)
    {
        $result = [];
        $orderItem = $item->getOrderItem();

        // If it is child of configurable product, get options from parent.
        if ($item->getParentOrderItem()
            && Configurable::TYPE_CODE === $item->getParentOrderItem()->getProductType()
        ) {
            $orderItem = $item->getParentOrderItem();
        }

        if ($options = $orderItem->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * Retrieve item numbers in return process
     *
     * @param  Item $item
     * @return array
     */
    public function getItemNumbers(Item $item)
    {
        $numbers = [];
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($cols as $col) {
            $qty = $item->getData($col);
            if (null !== $qty) {
                $numbers[] = [
                    'label' => $this->itemHelper->getCols($col),
                    'value' => $qty
                ];
            }
        }

        return $numbers;
    }

    /**
     * Check if need to show item numbers
     *
     * @param  Item   $item
     * @return boolean
     */
    public function showItemNumbers(Item $item)
    {
        $numbers = $this->getItemNumbers($item);
        foreach ($numbers as $number) {
            $next = next($numbers);
            if (isset($next['value']) && $number['value'] != $next['value']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get select options to qty
     *
     * @param  int $max
     * @return array
     */
    public function getQtyOptions($max)
    {
        $max = (int)$max;
        return array_combine(range(1, $max), range(1, $max));
    }

    /**
     * Retrieve list of return reasons
     *
     * @return \Plumrocket\RMA\Model\Response[]
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
            $options[$item->getId()] = $item->getLabel() ?: $item->getTitle();
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

        $options = ['' => __('Please choose')];
        foreach ($collection as $item) {
            $options[$item->getId()] = $item->getLabel() ?: $item->getTitle();
        }

        return $options;
    }

    /**
     * Retrieve list of return resolutions
     *
     * @param OrderItem $orderItem
     * @param bool $excludeExpired
     * @return Resolution[]
     */
    public function getResolutionOptions(OrderItem $orderItem, $excludeExpired = false)
    {
        return $this->returnruleHelper
            ->getSelectOptions($orderItem->getProduct(), [
                'exclude_expired' => $excludeExpired,
                'date' => $orderItem->getCreatedAt(),
                'sort' => true
            ]);
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
     * @return int
     */
    public function getQtyToReturn(Item $item)
    {
        return $this->itemHelper->getQtyToReturn($item->getOrderItem());
    }

    /**
     * Check if can return
     *
     * @param  Item   $item
     * @return boolean
     */
    public function canReturn(Item $item)
    {
        return $this->itemHelper->canReturnCustomer($item->getOrderItem());
    }

    /**
     * Check if all item resolutions are expired
     *
     * @param  Item    $item
     * @return boolean
     */
    public function isExpired(Item $item)
    {
        return $this->itemHelper->isExpired($item->getOrderItem());
    }

    /**
     * Retrieve returns by item
     *
     * @param  Item   $item
     * @return [type]
     */
    public function getReturnsByItem(Item $item)
    {
        return $this->returnsHelper->getAllByOrderItem($item->getOrderItemId());
    }

    /**
     * Get unique row key
     *
     * @param  Item   $item
     * @return string
     */
    public function getRowKey(Item $item)
    {
        return (string)$item->getOrderItemId();
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

            // Order item id.
            $key = ItemHelper::ORDER_ITEM_ID;
            $elements[$key] = $this->createElement($key, 'hidden', [
                'name'      => "items[$rowKey][$key]",
                'value'     => $item->getOrderItemId(),
            ], $form);

            // Return Active.
            $key = 'active';
            $elements[$key] = $this->createElement($key, 'checkbox', [
                'name'      => "items[$rowKey][$key]",
                'value'     => 1,
                'disabled'  => ! $this->canReturn($item),
            ], $form);

            if ($this->canReturn($item)) {
                // Requested Qty.
                $key = ItemHelper::QTY_REQUESTED;
                $elements[$key] = $this->createElement($key, 'select', [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'required'  => true,
                    'class'     => 'required',
                    'options'   => $this->getQtyOptions($this->getQtyToReturn($item)),
                ], $form);

                // Return Reason.
                $key = ItemHelper::REASON_ID;
                $elements[$key] = $this->createElement($key, 'select', [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'required'  => true,
                    'class'     => 'required ' . ItemHelper::REASON_ID,
                    'options'   => $this->getReasonOptions(),
                ], $form);

                // Item Condition.
                $key = ItemHelper::CONDITION_ID;
                $elements[$key] = $this->createElement($key, 'select', [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => __('Item Condition'),
                    'options'   => $this->getConditionOptions(),
                ], $form);

                // Resolution.
                $key = ItemHelper::RESOLUTION_ID;
                $elements[$key] = $this->createElement($key, 'select', [
                    'name'      => "items[$rowKey][$key]",
                    'label'     => $this->itemHelper->getCols($key),
                    'required'  => true,
                    'class'     => 'required',
                    'options'   => $this->getResolutionOptions($item->getOrderItem()),
                ], $form);
            }

            // Set values.
            $data = $this->getDataHelper()->getFormData('items');
            if (! empty($data[$rowKey])) {
                foreach ($elements as $key => $element) {
                    if (! isset($data[$rowKey][$key])) {
                        continue;
                    }

                    if ($element->getType() == 'checkbox') {
                        $element->setIsChecked((bool)$data[$rowKey][$key]);
                    } else {
                        $element->setValue($data[$rowKey][$key]);
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
            $html .= '<div class="field' . ($element->getRequired()? ' required' : '') . '">';
            $html .= $element->getLabelHtml();
            $html .= $element->getElementHtml();
            $html .= '</div>';
        }

        return $html;
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
            self::IMAGE_HEIGHT
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
