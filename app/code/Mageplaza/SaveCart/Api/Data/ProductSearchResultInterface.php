<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProductSearchResultInterface
 * @package Mageplaza\SaveCart\Api\Data
 */
interface ProductSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \Mageplaza\SaveCart\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * @param \Mageplaza\SaveCart\Api\Data\ProductInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null);
}
