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

namespace Mageplaza\SaveCart\Observer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollection;

/**
 * Class Delete
 * @package Mageplaza\SaveCart\Observer
 */
class Delete implements ObserverInterface
{
    /**
     * @var CartItemCollection
     */
    private $cartItemCollection;

    /**
     * Delete constructor.
     *
     * @param CartItemCollection $cartItemCollection
     */
    public function __construct(CartItemCollection $cartItemCollection)
    {
        $this->cartItemCollection = $cartItemCollection;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Collection $cartItemCollection */
        $cartItemCollection = $this->cartItemCollection->create();
        /** @var AdapterInterface $connection */
        $connection = $cartItemCollection->getConnection();

        $cartItemTable = $cartItemCollection->getMainTable();
        $cartTable     = $cartItemCollection->getTable('mageplaza_saved_cart');

        $select            = $connection->select()->from($cartItemTable, 'cart_id')->group('cart_id');
        $cartIds           = $connection->fetchCol($select);
        $conditionToDelete = 'cart_id NOT IN (' . implode(',', $cartIds) . ')';

        $connection->delete($cartTable, $conditionToDelete);
    }
}
