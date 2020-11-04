<?php
/**
 * Webkul Odoomagentoconnect System Config Reset Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\System\Config;

class Reset extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Set template to itself
     *
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Reset
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/reset.phtml');
        }
        return $this;
    }

    public function getAjaxCheckUrl()
    {
        return $this->getUrl('odoomagentoconnect/reset/reset'); //hit controller by ajax call on button click.
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id'        => 'reset_odoo_mapping',
                'button_label'     => 'Reset Odoo Mapping',
                'onclick'   => 'javascript:check(); return false;'
            ]
        );
        return $this->_toHtml();
    }
}
