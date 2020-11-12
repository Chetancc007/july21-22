<?php
/**
 * Webkul Odoomagentoconnect Set Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\Data\SetInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Set extends \Magento\Framework\Model\AbstractModel implements SetInterface, IdentityInterface
{

    protected $_interfaceAttributes = [
   
    SetInterface::NAME,
    SetInterface::MAGENTO_ID,
    SetInterface::ODOO_ID,
    SetInterface::CREATED_BY,
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
    const CACHE_TAG = 'odoomagentoconnect_set';

    /**
     * @var string
     */
    protected $_cacheTag = 'odoomagentoconnect_set';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'odoomagentoconnect_set';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Odoomagentoconnect\Model\ResourceModel\Set');
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
     * Get the name.
     *
     * @api
     * @return string|null
     */

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get the magento set id.
     *
     * @api
     * @return int|null
     */

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Get the odoo set id.
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
     * Set name
     *
     * @api
     * @param string $setName
     * @return $this
     */
    public function setName($setName)
    {
        return $this->setData(self::NAME, $setName);
    }

    /**
     * Set magento id
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
     * Set odoo set id
     *
     * @api
     * @param int $odooSetId
     * @return $this
     */
    public function setOdooId($odooSetId)
    {
        return $this->setData(self::ODOO_ID, $odooSetId);
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
