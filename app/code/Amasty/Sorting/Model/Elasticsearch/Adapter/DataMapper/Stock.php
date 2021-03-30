<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapperInterface;
use Amasty\Sorting\Model\ResourceModel\Inventory;
use Magento\Store\Model\StoreManagerInterface;

class Stock implements DataMapperInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Data $data,
        Inventory $inventory,
        StoreManagerInterface $storeManager
    ) {
        $this->data = $data;
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        if ($this->data->isOutOfStockByQty($storeId)) {
            $currentQty = $this->inventory->getQty(
                $entityIndexData['sku'],
                $this->storeManager->getStore($storeId)->getWebsite()->getCode()
            );
            $value = (int) ($currentQty > $this->data->getQtyOutStock($storeId));
        } else {
            $value = (int) $this->inventory->getStockStatus(
                $entityIndexData['sku'],
                $this->storeManager->getStore($storeId)->getWebsite()->getCode()
            );
        }

        return ['out_of_stock_last' => $value];
    }

    /**
     * @inheritdoc
     */
    public function isAllowed($storeId)
    {
        return $this->data->getOutOfStockLast($storeId);
    }
}
