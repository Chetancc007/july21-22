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

namespace Mageplaza\SaveCart\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class Link
 * @package Mageplaza\SaveCart\Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    const SORT_ORDER = 'sortOrder';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Link constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper->showLinkOn()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('mpsavecart/dashboard');
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return __('Buy Later Notes');
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
