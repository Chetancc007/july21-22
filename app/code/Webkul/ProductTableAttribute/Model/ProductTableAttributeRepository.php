<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductTableAttribute\Model;

use Webkul\ProductTableAttribute\Api\Data\ProductTableAttributeInterface;
use Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttribute\Collection;
use Webkul\ProductTableAttribute\Api\ProductTableAttributeRepositoryInterface;
use Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttribute\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductTableAttributeRepository implements ProductTableAttributeRepositoryInterface
{
    /**
     * resource model
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get collection by attributeId
     * @param  int $attributeId
     * @return object
     */
    public function getCollectionByAttributeId($attributeId)
    {
        $columnsCollection = $this->collectionFactory->create()
                                  ->addFieldToFilter('attribute_id', $attributeId);
        
        return $columnsCollection;
    }
}
