<?php

namespace Nits\StockRegistry\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

/**
 * Class StockRegistry
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistry extends \Magento\CatalogInventory\Model\StockRegistry 
{
    
    /**
     * @param string $productSku
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productFactory->create();
        if($productSku == trim($productSku) && strpos($productSku, ' ') !== false) 
        {
            $productSku1=explode(' ',$productSku)[0];
            $productId = $product->getIdBySku($productSku1);
            if(!$productId){
                $productId = $product->getIdBySku($productSku);
            }
        }
        else{
            $productId = $product->getIdBySku($productSku);
        }
        if (!$productId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'The Product with the "%1" SKU doesn\'t exist.',
                    $productSku
                )
            );
        }
        return $productId;
    }
}
