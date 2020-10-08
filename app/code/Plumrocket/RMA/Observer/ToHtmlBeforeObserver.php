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

namespace Plumrocket\RMA\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\Position;

class ToHtmlBeforeObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Returnrule
     */
    protected $returnruleHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param Data $dataHelper
     * @param Returnrule $returnruleHelper
     * @param RequestInterface $httpRequest
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Data $dataHelper,
        Returnrule $returnruleHelper,
        RequestInterface $httpRequest,
        SerializerInterface $serializer
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnruleHelper = $returnruleHelper;
        $this->request = $httpRequest;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $block = $observer->getBlock();
        $forOrder = false;

        switch (true) {
            case $this->returnruleHelper->showPosition(Position::SHOPPING_CART)
                && $block instanceof \Magento\Checkout\Block\Cart\Item\Renderer
                && $this->request->getModuleName()        == 'checkout'
                && $this->request->getControllerName()    == 'cart':

                $item = $block->getItem();

                if ($item) {
                    $this->returnruleHelper->setAdditionalOption([$item]);
                }
                break;

            case $block instanceof \Magento\Catalog\Block\Product\View
                && ($item = $block->getProduct()):
            case $this->returnruleHelper->showPosition(Position::PM_ORDER_SUCCESS)
                && $block instanceof \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
                && $this->request->getModuleName()        == 'checkout'
                && $this->request->getControllerName()    == 'onepage'
                && $this->request->getActionName()        == 'success':
            case $this->returnruleHelper->showPosition(Position::CUSTOMER_ORDER)
                && $block instanceof \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
                && $this->request->getModuleName()        == 'sales'
                && $this->request->getControllerName()    == 'order'
                // Fix when html was escaped on print order page.
                && ($this->request->getActionName()        != 'print'
                    || $this->request->getActionName()     == 'print' && $block->setPrintStatus(false))
                && ($forOrder = true):
            case $this->returnruleHelper->showPosition(Position::ORDER_CONFIRMATION)
                && $block instanceof \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
                && $block->getRenderedBlock() instanceof \Magento\Sales\Block\Order\Email\Items
                && ($forOrder = true):

            case $this->returnruleHelper->showPosition(Position::INVOICE)
                && $block instanceof \Magento\Sales\Block\Order\Email\Items\DefaultItems
                && $block->getRenderedBlock() instanceof \Magento\Sales\Block\Order\Email\Invoice\Items
                && ($item = $block->getItem()->getOrderItem())
                && ($forOrder = true):
            case $this->returnruleHelper->showPosition(Position::SHIPMENT)
                && $block instanceof \Magento\Sales\Block\Order\Email\Items\DefaultItems
                && $block->getRenderedBlock() instanceof \Magento\Sales\Block\Order\Email\Shipment\Items
                && ($item = $block->getItem()->getOrderItem())
                && ($forOrder = true):
            case $this->returnruleHelper->showPosition(Position::ADMINPANEL_ORDER)
                && $block instanceof \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer // \Magento\Sales\Block\Adminhtml\Items\AbstractItems
                && $this->request->getControllerName()    == 'order'
                && $this->request->getActionName()        == 'view'
                && ($forOrder = true):

                if (empty($item)) {
                    $item = $block->getItem();
                }

                if ($item) {
                    if ($options = $this->returnruleHelper->getOptions($item, $forOrder)) {
                        $itemOptions = $item->getProductOptions();
                        $doSerialize = false;

                        if (null === $itemOptions) {
                            $itemOptions = [];
                        }

                        try {
                            if (! is_array($itemOptions)
                                && $itemOptions = $this->serializer->unserialize($itemOptions)
                            ) {
                                $doSerialize = true;
                            }
                        } catch (\InvalidArgumentException $e) {
                        }

                        // If use key "additional_options", delivery data will
                        // display before configurable attributes of product.
                        if (empty($itemOptions['attributes_info'])) {
                            $itemOptions['attributes_info'] = [];
                        }

                        $itemOptions['attributes_info'] = array_merge($itemOptions['attributes_info'], $options);
                        if ($doSerialize && ! ($item instanceof \Magento\Sales\Model\Order\Item)) { // need array for order item
                            $itemOptions = $this->serializer->serialize($itemOptions);
                        }
                        $item->setProductOptions($itemOptions);
                    }
                }
                break;
        }
    }
}
