<?php
/**
 * Webkul Odoomagentoconnect Order Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\Data\OrderInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Order extends \Magento\Framework\Model\AbstractModel implements OrderInterface, IdentityInterface
{
    protected $_interfaceAttributes = [
   
    OrderInterface::MAGENTO_ORDER,
    OrderInterface::MAGENTO_ID,
    OrderInterface::ODOO_ID,
    OrderInterface::ODOO_LINE_ID,
    OrderInterface::ODOO_CUSTOMER_ID,
    OrderInterface::ODOO_ORDER,
    OrderInterface::CREATED_BY,
    ];
    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'odoomagentoconnect_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'odoomagentoconnect_order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'odoomagentoconnect_order';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Odoomagentoconnect\Model\ResourceModel\Order');
    }
    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get the magento_order.
     *
     * @api
     * @return string|null
     */

    public function getMagentoOrder()
    {
        return $this->getData(self::MAGENTO_ORDER);
    }

    /**
     * Get the magento_id.
     *
     * @api
     * @return int|null
     */

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Get the odoo_id.
     *
     * @api
     * @return int|null
     */

    public function getOdooId()
    {
        return $this->getData(self::ODOO_ID);
    }

    /**
     * Get the odoo_line_id.
     *
     * @api
     * @return int|null
     */

    public function getOdooLineId()
    {
        return $this->getData(self::ODOO_LINE_ID);
    }

    /**
     * Get the odoo_customer_id.
     *
     * @api
     * @return int|null
     */

    public function getOdooCustomerId()
    {
        return $this->getData(self::ODOO_CUSTOMER_ID);
    }

    /**
     * Get the odoo_order.
     *
     * @api
     * @return string|null
     */

    public function getOdooOrder()
    {
        return $this->getData(self::ODOO_ORDER);
    }
    /**
     * Get the created_by.
     *
     * @api
     * @return string|null
     */

    public function getCreatedBy()
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * Set magentoOrder
     *
     * @api
     * @param string $magentoOrder
     * @return $this
     */
    public function setMagentoOrder($magentoOrder)
    {
        return $this->setData(self::MAGENTO_ORDER, $magentoOrder);
    }

    /**
     * Set magentoId
     *
     * @api
     * @param int $magentoId
     * @return $this
     */

    public function setMagentoId($magentoId)
    {
        return $this->setData(self::MAGENTO_ID, $magentoId);
    }

    /**
     * Set odooId
     *
     * @api
     * @param int $odooId
     * @return $this
     */
    public function setOdooId($odooId)
    {
        return $this->setData(self::ODOO_ID, $odooId);
    }

    /**
     * Set odooLineId
     *
     * @api
     * @param int $odooLineId
     * @return $this
     */
    public function setOdooLineId($odooLineId)
    {
        return $this->setData(self::ODOO_LINE_ID, $odooLineId);
    }

    /**
     * Set odooCustomerId
     *
     * @api
     * @param int $odooCustomerId
     * @return $this
     */
    public function setOdooCustomerId($odooCustomerId)
    {
        return $this->setData(self::ODOO_CUSTOMER_ID, $odooCustomerId);
    }

    /**
     * Set odooOrder
     *
     * @api
     * @param string $odooOrder
     * @return $this
     */
    public function setOdooOrder($odooOrder)
    {
        return $this->setData(self::ODOO_ORDER, $odooOrder);
    }

    /**
     * Set createdBy
     *
     * @api
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }
}
