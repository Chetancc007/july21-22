<?php
/**
 * Webkul MobOrderResource Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */


namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\MobOrderResourceInterface;

/**
 * Defines the implementation class of the mob order service contract.
 */
class MobOrderResource implements MobOrderResourceInterface
{
    
    /**
     * @var MobFactory
     */
    protected $mobOrderResourceFactory;

    /**
     * Constructor.
     *
     * @param MobFactory
     */
    public function __construct(
        MobOrderResourceFactory $mobOrderResourceFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Model\Service\InvoiceService $invoiceRepository,
        \Magento\Framework\DB\Transaction $invoiceTransaction,
        \Magento\Sales\Model\Convert\Order $shipmentRepository,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
    
        $this->mobOrderResourceFactory = $mobOrderResourceFactory;
        $this->_objectManager = $objectManager;
        $this->_orderInterface = $orderInterface;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_trackFactory = $trackFactory;
        $this->_invoiceTransaction = $invoiceTransaction;
        $this->_shipmentRepository = $shipmentRepository;
        $this->shipmentSender = $shipmentSender;
        $this->invoiceSender = $invoiceSender;
    }

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
    public function orderInvoice($incrementId, $itemData = [])
    {
        $order = $this->_orderInterface->loadByIncrementId($incrementId);
        if ($order->canInvoice()) {
            $invoice = $this->_invoiceRepository->prepareInvoice($order);

            if($invoice->canCapture())
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);

            $invoice->addComment(
                __('Invoice created from Odoo.'),
                false,
                false
            );

            $invoice->register();

            $invoice->getOrder()->setIsInProcess(true);

            $this->_invoiceTransaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            )->save();

            // send invoice emails
            try {
                // if (isset($itemData['send_email'])) {
                    $this->invoiceSender->send($invoice);
                // }
            } catch (\Exception $e) {
                $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
                $helper->addError("$incrementId >> Invoice email is not sent.");
            }
        }
        return true;
    }

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
    public function orderShipment($incrementId, $itemData = [])
    {
        $order = $this->_orderInterface->loadByIncrementId($incrementId);
        $shipmentId = 0;
        $isNotify = false;
        if (isset($itemData['send_email'])) {
            $isNotify = $itemData['send_email'];
            unset($itemData['send_email']);
        }
        if ($order->canShip()) {
            $shipment = $this->_shipmentRepository->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = 0;
                if ($itemData) {
                    $sku = $orderItem->getSku();
                    $prodType = $orderItem->getProductType();
                    if ($prodType == 'bundle') {
                        foreach($itemData as $key => $value ) {
                            if (strpos($sku, $key) !== false) {
                                $qtyShipped = $itemData[$key];
                            }
                            
                        }
                    } else if (isset($itemData[$sku])) {
                        $qtyShipped = $itemData[$sku];
                    }
                } else {
                    $qtyShipped = $orderItem->getQtyToShip();
                }
                if ($qtyShipped) {
                    $shipmentItem = $this->_shipmentRepository->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                }
            }

            $shipment->addComment(
                __('Shipment created from Odoo.'),
                false,
                false
            );

            $shipment->register();

            $shipment->getOrder()->setIsInProcess(true);
            if (isset($itemData['tracking_data'])) {
                $trackingData = $itemData['tracking_data'];
                if ($trackingData) {
                    $track = $this->_trackFactory->create()->addData($trackingData);
                    $shipment->addTrack($track);
                }
            }
            try {
                $this->_invoiceTransaction->addObject(
                    $shipment
                )->addObject(
                    $shipment->getOrder()
                )->save();

                $shipmentId = $shipment->getId();
            } catch (\Exception $e) {
                return false;
            }

            // send shipment emails
            try {
                if ($isNotify) {
                    $this->shipmentSender->send($shipment);
                }
            } catch (\Exception $e) {
                $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
                $helper->addError("$incrementId >> Shipment email is not sent.");
            }
        }
        return $shipmentId;
    }

    /**
     * Cancel order
     *
     * @param string $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function orderCancel($incrementId)
    {
        $order = $this->_orderInterface->loadByIncrementId($incrementId);
        try {
            $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
            $helper->getSession()->setOperationFrom('odoo');
            $order->cancel()->save();
        }
        catch (\Exception $e) {
            return false;
        }
        $order->addStatusHistoryComment(
            __('Order Cancelled from Odoo.')
        )
        ->setIsCustomerNotified(false)
        ->save();
        return true;
    }
}
