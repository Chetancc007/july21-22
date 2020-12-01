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

namespace Mageplaza\SaveCart\Api;

/**
 * Interface SaveProductRepositoryInterface
 * @package Mageplaza\SaveCart\Api
 */
interface SaveProductRepositoryInterface
{
    /**
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\SaveCart\Api\Data\ProductSearchResultInterface
     */
    public function getList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param int $customerId
     * @param int $cartId
     *
     * @return bool
     */
    public function restore($customerId, $cartId): bool;

    /**
     * @param int $customerId
     * @param string $token
     *
     * @return bool
     */
    public function delete($customerId, $token): bool;

    /**
     * @param int $cartId
     *
     * @return bool
     */
    public function share($cartId): bool;

    /**
     * @param int $customerId
     * @param int $cartId
     *
     * @return mixed
     */
    public function save($customerId, $cartId): bool;
}
