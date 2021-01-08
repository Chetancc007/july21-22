<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Notice extends AbstractElement
{
    public function getHtml()
    {
        return '<div name="' . $this->getName() . '" class="message message-info info">' . $this->getNoticeText()
            . '</div></br>';
    }

    public function getNoticeText()
    {
        return $this->_getData('notice_text');
    }
}
