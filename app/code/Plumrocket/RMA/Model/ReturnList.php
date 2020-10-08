<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\RMA\Api\Data\ReturnSearchResultsInterfaceFactory;
use Plumrocket\RMA\Api\ReturnListInterface;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory;

class ReturnList implements ReturnListInterface
{
    /**
     * @var ResourceModel\Returns\CollectionFactory
     */
    private $returnCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ReturnSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ResourceModel\Returns\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var ResourceModel\Reason\CollectionFactory
     */
    private $reasonCollectionFactory;

    /**
     * @var ResourceModel\Condition\CollectionFactory
     */
    private $conditionCollectionFactory;

    /**
     * @var ResourceModel\Resolution\CollectionFactory
     */
    private $resolutionCollectionFactory;

    /**
     * @var ReturnsHelper
     */
    private $returnHelper;

    /**
     * ReturnList constructor.
     * @param CollectionFactory $returnCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ReturnSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Returns\Item\CollectionFactory $itemCollectionFactory
     * @param ResourceModel\Reason\CollectionFactory $reasonCollectionFactory
     * @param ResourceModel\Condition\CollectionFactory $conditionCollectionFactory
     * @param ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory
     * @param ReturnsHelper $returnHelper
     */
    public function __construct(
        CollectionFactory $returnCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ReturnSearchResultsInterfaceFactory $searchResultsFactory,
        ResourceModel\Returns\Item\CollectionFactory $itemCollectionFactory,
        ResourceModel\Reason\CollectionFactory $reasonCollectionFactory,
        ResourceModel\Condition\CollectionFactory $conditionCollectionFactory,
        ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory,
        ReturnsHelper $returnHelper
    ) {
        $this->returnCollectionFactory = $returnCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->reasonCollectionFactory = $reasonCollectionFactory;
        $this->conditionCollectionFactory = $conditionCollectionFactory;
        $this->resolutionCollectionFactory = $resolutionCollectionFactory;
        $this->returnHelper = $returnHelper;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Returns\Collection $collection */
        $collection = $this->returnCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Plumrocket\RMA\Api\Data\ReturnSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $returnItems = $this->getReturnItems($collection->getAllIds());

        /** @var \Plumrocket\RMA\Model\ResourceModel\Reason\Collection $reasonCollection */
        $reasonCollection = $this->reasonCollectionFactory->create();

        /** @var \Plumrocket\RMA\Model\ResourceModel\Condition\Collection $conditionCollection */
        $conditionCollection = $this->conditionCollectionFactory->create();

        /** @var \Plumrocket\RMA\Model\ResourceModel\Resolution\Collection $resolutionCollection */
        $resolutionCollection = $this->resolutionCollectionFactory->create();
        $items = [];

        foreach ($returnItems as $item) {
            $itemData = $item->getData();
            $reason = $itemData['reason_id'] ? $reasonCollection->getItemById($itemData['reason_id']) : null;
            $condition = $itemData['condition_id']
                ? $conditionCollection->getItemById($itemData['condition_id'])
                : null;
            $resolution = $itemData['resolution_id']
                ? $resolutionCollection->getItemById($itemData['resolution_id'])
                : null;
            $itemData['reason'] = $reason ? $reason->getTitle() : '';
            $itemData['condition'] = $condition ? $condition->getTitle() : '';
            $itemData['resolution'] = $resolution ? $resolution->getTitle() : '';
            unset($itemData['reason_id'], $itemData['condition_id'], $itemData['resolution_id']);
            $items[$item->getParentId()][] = $itemData;
        }

        foreach ($collection->getItems() as $return) {
            if ($label = $return->getShippingLabel()) {
                $return->setShippingLabel($this->returnHelper->getFileUrl($return, $label, true));
            }

            $return->setItems($items[$return->getId()]);
        }

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param array $returnIds
     * @return ResourceModel\Returns\Item\Collection
     */
    private function getReturnItems(array $returnIds)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Returns\Item\Collection $itemCollection */
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('parent_id', ['in' => $returnIds]);
        return $itemCollection;
    }
}
