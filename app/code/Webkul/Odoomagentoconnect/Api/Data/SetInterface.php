<?php
namespace Webkul\Odoomagentoconnect\Api\Data;

/**
 * Webkul Odoomagentoconnect Set Interface
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

interface SetInterface
{
    /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const NAME    = 'name';
    const MAGENTO_ID    = 'magento_id';
    const ODOO_ID    = 'odoo_id';
    const CREATED_BY    = 'created_by';
    const CREATED_AT    = 'created_at';

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

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
     * Set createdBy
     *
     * @api
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy);
}
