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
 * Interface CartInterface
 * @package Mageplaza\SaveCart\Api\Data
 */
interface CartInterface
{
    /**
     * @return int
     */
    public function getCartId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCartId($value);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $value
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
    public function getCartName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCartName($value);

    /**
     * @return string
     */
    public function setDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function getDescription($value);

    /**
     * @return string
     */
    public function getCustomerId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return string
     */
    public function getShareUrl();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShareUrl($value);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setToken($value);

    /**
     * @return \Mageplaza\SaveCart\Api\Data\CartItemInterface[]
     */
    public function getItems();

    /**
     * @param \Mageplaza\SaveCart\Api\Data\CartItemInterface[] $value
     *
     * @return $this
     */
    public function setItems($value);
}
