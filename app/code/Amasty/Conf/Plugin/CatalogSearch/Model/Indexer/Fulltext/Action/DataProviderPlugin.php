<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider as MagentoDataProvider;
use Amasty\Conf\Helper\Group as GroupHelper;

class DataProviderPlugin
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
     * @param MagentoDataProvider $subject
     * @param array $indexData
     * @return array
     */
    public function afterGetProductAttributes(MagentoDataProvider $subject, array $indexData) : array
    {
        $indexData = $this->addGroupedToIndexData($indexData);

        return $indexData;
    }

    /**
     * @param array $indexData
     * @return array
     */
    private function addGroupedToIndexData(array $indexData) : array
    {
        $groupedOptions = $this->getGroupedOptions();
        foreach ($groupedOptions as $attributeId => $optionData) {
            $allAttributeOptionsContainedInGroups = array_keys($optionData);
            foreach ($indexData as &$product) {
                if (isset($product[$attributeId])) {
                    $productOptions = explode(',', $product[$attributeId]);
                    $intersectedOptionIds = array_intersect($allAttributeOptionsContainedInGroups, $productOptions);
                    if (!$intersectedOptionIds) {
                        continue;
                    }

                    $intersectedGroupedData = array_intersect_key($optionData, array_flip($intersectedOptionIds));
                    if (count($intersectedGroupedData)) {
                        // @codingStandardsIgnoreLine
                        $gropedValues = array_unique(array_merge(...$intersectedGroupedData));
                    } else {
                        $gropedValues = [];
                    }

                    $notGroupedOptions = array_diff($productOptions, $allAttributeOptionsContainedInGroups);
                    //@codingStandardsIgnoreLine
                    $allValues = array_merge($gropedValues, $notGroupedOptions);
                    $product[$attributeId] = implode(',', $allValues);
                }
            }
        }

        return $indexData;
    }

    /**
     * @return array
     */
    private function getGroupedOptions() : array
    {
        if ($this->groupedOptions === null) {
            /** @var \Amasty\Conf\Model\ResourceModel\GroupAttr\Collection $groupedCollection */
            $groupedCollection = $this->groupHelper->getGroupCollection();
            $groupedCollection
                ->addFieldToSelect(['attribute_id', 'group_code'])
                ->addOptionsToSelect();
            $fetched = $groupedCollection->getConnection()->fetchAll($groupedCollection->getSelect());

            $this->groupedOptions = [];
            foreach ($fetched as $group) {
                foreach (explode(',', (string)$group['options']) as $attributeOptionId) {
                    $this->groupedOptions[$group['attribute_id']][$attributeOptionId][] =
                        $this->groupHelper->getFakeKey($group['group_id']);

                }
            }
        }

        return $this->groupedOptions;
    }
}
