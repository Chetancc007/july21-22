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

namespace Webkul\ProductTableAttribute\Model\ResourceModel;

use Magento\Catalog\Model\ProductFactory;

/**
 * ProductTableAttribute ProductTableAttribute mysql resource.
 */
class ProductTableAttribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var int
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('wk_table_columns', 'entity_id');
    }
}
