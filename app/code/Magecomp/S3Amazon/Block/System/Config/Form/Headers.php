<?php
namespace Magecomp\S3Amazon\Block\System\Config\Form;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Factory;
class Headers extends AbstractFieldArray
{
    protected $_elementFactory;
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        $this->addColumn('header', ['label' => __('Header')]);
        $this->addColumn('value', ['label' => __('Value')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Custom Header');
        parent::_construct();
    }
}