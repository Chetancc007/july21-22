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

namespace Mageplaza\SaveCart\Block\View\Element\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Config\Source\PageLinkArea;

/**
 * Class Link
 * @package Mageplaza\SaveCart\Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Link constructor.
     *
     * @param Data $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function showLinkOn()
    {
        return $this->helper->showLinkOn(PageLinkArea::FOOTER);
    }
}
