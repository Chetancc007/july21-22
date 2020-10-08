<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns;

trait CollectionTrait
{
    /**
     * Add order data to collection
     *
     * @return $this
     */
    public function addOrderData()
    {
        $this->getSelect()->join(
            ['o' => $this->getTable('sales_order')],
            'o.entity_id = main_table.order_id',
            [
                'increment_id as order_increment_id',
                // 'GREATEST(COALESCE(o.`created_at`, 0), COALESCE(o.`updated_at`, 0)) as order_date'
                'updated_at as order_date', 'store_id'
            ]
        );

        return $this;
    }

    /**
     * Add customer data to collection
     *
     * @return $this
     */
    public function addCustomerData()
    {
        $this->getSelect()->joinLeft(
            ['c' => $this->getTable('customer_grid_flat')],
            'c.entity_id = o.customer_id',
            ['customer_name' => 'c.name']
        );

        return $this;
    }

    /**
     * Add admin data to collection
     *
     * @return $this
     */
    public function addAdminData()
    {
        $this->getSelect()->joinLeft(
            ['au' => $this->getTable('admin_user')],
            'au.user_id = main_table.manager_id',
            ['COALESCE(CONCAT(au.`firstname`, " ", au.`lastname`), "N/A") as manager_name']
        );

        return $this;
    }

    /**
     * Add data of last reply to collection
     *
     * @return $this
     */
    public function addLastReplyData()
    {
        $messagesTable = '(SELECT * FROM ' .
            $this->getTable('plumrocket_rma_returns_message') .
            ' WHERE is_system = 0 AND is_internal = 0 ORDER BY entity_id DESC)';

        $this->getSelect()->joinLeft(
            ['rm' => new \Zend_Db_Expr($messagesTable)],
            'rm.parent_id = main_table.entity_id',
            ['rm.created_at as reply_at', 'rm.name as reply_name']
        );

        $this->getSelect()->group('main_table.entity_id');

        return $this;
    }
}
