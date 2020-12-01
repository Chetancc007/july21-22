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

namespace Mageplaza\SaveCart\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class MoveToProductList
 * @package Mageplaza\SaveCart\Block\Cart\Item\Renderer\Actions
 */
class MoveToProductList extends Generic
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * MoveToProducts constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check whether "add to product list" button is allowed in cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->helper->isAllowInCart();
    }

    /**
     * Get JSON POST params for moving from cart
     *
     * @return string
     */
    public function getMoveFromCartParams()
    {
        return $this->helper->getMoveFromCartParams($this->getItem()->getId());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getIconHtml()
    {
        return $this->helper->getIconHtml();
    }
}
