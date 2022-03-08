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

namespace Webkul\ProductTableAttribute\Plugin\Product\Set;

class SavePlugin
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attrGroupCollection
    ) {
        $this->attribute = $attribute;
        $this->attributeManagement = $attributeManagement;
        $this->attrGroupCollection = $attrGroupCollection;
    }

    /**
     * to add the dependent attribute to attribute set as well
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Set\Save $subject,
        callable $proceed
    ) {
        $attributeSetId = $subject->getRequest()->getParam('id', false);
        if ($attributeSetId != "") {
            $params = json_decode($subject->getRequest()->getParam('data'));
            $group = $this->attrGroupCollection->create()
                        ->addFieldToFilter('attribute_group_name', 'Content')
                        ->addFieldToFilter('attribute_set_id', $attributeSetId)
                        ->setPageSize(1)
                        ->getFirstItem();
            $attrs = $params->attributes;
            foreach ($attrs as $param) {
                $this->assignAttributeSet($param[0], $attributeSetId, $group->getId());
            }
        }
        return $proceed();
    }

    /**
     * assign attribute set to other attribute ids
     *
     * @param int $attributeId
     * @return void
     */
    private function assignAttributeSet($attributeId, $attributeSetId, $groupId)
    {
        $attributeInfo = $this->attribute->load($attributeId);
        if ($attributeInfo->getFrontendInput() == "table") {
            $code = $attributeInfo->getAttributeCode();
            $otherCode = "wk_ta_".$code;
            $this->attributeManagement->assign(
                'catalog_product',
                $attributeSetId,
                $groupId,
                $otherCode,
                999
            );
        }
    }
}
