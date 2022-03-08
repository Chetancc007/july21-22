<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductTableAttribute\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Entity\AttributeFactory;

/**
 * ProductTableAttribute data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var AttributeFactory $attribute
     */
    private $attributeFactory;

    /**
     * @param Context $context
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Context $context,
        AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * get table type attributes
     *
     * @param int $attributeSetId
     * @return array
     */
    public function getTableAttributes($attributeSetId)
    {
        $attributes = [];
        $attributeCollection = $this->attributeFactory->create()
                                      ->getCollection()
                                      ->setAttributeSetFilter($attributeSetId);
        foreach ($attributeCollection as $attribute) {
            if ($attribute->getFrontendInput() == "table") {
                $attributes[] = [
                    'attribute_id'  =>  $attribute->getId(),
                    'attribute_name'=>  $attribute->getFrontendLabel(),
                    'attribute_code'=>  $attribute->getAttributeCode()
                ];
            }
        }
        return $attributes;
    }

    /**
     * get default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSet()
    {
        $set = $this->scopeConfig
                    ->getValue('producttableattribute/general_settings/attribute');
        if ($set) {
            return $set;
        } else {
            return 4;
        }
    }
}
