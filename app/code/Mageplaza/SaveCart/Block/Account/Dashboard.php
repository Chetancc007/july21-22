<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Block\Account;

use Magento\Framework\View\Element\Template;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class Dashboard
 * @package Mageplaza\SaveCart\Block\Account
 */
class Dashboard extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Dashboard constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @param float $price
     *
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->helper->convertPrice($price, true, false);
    }
}
