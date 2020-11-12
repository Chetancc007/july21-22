<?php
namespace Webkul\Odoomagentoconnect\Api\Data;

/**
 * Webkul Odoomagentoconnect Customer Interface
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

interface CustomerInterface
{
    /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const MAGENTO_ID    = 'magento_id';
    const ADDRESS_ID    = 'address_id';
    const ODOO_ID    = 'odoo_id';
    const CREATED_BY    = 'created_by';
    const CREATED_AT    = 'created_at';
    const NEED_SYNC    = 'need_sync';

    /**
     * Get the magento_id.
     *
     * @api
     * @return int|null
     */
    public function getMagentoId();

    /**
     * Get the address_id.
     *
     * @api
     * @return int|null
     */
    public function getAddressId();

    /**
     * Get odoo_id
     *
     * @return int|null
     */
    public function getOdooId();

    /**
     * Get Created By
     *
     * @return string|null
     */
    public function getCreatedBy();

    /**
     * Get need_sync
     *
     * @return string|null
     */
    public function getNeedSync();

    /**
     * Set magento_id
     *
     * @api
     * @param int $magento_id
     * @return $this
     */
    public function setMagentoId($magentoId);

    /**
     * Set address_id
     *
     * @api
     * @param int $address_id
     * @return $this
     */
    public function setAddressId($addressId);

    /**
     * Set odoo_id
     *
     * @api
     * @param int $odoo_id
     * @return $this
     */
    public function setOdooId($odooId);

    /**
     * Set created_by
     *
     * @api
     * @param string $created_by
     * @return $this
     */
    public function setCreatedBy($createdBy);

    /**
     * Set need_sync
     *
     * @api
     * @param string $need_sync
     * @return $this
     */
    public function setNeedSync($needSync);
}
