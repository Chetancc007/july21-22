<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Model\ResourceModel\Product;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Eav\Model\Config;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\SaveCart\Model\Product;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Mageplaza\SaveCart\Model\ResourceModel\Product
 */
class Collection extends AbstractCollection
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param MetadataPool $metadataPool
     * @param Config $eavConfig
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        MetadataPool $metadataPool,
        Config $eavConfig,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->metadataPool = $metadataPool;
        $this->eavConfig    = $eavConfig;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Product::class, \Mageplaza\SaveCart\Model\ResourceModel\Product::class);
    }

    /**
     * @param $customerId
     *
     * @return Collection
     * @throws LocalizedException
     * @throws Exception
     */
    public function filterCollection($customerId)
    {
        $idField  = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $attrName = $this->eavConfig->getAttribute(CatalogProduct::ENTITY, 'name');
        $attrId   = '';
        if ($attrName) {
            $attrId = $attrName->getId();
        }
        $this->addFieldToFilter('customer_id', $customerId);
        $select = $this->getSelect();
        $select->joinRight(
            ['pce' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = pce.entity_id',
            'sku'
        )->joinLeft(
            ['cpev' => $this->getTable('catalog_product_entity_varchar')],
            "main_table.product_id = cpev.{$idField} AND cpev.attribute_id = {$attrId}",
            'value'
        );

        return $this;
    }
}
