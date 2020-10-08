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

namespace Plumrocket\RMA\Block\Returns\Order;

use Plumrocket\RMA\Block\Returns\TemplateTrait;

class Link extends \Magento\Sales\Block\Order\Link
{
    use TemplateTrait;

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->getOrder();
        if ($order
            && ! $this->returnsHelper->getAllByOrder($order)->getSize()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
