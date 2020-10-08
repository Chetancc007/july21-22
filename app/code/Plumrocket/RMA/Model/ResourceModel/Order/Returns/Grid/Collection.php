<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Plumrocket\RMA\Model\ResourceModel\Order\Returns\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionTrait;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    use CollectionTrait;

    /**
     * Map with relations of tables columns and aliases for filters
     *
     * @var array
     */
    protected $columnsMap = [
        'entity_id'     => 'main_table.entity_id',
        'increment_id'  => 'main_table.increment_id',
        'created_at'    => 'main_table.created_at',
        'status'        => 'main_table.status',

        'order_date'    => 'o.updated_at',
        'store_id'      => 'o.store_id',
    ];

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'plumrocket_rma_returns',
        $resourceModel = '\Plumrocket\RMA\Model\ResourceModel\Returns'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        foreach ($this->columnsMap as $filter => $alias) {
            $this->addFilterToMap($filter, $alias);
        }
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addOrderData()
            ->addCustomerData()
            ->addAdminData();
    }
}
