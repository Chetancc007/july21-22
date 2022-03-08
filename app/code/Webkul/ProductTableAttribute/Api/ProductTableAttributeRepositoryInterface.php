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
interface ProductTableAttributeRepositoryInterface
{
    /**
     * get collection by attributeId
     * @param  int $attributeId
     * @return object
     */
    public function getCollectionByAttributeId($attributeId);
}
