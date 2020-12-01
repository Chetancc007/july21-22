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
 * Interface ConfigInterface
 * @package Mageplaza\SaveCart\Api\Data
 */
interface ConfigInterface
{
    /**
     * @return int
     */
    public function getEnabled();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setEnabled($value);

    /**
     * @return string
     */
    public function getButtonTittle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setButtonTittle($value);

    /**
     * @return int
     */
    public function getShowButtonGuest();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setShowButtonGuest($value);

    /**
     * @return string
     */
    public function getPageLinkArea();

    /**
     * @param $value
     *
     * @return $this
     */
    public function setPageLinkArea($value);

    /**
     * @return int
     */
    public function getAllowShare();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setAllowShare($value);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param $value
     *
     * @return $this
     */
    public function setIcon($value);

    /**
     * @return string
     */
    public function getIconUrl();

    /**
     * @param $value
     *
     * @return $this
     */
    public function setIconUrl($value);
}
