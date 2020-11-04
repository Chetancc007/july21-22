<?php
/**
 * Webkul Odoomagentoconnect Carrier Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\Data\CarrierInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Carrier extends \Magento\Framework\Model\AbstractModel implements CarrierInterface, IdentityInterface
{

    protected $_interfaceAttributes = [
   
    CarrierInterface::CARRIER_NAME,
    CarrierInterface::CARRIER_CODE,
    CarrierInterface::ODOO_ID,
    CarrierInterface::CREATED_BY,
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
    const CACHE_TAG = 'odoomagentoconnect_carrier';

    /**
     * @var string
     */
    protected $_cacheTag = 'odoomagentoconnect_carrier';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'odoomagentoconnect_carrier';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Odoomagentoconnect\Model\ResourceModel\Carrier');
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
     * Get the carrier_name.
     *
     * @api
     * @return string|null
     */

    public function getCarrierName()
    {
        return $this->getData(self::CARRIER_NAME);
    }

    /**
     * Get the carrier_code.
     *
     * @api
     * @return string|null
     */

    public function getCarrierCode()
    {
        return $this->getData(self::CARRIER_CODE);
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
     * Set carrierName
     *
     * @api
     * @param string $carrierName
     * @return $this
     */
    public function setCarrierName($carrierName)
    {
        return $this->setData(self::CARRIER_NAME, $carrierName);
    }

    /**
     * Set carrierCode
     *
     * @api
     * @param string $carrierCode
     * @return $this
     */
    public function setCarrierCode($carrierCode)
    {
        return $this->setData(self::CARRIER_CODE, $carrierCode);
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
