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

namespace Webkul\ProductTableAttribute\Api\Data;

interface ProductTableAttributeOptionsInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const OPTION_ID = 'option_id';
    const COLUMN_NAME = 'column_name';
    const STORE_ID = 'store_id';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get OptionId.
     * @return int
     */
    public function getOptionId();

    /**
     * set OptionId.
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get columnName.
     * @return string
     */
    public function getColumnName();

    /**
     * set columnName.
     * @return $this
     */
    public function setColumnName($columnName);
    
        /**
         * Get StoreId.
         * @return int
         */
    public function getStoreId();

    /**
     * set StoreId.
     * @return $this
     */
    public function setStoreId($storeId);
}
