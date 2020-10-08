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

namespace Plumrocket\RMA\Plugin;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\Position;

class QuotePlugin
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
     * @param Data             $dataHelper
     * @param Returnrule       $returnruleHelper
     * @param RequestInterface $httpRequest
     */
    public function __construct(
        Data $dataHelper,
        Returnrule $returnruleHelper,
        RequestInterface $httpRequest
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnruleHelper = $returnruleHelper;
        $this->request = $httpRequest;
    }

    /**
     * Method calls after all checkout items were retrieved
     *
     * @param $subject
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function afterGetAllItems($subject, $items)
    {
        if ($this->dataHelper->moduleEnabled()) {
            switch (true) {
                case $this->returnruleHelper->showPosition(Position::CHECKOUT)
                    && false !== strpos($this->request->getModuleName(), 'checkout')
                    && $this->request->getControllerName()    == 'index'
                    && $this->request->getActionName()        != 'success':
                case $this->returnruleHelper->showPosition(Position::CHECKOUT)
                    && $this->request->getModuleName() == 'onestepcheckout':

                    if (is_array($items)) {
                        $this->returnruleHelper->setAdditionalOption($items);
                    }
                    break;
            }
        }

        return $items;
    }
}
