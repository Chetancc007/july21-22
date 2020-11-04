<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrdersGrid\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const TABLE_NAME_EXTENDED_GRID = 'mageworx_ordersgrid_grid';

    const XML_PATH_ENABLED            = 'mageworx_order_management/order_grid/main/enabled';
    const XML_PATH_SYNC_ORDER_GRID    = 'mageworx_order_management/order_grid/main/sync_order_grid';
    const XML_PATH_SYNC_ORDER_COUNT   = 'mageworx_order_management/order_grid/main/sync_order_count';
    const XML_PATH_SYNC_ORDER_BY_CRON = 'mageworx_order_management/order_grid/main/cron_sync_status';

    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Check is enabled modules features
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Is synchronization using cron enabled (disable life-time synchronization)
     *
     * @return bool
     */
    public function isSyncByCronEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SYNC_ORDER_BY_CRON);
    }

    /**
     * Get enable permanent order item removal
     *
     * @return bool
     */
    public function getSyncOrderGrid(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SYNC_ORDER_GRID);
    }

    /**
     * Get count of the sync orders
     *
     * @return int
     */
    public function getSyncOrdersCount()
    {
        return intval($this->scopeConfig->getValue(self::XML_PATH_SYNC_ORDER_COUNT));
    }
}
