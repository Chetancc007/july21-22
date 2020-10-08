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
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Ui\DataProvider\Returns;

class ReturnsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Plumrocket\RMA\Model\ResourceModel\Returns\Collection
     */
    protected $collection;

    /**
     * @var Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter
     */
    private $fulltextFilter;

    /**
     * @var Plumrocket\RMA\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\ReportingInterface
     */
    protected $reportingInterface;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $fulltextFilter
     * @param \Plumrocket\RMA\Helper\Data $dataHelper
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\Search\ReportingInterface $reportingInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $fulltextFilter,
        \Plumrocket\RMA\Helper\Data $dataHelper,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\Search\ReportingInterface $reportingInterface,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataHelper = $dataHelper;
        $this->fulltextFilter = $fulltextFilter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reportingInterface = $reportingInterface;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        $filterMap = $this->getFilterMap();
        $storeIds = $this->dataHelper->getStoreIds();

        $collection->addOrderData()
            ->addCustomerData()
            ->addAdminData()
            ->addLastReplyData();

        if (false === $this->isArchive()) {
            $collection->addNotArchiveFilter();
        } else {
            $collection->addArchiveFilter();
        }

        foreach ($filterMap as $filter => $alias) {
            $collection->addFilterToMap($filter, $alias);
        }

        if (!empty($storeIds)) {
            $collection->addFieldToFilter('o.store_id', ['in' => $storeIds]);
        }

        return $collection->toArray();
    }

    /**
     * @return bool|false|int
     */
    public function isArchive()
    {
        return mb_strpos($this->request->getServerValue('HTTP_REFERER'), 'returnsarchive');
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ('fulltext' === $filter->getConditionType()) {
            $this->fulltextFilter->apply($this->getCollection(), $filter);
        } else {
            $filterMap = $this->getFilterMap();

            if (isset($filterMap[$filter->getField()])) {
                $filter->setField($filterMap[$filter->getField()]);
            }

            parent::addFilter($filter);
        }

        $this->searchCriteriaBuilder->addFilter($filter);

        return $this;
    }

    /**
     * @return array
     */
    private function getFilterMap()
    {
        return $filterMap = [
            'increment_id' => 'main_table.increment_id',
            'created_at'   => 'main_table.created_at',
            'order_increment_id' => 'o.increment_id',
            'order_date' => 'o.updated_at',
            'entity_id' => 'main_table.entity_id',
            'customer_name' => 'c.name',
            'reply_at' => 'rm.created_at',
            'status' => 'main_table.status',
            'store_id' => 'o.store_id',
            'manager_name' => new \Zend_Db_Expr('CONCAT(au.`firstname`, " ", au.`lastname`)')
        ];
    }

    /**
     * @return $this
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;
    }

    /**
     * Returns Search result
     *
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function getSearchResult()
    {
        return $this->reportingInterface->search($this->getSearchCriteria());
    }
}
