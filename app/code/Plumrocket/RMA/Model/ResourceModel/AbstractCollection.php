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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection;

/**
 * Abstract collection of RMA
 */
abstract class AbstractCollection extends Collection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->getEntityTypeId()) {
            $this->getSelect()
                ->columns('GROUP_CONCAT(store.store_id) AS store_id')
                ->join(
                    ['store' => $this->getStoreTable()],
                    'store.entity_type_id = ' . $this->getEntityTypeId() . ' AND store.entity_id = main_table.id',
                    []
                );

            $storeId = $this->storeManager->getStore()->getId();
            $labelTable = '(SELECT l.value FROM ' .
                $this->getLabelTable() . ' AS l' .
                ' WHERE l.entity_type_id = ' . $this->getEntityTypeId() . ' AND l.entity_id = main_table.id AND l.store_id = ' . $storeId . ' LIMIT 1) AS label';

            $this->getSelect()->columns(new \Zend_Db_Expr($labelTable));

            $this->addFilterToMap('store_id', 'store.store_id');
            $this->getSelect()->group('main_table.id');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getEntityTypeId()) {
            foreach ($this->_items as $item) {
                $item->setStoreId(array_unique(explode(',', $item->getStoreId())));
            }
        }

        return $this;
    }

    /**
     * Store table
     * @return string
     */
    protected function getStoreTable()
    {
        return $this->getResource()->getTable('plumrocket_rma_store');
    }

    /**
     * Return store label text
     * @return string
     */
    protected function getLabelTable()
    {
        return $this->getResource()->getTable('plumrocket_rma_text');
    }

    /**
     * Retrieve entity type id
     * @return int
     */
    protected function getEntityTypeId()
    {
        return $this->getResource()->getEntityTypeId();
    }

    /**
     * Add active filter
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->addFieldToFilter('status', \Plumrocket\RMA\Model\Config\Source\Status::STATUS_ENABLED);
        return $this;
    }

    /**
     * Add active filter
     * @param int|int[] $ids
     * @return $this
     */
    public function addStoreFilter($ids)
    {
        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        // Add "All stores"
        $ids[] = 0;
        $ids = array_unique($ids);

        // $this->addFieldToFilter('store_id', ['finset' => $ids]);
        $this->addFieldToFilter('store_id', $ids);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'title')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toOptionArray($valueField = 'id', $labelField = 'title', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}
