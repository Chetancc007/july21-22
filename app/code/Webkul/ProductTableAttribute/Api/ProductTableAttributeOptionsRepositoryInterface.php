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

namespace Webkul\ProductTableAttribute\Api;

/**
 * @api
 */
interface ProductTableAttributeOptionsRepositoryInterface
{
    /**
     * get collection by columnId
     * @param  int $columnId
     * @return object
     */
    public function getCollectionByColumnId($columnId);
}
