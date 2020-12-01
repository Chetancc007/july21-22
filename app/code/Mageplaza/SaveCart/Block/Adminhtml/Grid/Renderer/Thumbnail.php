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

namespace Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Product;

/**
 * Class Thumbnail
 * @package Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer
 */
class Thumbnail extends AbstractRenderer
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Thumbnail constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param Product |DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $imageUrl = $this->helperData->getImage($row->getData(), $row->getStoreId());

        return '<img src="' . str_replace('\\', '/', $imageUrl) . '" width="50"/>';
    }
}
