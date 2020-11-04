<?php
/**
 * Webkul Odoomagentoconnect Option Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\Data\OptionInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Option extends \Magento\Framework\Model\AbstractModel implements OptionInterface, IdentityInterface
{

    protected $_interfaceAttributes = [
   
    OptionInterface::NAME,
    OptionInterface::MAGENTO_ID,
    OptionInterface::ODOO_ID,
    OptionInterface::CREATED_BY,
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
    const CACHE_TAG = 'odoomagentoconnect_option';

    /**
     * @var string
     */
    protected $_cacheTag = 'odoomagentoconnect_option';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'odoomagentoconnect_option';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Odoomagentoconnect\Model\ResourceModel\Option');
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
     * Get the magento option id.
     *
     * @api
     * @return int|null
     */

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Get the odoo option id.
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
     * Set option name
     *
     * @api
     * @param string $optionName
     * @return $this
     */
    public function setName($optionName)
    {
        return $this->setData(self::NAME, $optionName);
    }

    /**
     * Set magento option id
     *
     * @api
     * @param int $magentoOptionId
     * @return $this
     */

    public function setMagentoId($magentoOptionId)
    {
        return $this->setData(self::MAGENTO_ID, $magentoOptionId);
    }

    /**
     * Set odoo option id
     *
     * @api
     * @param int $odoo_id
     * @return $this
     */
    public function setOdooId($odooOptionId)
    {
        return $this->setData(self::ODOO_ID, $odooOptionId);
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
