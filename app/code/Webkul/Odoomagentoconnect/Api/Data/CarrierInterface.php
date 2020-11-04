<?php
namespace Webkul\Odoomagentoconnect\Api\Data;

/**
 * Webkul Odoomagentoconnect Carrier Interface
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

interface CarrierInterface
{
   /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const CARRIER_NAME    = 'carrier_name';
    const CARRIER_CODE    = 'carrier_code';
    const ODOO_ID         = 'odoo_id';
    const CREATED_BY      = 'created_by';
    const CREATED_AT      = 'created_at';

    /**
     * Get Carrier Name
     *
     * @return string|null
     */
    public function getCarrierName();

    /**
     * Get Carrier Code
     *
     * @return string|null
     */
    public function getCarrierCode();

    /**
     * Get odooId
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
     * Set carrierName
     *
     * @api
     * @param string $carrierName
     * @return $this
     */
    public function setCarrierName($carrierName);

    /**
     * Set carrierCode
     *
     * @api
     * @param string $carrierCode
     * @return $this
     */
    public function setCarrierCode($carrierCode);

    /**
     * Set odooId
     *
     * @api
     * @param int $odooId
     * @return $this
     */
    public function setOdooId($odooId);

    /**
     * Set createdBy
     *
     * @api
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy);
}
