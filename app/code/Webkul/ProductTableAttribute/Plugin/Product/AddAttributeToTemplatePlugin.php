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

namespace Webkul\ProductTableAttribute\Plugin\Product;

class AddAttributeToTemplatePlugin
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->attribute = $attribute;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * to add the dependent attribute to product as well
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\AddAttributeToTemplate $subject,
        callable $proceed
    ) {
    
        $otherIds = [];
        $attributesArray = (array) $subject->getRequest()->getParam('attributeIds', []);
        $selected = $attributesArray['selected'];
        foreach ($selected as $select) {
            $otherIds[] = $this->getOtherIds($select);
        }
        $finalArray = array_unique(array_merge($selected, $otherIds), SORT_REGULAR);
        unset($attributesArray['selected']);
        $attributesArray['selected'] = $finalArray;
        $subject->getRequest()->setParam('attributeIds', $attributesArray);

        return $proceed();
    }

    /**
     * get other attribute ids
     *
     * @param int $attributeId
     * @return int|void
     */
    private function getOtherIds($attributeId)
    {
        $attributeInfo = $this->attribute->load($attributeId);
        if ($attributeInfo->getFrontendInput() == "table") {
            $code = $attributeInfo->getAttributeCode();
            $otherCode = "wk_ta_".$code;
            return $this->eavAttribute->getIdByCode('catalog_product', $otherCode);
        }
    }
}
