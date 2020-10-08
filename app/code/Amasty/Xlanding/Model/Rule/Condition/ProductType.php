<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


declare(strict_types=1);

namespace Amasty\Xlanding\Model\Rule\Condition;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductType extends AbstractCondition
{
    /**
     * @var string
     */
    protected $_inputType = 'multiselect';

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'multiselect';
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'product_type';
    }

    /**
     * @return array|null
     */
    public function getDefaultOperatorInputByType()
    {
        parent::getDefaultOperatorInputByType();
        $this->_defaultOperatorInputByType['product_type'] = ['==', '!=', '()', '!()'];

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'product_type';
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getAttributeElementHtml()
    {
        return __('Product Type by "type_id" attribute');
    }

    /**
     * @return $this|ProductType
     */
    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        $selectOptions = $this->getData('productTypes')->getAllOptions();

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $select
     * @return ProductType|void
     */
    public function collectValidatedAttributes($select)
    {
        $value = $this->getValue();
        $operator = $this->getOperatorForValidate();
        $this->_condition = $this->getOperatorCondition(ProductInterface::TYPE_ID, $operator, $value);
    }
}
