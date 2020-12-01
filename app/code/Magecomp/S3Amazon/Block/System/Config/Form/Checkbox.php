<?php

namespace Magecomp\S3Amazon\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Checkbox extends Field
{
    protected $_template = 'Magecomp_S3Amazon::system/config/checkbox.phtml';

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}