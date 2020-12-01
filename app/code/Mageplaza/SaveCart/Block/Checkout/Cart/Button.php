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
 * @package     Mageplaza_ShareCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Block\Checkout\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class Button
 * @package Mageplaza\SaveCart\Block\Checkout\Cart
 */
class Button extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Button constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param CheckoutSession $checkoutSession
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        CheckoutSession $checkoutSession,
        FormKey $formKey,
        array $data = []
    ) {
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->formKey         = $formKey;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->helper->getLabel() ?: __('Save Cart');
    }

    /**
     * @return string
     */
    public function saveCartUrl()
    {
        return $this->getUrl('mpsavecart/index/savecart');
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function showButton()
    {
        $quote = $this->checkoutSession->getQuote();

        return $this->helper->isEnabled() && $quote->getItemsCount() &&
            ($this->helper->showButtonGuest() || $quote->getCustomerId());
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
