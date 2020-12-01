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
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Framework\DataObject;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;

/**
 * Class CartPrice
 * @package Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer
 */
class CartPrice extends AbstractRenderer
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    protected $customerRepositoryFactory;

    /**
     * CartPrice constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param CustomerRepositoryInterfaceFactory $customerRepositoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
        array $data = []
    ) {
        $this->helperData                = $helperData;
        $this->customerRepositoryFactory = $customerRepositoryFactory;

        parent::__construct($context, $data);
    }

    /**
     * @param Cart|DataObject $row
     *
     * @return float|string
     */
    public function render(DataObject $row)
    {
        $customerGroupId = $this->customerRepositoryFactory->create()->getById($row->getCustomerId());

        return $this->helperData->getCartTotal($row->getData(), $customerGroupId);
    }
}
