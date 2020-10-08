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

namespace Plumrocket\RMA\Plugin\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returns;

class OrderView
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Returns
     */
    protected $returnsHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Data             $dataHelper
     * @param Returns          $returnsHelper
     * @param RequestInterface $httpRequest
     * @param UrlInterface     $urlBuilder
     */
    public function __construct(
        Data $dataHelper,
        Returns $returnsHelper,
        RequestInterface $httpRequest,
        UrlInterface $urlBuilder
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnsHelper = $returnsHelper;
        $this->request = $httpRequest;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Add button to order view
     *
     * @param  View $subject
     * @return void
     */
    public function beforeGetOrderId(View $subject)
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return;
        }

        $order = $subject->getOrder();
        if (! $this->returnsHelper->canReturnAdmin($order)) {
            return;
        }

        $url = $this->getReturnsUrl($subject);
        $subject->addButton(
            'returns_button',
            [
                'label' => __('Return'),
                'onclick' => "setLocation('$url')",
                'class' => 'returns_button'
            ],
            1
        );
    }

    /**
     * Returns URL getter
     *
     * @return string
     */
    public function getReturnsUrl($subject)
    {
        // Can't call method $subject->getOrderId(), because this plugin use it.
        $orderId = $subject->getOrder()->getId();
        return $this->urlBuilder->getUrl('prrma/returns/new', [
            'order_id' => $orderId
        ]);
    }
}
