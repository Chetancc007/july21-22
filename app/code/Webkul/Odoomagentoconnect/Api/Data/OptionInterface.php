<?php
namespace Webkul\Odoomagentoconnect\Api\Data;

/**
 * Webkul Odoomagentoconnect Option Interface
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

interface OptionInterface
{
    /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const NAME          = 'name';
    const MAGENTO_ID    = 'magento_id';
    const ODOO_ID       = 'odoo_id';
    const CREATED_BY    = 'created_by';
    const CREATED_AT    = 'created_at';

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get the magento_id.
     *
     * @api
     * @return int|null
     */
    public function getMagentoId();

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
     * Set name
     *
     * @api
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set magento_id
     *
     * @api
     * @param int $magento_id
     * @return $this
     */
    public function setMagentoId($magentoId);

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
}
