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

use Magento\Checkout\Model\Session;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\Position;

class QuoteTotalsItemPlugin
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
     * @var Session
     */
    protected $session;

    /**
     * @param Data       $dataHelper
     * @param Returnrule $returnruleHelper
     * @param Session    $session
     */
    public function __construct(
        Data $dataHelper,
        Returnrule $returnruleHelper,
        Session $session
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnruleHelper = $returnruleHelper;
        $this->session = $session;
    }

    /**
     * Method calls after product options retrieved on checkout
     */
    public function afterGetOptions($subject, $options)
    {
        if ($this->dataHelper->moduleEnabled()) {
            switch (true) {
                case $this->returnruleHelper->showPosition(Position::CHECKOUT):
                    if ($_options = $this->getOptions($this->session->getQuote()->getItemById($subject->getItemId()))) {
                        if (is_string($options)) {
                            $options = json_decode($options, true);
                        }
                        if (! is_array($options)) {
                            $options = [];
                        }
                        $options = json_encode(array_merge($options, $_options));
                    }
                    break;
            }
        }

        return $options;
    }

    /**
     * Retrieve product options
     *
     * @param  object $item
     * @return array|null
     */
    protected function getOptions($item)
    {
        if (! $product = $item->getProduct()) {
            return;
            // $product = $this->productFactory->create()->load($item->getProductId());
        }

        return $this->returnruleHelper->getProductOptions($product);
    }
}
