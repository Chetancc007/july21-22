<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\AmastyElastic\Model\Indexer\Data\Product;

use Amasty\Conf\Helper\Group as GroupHelper;

class ProductDataMapperPlugin
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
    private function getGroupedOptions($attributeId) : array
    {
        if (!isset($this->groupedOptions[$attributeId])) {
            $this->groupedOptions[$attributeId] = [];
            $collection = $this->groupHelper
                ->getGroupCollection($attributeId)
                ->joinOptions()
                ->groupByCode();

            foreach ($collection as $option) {
                $fakeKey = $this->groupHelper->getFakeKey($option->getGroupId());
                $this->groupedOptions[$attributeId][$fakeKey] = $option->getName();
            }
        }

        return $this->groupedOptions[$attributeId];
    }
}
