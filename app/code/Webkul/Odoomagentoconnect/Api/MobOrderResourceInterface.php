<?php

namespace Webkul\Odoomagentoconnect\Api;

/**
 * @api
 */
interface MobOrderResourceInterface
{
    /**
     * Create order invoice
     *
     * @param string $orderId
     * @param mixed $itemData
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function orderInvoice($orderId, $itemData = []);

    /**
     * Create order shipment
     *
     * @param string $orderId
     * @param mixed $itemData
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function orderShipment($orderId, $itemData = []);

    /**
     * Cancel order
     *
     * @param string $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function orderCancel($orderId);
}
