<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider as MagentoDataProvider;
use Amasty\Shopby\Helper\Group as GroupHelper;
use Magento\Framework\App\ResourceConnection;
use Amasty\Shopby\Model\CatalogSearch\Indexer\Fulltext\DataProvider as AmastyDataProvider;

class DataProvider
{
    /**
     * @var GroupHelper
     */
    private $groupHelper;

    /**
     * @var array|null
     */
    private $groupedOptions;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AmastyDataProvider
     */
    private $amastyDataProvider;

    public function __construct(
        GroupHelper $groupHelper,
        ResourceConnection $resourceConnection,
        AmastyDataProvider $amastyDataProvider
    ) {
        $this->groupHelper = $groupHelper;
        $this->resource = $resourceConnection;
        $this->amastyDataProvider = $amastyDataProvider;
    }

    /**
     * @param MagentoDataProvider $subject
     * @param array $indexData
     * @return array
     */
    public function afterGetProductAttributes(MagentoDataProvider $subject, array $indexData)
    {
        $indexData = $this->addGroupedToIndexData($indexData);

        return $indexData;
    }

    /**
     * @param array $indexData
     * @return array
     */
    private function addGroupedToIndexData(array $indexData)
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
    private function getGroupedOptions()
    {
        if ($this->groupedOptions === null) {
            /** @var \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection $groupedCollection */
            $groupedCollection = $this->groupHelper->getGroupCollection();
            $groupedCollection
                ->addFieldToSelect(['attribute_id', 'group_code'])
                ->joinOptions()
                ->getSelect()
                ->columns('group_concat(`aagao`.`option_id`) as options')
                ->group('group_id');
            $fetched = $groupedCollection->getConnection()->fetchAll($groupedCollection->getSelect());

            $this->groupedOptions = [];
            foreach ($fetched as $group) {
                foreach (explode(',', $group['options']) as $attributeOptionId) {
                    $this->groupedOptions[$group['attribute_id']][$attributeOptionId][] =
                        \Amasty\Shopby\Helper\Group::LAST_POSSIBLE_OPTION_ID - $group['group_id'];
                }
            }
        }

        return $this->groupedOptions;
    }

    /**
     * @param MagentoDataProvider $subject
     * @param callable $proceed
     * @param string $storeId
     * @param array $staticFields
     * @param array|null $productIds
     * @param int|string $lastProductId
     * @param int|string $batchSize
     * @return array
     */
    public function aroundGetSearchableProducts(
        MagentoDataProvider $subject,
        callable $proceed,
        $storeId,
        array $staticFields,
        $productIds = null,
        $lastProductId = 0,
        $batchSize = 100
    ): array {
        return $this->amastyDataProvider->getSearchableProducts(
            (int)$storeId,
            $staticFields,
            $productIds,
            (int)$lastProductId,
            (int)$batchSize
        );
    }
}
