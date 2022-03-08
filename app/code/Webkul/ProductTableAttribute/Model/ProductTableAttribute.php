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

namespace Webkul\ProductTableAttribute\Model;

use Webkul\ProductTableAttribute\Api\Data\ProductTableAttributeInterface;
use Magento\Framework\DataObject\IdentityInterface;

class ProductTableAttribute extends \Magento\Framework\Model\AbstractModel implements ProductTableAttributeInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_table_columns';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_table_columns';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_table_columns';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\ProductTableAttribute\Model\ResourceModel\ProductTableAttribute::class);
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get AttributeId.
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Set AttributeId.
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }
}
