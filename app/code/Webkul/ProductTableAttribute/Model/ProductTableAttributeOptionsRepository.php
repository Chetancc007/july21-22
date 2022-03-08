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

use Webkul\ProductTableAttribute\Api\Data\ProductTableAttributeOptionsInterface;
use Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttributeOptions\Collection;
use Webkul\ProductTableAttribute\Api\ProductTableAttributeOptionsRepositoryInterface;
use Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttributeOptions\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductTableAttributeOptionsRepository implements ProductTableAttributeOptionsRepositoryInterface
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
     * get collection by columnId
     * @param  int $columnId
     * @return object
     */
    public function getCollectionByColumnId($columnId)
    {
        $columnsCollection = $this->collectionFactory->create()
                                  ->addFieldToFilter('column_id', $columnId);
        return $columnsCollection;
    }
}
