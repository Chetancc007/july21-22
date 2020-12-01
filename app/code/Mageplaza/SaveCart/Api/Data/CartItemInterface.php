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

/**
 * Interface CartItemInterface
 * @package Mageplaza\SaveCart\Api\Data
 */
interface CartItemInterface
{
    /**
     * Returns the item ID.
     *
     * @return int|null Item ID. Otherwise, null.
     */
    public function getCartItemId();

    /**
     * Sets the item ID.
     *
     * @param int $itemId
     *
     * @return $this
     */
    public function setCartItemId($itemId);

    /**
     * Returns the product SKU.
     *
     * @return string|null Product SKU. Otherwise, null.
     */
    public function getProductId();

    /**
     * Sets the product SKU.
     *
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getProductName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setProductName($value);

    /**
     * Returns the product quantity.
     *
     * @return float Product quantity.
     */
    public function getQty();

    /**
     * Sets the product quantity.
     *
     * @param float $qty
     *
     * @return $this
     */
    public function setQty($qty);

    /**
     * Returns Cart ID.
     *
     * @return string
     */
    public function getCartId();

    /**
     * @param int $cartId
     *
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getPrice();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPrice($value);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setImage($value);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSku($value);

    /**
     * @return string
     */
    public function getSubtotalConverted();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSubtotalConverted($value);
}
