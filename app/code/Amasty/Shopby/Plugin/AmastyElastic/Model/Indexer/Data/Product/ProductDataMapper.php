<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\AmastyElastic\Model\Indexer\Data\Product;

use Amasty\Shopby\Helper\Group as GroupHelper;

class ProductDataMapper
{
    /**
     * @var GroupHelper
     */
    private $groupHelper;

    /**
     * @var array|null
     */
    private $groupedOptions;

    public function __construct(GroupHelper $groupHelper)
    {
        $this->groupHelper = $groupHelper;
    }

    /**
     * @param mixed $subject
     * @param \Closure $closure
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return array
     */
    public function aroundGetAttributeOptions(
        $subject,
        \Closure $closure,
        \Magento\Eav\Model\Entity\Attribute $attribute
    ) {
        return $closure($attribute) + $this->getGroupedOptions($attribute->getAttributeId());
    }

    /**
     * @param int $attributeId
     * @return array
     */
    private function getGroupedOptions($attributeId)
    {
        if (!isset($this->groupedOptions[$attributeId])) {
            $this->groupedOptions[$attributeId] = [];
            $collection = $this->groupHelper
                ->getGroupCollection($attributeId)
                ->joinOptions();
            $collection->getSelect()->group('group_code');
            foreach ($collection as $option) {
                $fakeKey = $this->groupHelper->getFakeKey($option->getGroupId());
                $this->groupedOptions[$attributeId][$fakeKey] = $option->getName();
            }
        }

        return $this->groupedOptions[$attributeId];
    }
}
