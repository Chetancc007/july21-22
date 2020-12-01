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
 * Interface ProductInterface
 * @package Mageplaza\SaveCart\Api\Data
 */
interface ProductInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setId($value);

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
    public function getSubtotalConverted();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSubtotalConverted($value);

    /**
     * @return string
     */
    public function getImageUrl();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setImageUrl($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setProductId($value);

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
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
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
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);
}
