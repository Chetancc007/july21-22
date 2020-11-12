<?php
namespace Webkul\Odoomagentoconnect\Api\Data;

/**
 * Webkul Odoomagentoconnect Order Interface
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

interface OrderInterface
{
   /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const MAGENTO_ORDER    = 'magento_order';
    const MAGENTO_ID    = 'magento_id';
    const ODOO_ID    = 'odoo_id';
    const ODOO_LINE_ID    = 'odoo_line_id';
    const ODOO_CUSTOMER_ID    = 'odoo_customer_id';
    const ODOO_ORDER    = 'odoo_order';
    const CREATED_BY    = 'created_by';
    const CREATED_AT    = 'created_at';

    /**
     * Get magentoOrder
     *
     * @return string|null
     */
    public function getMagentoOrder();

    /**
     * Get the magentoId.
     *
     * @api
     * @return int|null
     */
    public function getMagentoId();

    /**
     * Get odooId
     *
     * @return int|null
     */
    public function getOdooId();

    /**
     * Get odooLineId
     *
     * @return int|null
     */
    public function getOdooLineId();

    /**
     * Get odooCustomerId
     *
     * @return int|null
     */
    public function getOdooCustomerId();

    /**
     * Get odooOrder
     *
     * @return string|null
     */
    public function getOdooOrder();

    /**
     * Get Created By
     *
     * @return string|null
     */
    public function getCreatedBy();

    /**
     * Set magentoOrder
     *
     * @api
     * @param string $magentoOrder
     * @return $this
     */
    public function setMagentoOrder($magentoOrder);

    /**
     * Set magentoId
     *
     * @api
     * @param int $magentoId
     * @return $this
     */
    public function setMagentoId($magentoId);

    /**
     * Set odooId
     *
     * @api
     * @param int $odooId
     * @return $this
     */
    public function setOdooId($odooId);

    /**
     * Set odooLineId
     *
     * @api
     * @param int $odooLineId
     * @return $this
     */
    public function setOdooLineId($odooLineId);

    /**
     * Set odooCustomerId
     *
     * @api
     * @param int $odooCustomerId
     * @return $this
     */
    public function setOdooCustomerId($odooCustomerId);

    /**
     * Set odooOrder
     *
     * @api
     * @param string $odooOrder
     * @return $this
     */
    public function setOdooOrder($odooOrder);

    /**
     * Set createdBy
     *
     * @api
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy);
}
