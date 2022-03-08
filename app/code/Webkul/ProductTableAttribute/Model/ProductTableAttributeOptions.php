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
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ProductTableAttributeOptions extends AbstractModel implements ProductTableAttributeOptionsInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_table_columns_value';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_table_columns_value';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_table_columns_value';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttributeOptions::class);
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get OptionId.
     *
     * @return int
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * Set OptionId.
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * Get ColumnName.
     *
     * @return varchar
     */
    public function getColumnName()
    {
        return $this->getData(self::COLUMN_NAME);
    }

    /**
     * Set ColumnName.
     */
    public function setColumnName($columnName)
    {
        return $this->setData(self::COLUMN_NAME, $columnName);
    }

    /**
     * Get StoreId.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set StoreId.
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}
