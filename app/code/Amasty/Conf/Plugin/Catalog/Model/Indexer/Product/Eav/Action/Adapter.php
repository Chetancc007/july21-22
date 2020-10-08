<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\Catalog\Model\Indexer\Product\Eav\Action;

use Amasty\Conf\Helper\Group as GroupHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;
use Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory as GroupOptionCollectionFactory;

class Adapter
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $indexTable;

    /**
     * @var GroupHelper
     */
    private $helper;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttrOption\Collection
     */
    private $groupOptionCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $groupedOptions = null;

    public function __construct(
        EavSource $eavSource,
        GroupHelper $helper,
        GroupOptionCollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->connection = $eavSource->getConnection();
        $this->indexTable = $eavSource->getMainTable();
        $this->helper = $helper;
        $this->groupOptionCollection = $collectionFactory->create();
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @throws \Exception
     */
    public function updateGroupedOptionsIndex(\Magento\Framework\DB\Select $select)
    {
        $productIndex = $this->connection->fetchAll($select);
        if (empty($productIndex)) {
            return;
        }

        $groupedIndexData = [];
        $groupedOptions = $this->getGroupedOptions();
        foreach ($productIndex as $key => $productIndexData) {
            $optionValue = $productIndexData['value'];
            if (isset($groupedOptions[$optionValue])) {
                foreach ($groupedOptions[$optionValue] as $groupedOptionId) {
                    $groupedIndexRow = $productIndexData;
                    $groupedIndexRow['value'] = $groupedOptionId;
                    $groupedIndexData[] = $groupedIndexRow;
                }
            }

            unset($productIndex[$key]); //reduce memory consumption
        }

        $this->groupOptionCollection->getResource()->updateGroupedOptionsIndex($this->indexTable, $groupedIndexData);
    }

    /**
     * @return array
     */
    public function getGroupedOptions() : array
    {
        if ($this->groupedOptions === null) {
            $groupAttributesWithOptions = $this->helper->getGroupsWithOptions();
            $this->groupedOptions = [];

            foreach ($groupAttributesWithOptions as $groupId => $value) {
                foreach ($value['options'] as $option) {
                    $this->groupedOptions[$option][] = $this->helper->getFakeKey($groupId);
                }
            }
        }

        return $this->groupedOptions;
    }
}
