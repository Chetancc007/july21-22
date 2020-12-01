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

namespace Mageplaza\SaveCart\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class BuyLater
 * @package Mageplaza\SaveCart\Block\Adminhtml\Customer\Edit\Tab
 */
class BuyLater extends Template implements TabInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * BuyLater constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Get Customer Id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get Tab Label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Buy Later Notes');
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Buy Later Notes');
    }

    /**
     * Can show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getCustomerId() && $this->_authorization->isAllowed('Mageplaza_SaveCart::configuration');
    }

    /**
     * Is Hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return !$this->getCustomerId() && $this->_authorization->isAllowed('Mageplaza_SaveCart::configuration');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('mpsavecart/customer/saveCart', ['_current' => true]);
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}
