<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Store\Model\ScopeInterface;

class Configurable
{
    const MAIN_INDEX_TABLE = 'catalog_product_index_price';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $entityIds;

    /**
     * @var string
     */
    protected $productIdLink;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    protected $subject;

    /**
     * @var array
     */
    protected $dimensions;

    /**
     * @var string
     */
    protected $tmpTableSuffix = '_temp';

    /**
     * @var TableResolver
     */
    protected $tableResolver;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        TableResolver $tableResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->resource = $resourceConnection;
        $this->productIdLink = $productResource->getLinkField();
        $this->tableResolver = $tableResolver;
        $this->config = $config;
    }

    /**
     * @param $subject
     * @param $entityIds
     * @return array
     */
    public function beforeExecuteByDimensions($subject, array $dimensions, \Traversable $entityIds)
    {
        $this->subject = $subject;
        $this->dimensions = $dimensions;
        $this->entityIds = iterator_to_array($entityIds);

        return [$dimensions, $entityIds];
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecuteByDimensions($subject, $result)
    {
        $this->addSpecialPriceToConfigurable();

        return $result;
    }

    private function addSpecialPriceToConfigurable(): void
    {
        if (!$this->entityIds && !$this->isOnSaleEnabled()) {
            return;
        }

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(['main_table' => $this->getDataTable()]);

        $select->joinInner(
            ['simple_link' => $this->resource->getTableName('catalog_product_super_link')],
            'simple_link.product_id=main_table.entity_id',
            []
        );
        if ($this->productIdLink == 'row_id') {
            $select->joinInner(
                ['product_link' => $this->resource->getTableName('catalog_product_entity')],
                'simple_link.parent_id=product_link.row_id',
                ['parent_id' => 'product_link.entity_id']
            );
            $select->where('product_link.entity_id IN (?)', $this->entityIds);
        } else {
            $select->columns(['parent_id' => 'simple_link.parent_id']);
            $select->where('simple_link.parent_id IN (?)', $this->entityIds);
        }

        $select->where('main_table.price > main_table.final_price and main_table.final_price > 0');

        $select->group(['simple_link.parent_id', 'main_table.customer_group_id', 'main_table.website_id']);

        $insertData = $connection->fetchAll($select);
        if (!empty($insertData)) {
            foreach ($insertData as &$row) {
                if (isset($row['parent_id'])) {
                    $row['entity_id'] = $row['parent_id'];
                    unset($row['parent_id']);
                }
            }

            $connection->insertOnDuplicate(
                $this->getIdxTable(),
                $insertData,
                ['price', 'final_price']
            );
        }
    }

    public function getDataTable(): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $this->dimensions);
    }

    public function getIdxTable(): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $this->dimensions) . $this->tmpTableSuffix;
    }

    private function isOnSaleEnabled(): bool
    {
        return $this->config->isSetFlag('amshopby/am_on_sale_filter/enabled', ScopeInterface::SCOPE_STORE);
    }
}
