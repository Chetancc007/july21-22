<?php
/**
 * Webkul Odoomagentoconnect Order ResourceModel
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Session $session,
        \Webkul\Odoomagentoconnect\Model\Customer $customerModel,
        Connection $connection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Currency $currencyModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Carrier $carrierMapping,
        \Magento\Catalog\Model\Product $catalogModel,
        \Magento\Sales\Model\Order\Item $orderItemModel,
        \Webkul\Odoomagentoconnect\Model\Product $productModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productMapping,
        \Magento\Tax\Model\Calculation\Rate $taxRateModel,
        \Webkul\Odoomagentoconnect\Model\Tax $taxModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Tax $taxMapping,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateModel,
        \Magento\Sales\Model\ResourceModel\Order\Tax\Item $taxItemModel,
        \Webkul\Odoomagentoconnect\Model\Payment $paymentModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Payment $paymentMapping,
        $resourcePrefix = null
    ) {
        $this->_connection = $connection;
        $this->_catalogModel = $catalogModel;
        $this->_currencyModel = $currencyModel;
        $this->_customerModel = $customerModel;
        $this->_customerMapping = $customerMapping;
        $this->_carrierMapping = $carrierMapping;
        $this->_scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
        $this->_orderItemModel = $orderItemModel;
        $this->_productModel = $productModel;
        $this->_productMapping = $productMapping;
        $this->_taxRateModel = $taxRateModel;
        $this->_taxModel = $taxModel;
        $this->_taxMapping = $taxMapping;
        $this->_taxItemModel = $taxItemModel;
        $this->_configModel = $configModel;
        $this->_templateModel = $templateModel;
        $this->_paymentModel = $paymentModel;
        $this->_paymentMapping = $paymentMapping;
        $this->_session = $session;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $resourcePrefix);
    }

    public function exportOrder($thisOrder, $quote=false)
    {
        $odooId = 0;
        $helper = $this->_connection;
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $currencyCode = $thisOrder->getOrderCurrencyCode();
        $pricelistId = $this->_currencyModel
                            ->syncCurrency($currencyCode);
        if (!$pricelistId) {
            $error = "Export Error, Order ".$incrementId." >> No Pricelist found for currency ".$currencyCode." at odoo end.";
            $helper->addError($error);
            return 0;
        }
        $erpAddressArray = $this->getErpOrderAddresses($thisOrder);

        if (count(array_filter($erpAddressArray)) == 3) {
            $lineids = '';
            $partnerId = $erpAddressArray[0];
            $odooOrder = $this->createOdooOrder($thisOrder, $pricelistId, $erpAddressArray);
            if (!$odooOrder){
                return $odooId;
            }
            $odooId = $odooOrder[0];
            $orderName = $odooOrder[1];
            if ($odooId) {
                $lineids = $this->createOdooOrderLine($thisOrder, $odooId, $quote);
                $includesTax = $this->_scopeConfig->getValue('tax/calculation/price_includes_tax');
                $this->_eventManager
                        ->dispatch(
                            'odoo_order_sync_after',
                            ['mage_order_id' => $mageOrderId, 'odoo_order_id' => $odooId]
                        );
                if ($thisOrder->getShippingDescription()) {
                    $shippingLineId = $this->createOdooOrderShippingLine($thisOrder, $odooId);
                    $lineids .= $shippingLineId;
                }
                /* Creating Order Mapping At both End..*/
                $this->createOrderMapping($thisOrder, $odooId, $orderName, $partnerId, $lineids);

                $draftState = $this->_scopeConfig->getValue('odoomagentoconnect/order_settings/draft_order');
                $autoInvoice = $this->_scopeConfig->getValue('odoomagentoconnect/order_settings/invoice_order');
                $autoShipment = $this->_scopeConfig->getValue('odoomagentoconnect/order_settings/ship_order');
                if (!$draftState) {
                    $this->confirmOdooOrder($odooId);
                }
                if ($thisOrder->hasInvoices() && $autoInvoice==1) {
                    $this->invoiceOdooOrder($thisOrder, $odooId, false);
                }

                if ($thisOrder->hasShipments() && $autoShipment == 1) {
                    $this->deliverOdooOrder($thisOrder, $odooId);
                }
                return $odooId;
            } else {
                return $odooId;
            }
        } else {
            return $odooId;
        }
    }

    public function createOdooOrder($thisOrder, $pricelistId, $erpAddressArray)
    {
        $odooOrder = [];
        $extraFieldArray = [];
        $odooOrderId = 0;
        $partnerId = $erpAddressArray[0];
        $partnerInvoiceId = $erpAddressArray[1];
        $partnerShippingId = $erpAddressArray[2];
        $mageOrderId = $thisOrder->getId();
        $this->_session->setExtraFieldArray($extraFieldArray);
        $this->_eventManager->dispatch('odoo_order_sync_before', ['mage_order_id' => $mageOrderId]);

        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $extraFieldArray = $this->_session->getExtraFieldArray();
        $incrementId = $thisOrder->getIncrementId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        //custom code added here
        // $warehouseId = $this->_session->getErpWarehouse();
        if ($thisOrder->getShippingMethod()) {
            if ($thisOrder->getShippingMethod() == "amstorepick_amstorepick1") {
                $warehouseId = 3;
            }elseif ($thisOrder->getShippingMethod() == "amstorepick_amstorepick2") {
                $warehouseId = 1;
            }
        }else{
            $warehouseId = $this->_session->getErpWarehouse();
        }
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($thisOrder->getShippingMethod(),true));
        $logger->info(print_r($warehouseId,true));
        $orderArray =  [
                    'partner_id'=>new xmlrpcval($partnerId, "int"),
                    'partner_invoice_id'=>new xmlrpcval($partnerInvoiceId, "int"),
                    'partner_shipping_id'=>new xmlrpcval($partnerShippingId, "int"),
                    'pricelist_id'=>new xmlrpcval($pricelistId, "int"),
                    'date_order'=>new xmlrpcval($thisOrder->getCreatedAt(), "string"),
                    'origin'=>new xmlrpcval($incrementId, "string"),
                    'warehouse_id'=>new xmlrpcval($warehouseId, "int"),
                    'ecommerce_channel'=>new xmlrpcval('magento', "string"),
                    'ecommerce_order_id'=>new xmlrpcval($thisOrder->getId(), "int"),
                ];
        $allowSequence = $this->_scopeConfig->getValue('odoomagentoconnect/order_settings/order_name');
        if ($allowSequence) {
            $orderArray['name'] = new xmlrpcval($incrementId, "string");
        }
        /* Adding Shipping Information*/
        if ($thisOrder->getShippingMethod()) {
            $shippingMethod = $thisOrder->getShippingMethod();
            $shippingCode = explode('_', $shippingMethod);
            if ($shippingCode) {
                $shippingCode = $shippingCode[0];
                $erpCarrierId =  $this->_carrierMapping
                                    ->checkSpecificCarrier($shippingCode);
                if ($erpCarrierId > 0) {
                    $orderArray['carrier_id'] = new xmlrpcval($erpCarrierId, "int");
                }
            }
        }
        /* Adding Payment Information*/
        $paymentMethod = $thisOrder->getPayment()->getMethodInstance()->getTitle();
        if ($paymentMethod) {
            $paymentInfo = 'Payment Information:- '.$paymentMethod;
            $orderArray['note'] = new xmlrpcval($paymentInfo, "string");
        }
        /* Adding Extra Fields*/
        foreach ($extraFieldArray as $field => $value) {
            $orderArray[$field]= $value;
        }
        $context = ['context' => new xmlrpcval($context, "struct")];
        $orderArray = [new xmlrpcval($orderArray, "struct")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("wk.skeleton", "string"));
        $msg->addParam(new xmlrpcval("create_order", "string"));
        $msg->addParam(new xmlrpcval($orderArray, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultcode()) {
            $error = "Export Error, Order ".$incrementId." >>".$resp->faultString();
            $helper->addError($error);
        } else {
            $response = $resp->value();
            $status = $response->me["struct"]["status"]->me["boolean"];
            if(!$status){
                $statusMessage = $response->me["struct"]["status_message"]->me["string"];
                $error = "Export Error, Order ".$incrementId.", Error:-".$statusMessage;
                $helper->addError($error);
            } else {
                $odooOrderId = $response->me["struct"]["order_id"]->me["int"];
                $odooOrderName = $response->me["struct"]["order_name"]->me["string"];
                array_push($odooOrder, $odooOrderId);
                array_push($odooOrder, $odooOrderName);
            }
        }
        return $odooOrder;
    }

    public function createOdooOrderLine($thisOrder, $odooId, $thisQuote=false)
    {
        $erpProductId = 0;
        $lineIds = '';
        $items = $thisOrder->getAllItems();
        if (!$items) {
            return false;
        }
        /* Odoo Conncetion Data*/
        $helper = $this->_connection;
        $userId = $helper->getSession()->getUserId();
        $context = $helper->getOdooContext();
        $client = $helper->getClientConnect();
        
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $write = $resource->getConnection('default');
        $shippingIncludesTax = $this->_scopeConfig->getValue('tax/calculation/shipping_includes_tax');
        $priceIncludesTax = $this->_scopeConfig->getValue('tax/calculation/price_includes_tax');

        foreach ($items as $item) {
            $itemId = $item->getId();
            $itemDesc = $item->getName();
            $productId = $item->getProductId();
            $product = $this->_objectManager
                ->create('\Magento\Catalog\Model\Product')
                ->load($productId);
            if ($priceIncludesTax) {
                $basePrice = $item->getPriceInclTax();
            } else {
                $basePrice = $item->getPrice();
            }
            $itemTaxPercent = $item->getTaxPercent();
            $itemType = $item->getProductType();
            if ($itemType == 'configurable') {
                continue;
            }
            if ($itemType == 'bundle') {
                $priceType = $product->getPriceType();
                if (!$priceType) {
                    $basePrice = 0;
                }
            }
            $discountAmount = 0;
            $discountAmount = $item->getDiscountAmount();
            $parent = false;
            if ($item->getParentItemId() != null) {
                $parentId = $item->getParentItemId();
                $parent = $this->_orderItemModel->load($parentId);
                if ($parent->getProductType() == 'configurable') {
                    if ($priceIncludesTax) {
                        $basePrice = $parent->getPriceInclTax();
                    } else {
                        $basePrice = $parent->getPrice();
                    }
                    $itemTaxPercent = $parent->getTaxPercent();

                    $discountAmount = $parent->getDiscountAmount();
                }

                $itemId = $parentId;
            }
            /*
                Fetching Odoo Product Id
            */
            $orderedQty = $item->getQtyOrdered();
            $mappingcollection = $this->_productModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$productId]);
            if ($mappingcollection->getSize() > 0) {
                foreach ($mappingcollection as $map) {
                    $erpProductId = $map->getOdooId();
                }
            } else {
                $erpProductId = $this->syncProduct($productId);
            }
            if (!$erpProductId) {
                $error = "Odoo Product Not Found For Order ".$incrementId." Product id = ".$productId;
                $helper->addError($error);
                continue;
            }
            $orderLineArray =  [
                        'order_id'=>new xmlrpcval($odooId, "int"),
                        'product_id'=>new xmlrpcval($erpProductId, "int"),
                        'price_unit'=>new xmlrpcval($basePrice, "string"),
                        'product_uom_qty'=>new xmlrpcval($orderedQty, "string"),
                        'name'=>new xmlrpcval(urlencode($itemDesc), "string")
                    ];
        /**************** checking tax applicable & getting mage tax id per item ************/
            if ($itemTaxPercent > 0) {
                $itemTaxes = [];
                if ($thisQuote) {
                    $qItems = $thisQuote->getAllItems();
                    $oQtItemId = $item->getQuoteItemId();
                    if ($parent) {
                        $oQtItemId = $parent->getQuoteItemId();
                    }
                    foreach ($qItems as $qItem) {
                        $qItemId = $qItem->getItemId();
                        $appliedTaxes = $qItem['applied_taxes'];
                        if ($qItemId == $oQtItemId && $appliedTaxes) {
                            foreach ($appliedTaxes as $appliedTaxe) {
                                $taxCode = $appliedTaxe['id'];
                                $erpTaxId = $this->getOdooTaxId($taxCode);
                                if ($erpTaxId) {
                                    array_push($itemTaxes, new xmlrpcval($erpTaxId, "int"));
                                }
                            }
                            break;
                        }
                    }
                } else {
                    $tableName = $resource->getTableName('sales_order_tax_item');
                    $taxItems = $write->query("SELECT * FROM ".$tableName." WHERE item_id='".$itemId."'")->fetchAll();
                
                    if ($taxItems) {
                        foreach ($taxItems as $itemTax) {
                            $erpTaxId = 0;
                            $tableName = $resource->getTableName('sales_order_tax');
                            $select = "SELECT code FROM ".$tableName;
                            $queryTax = $select." WHERE tax_id='".$itemTax['tax_id']."' AND order_id= '".$mageOrderId."'";
                            $orderTax = $write->query($queryTax);
                            $taxCodeResult = $orderTax->fetch();
                            
                            $taxCode = $taxCodeResult["code"];
                            $erpTaxId = $this->getOdooTaxId($taxCode);

                            /******************** getting erp tax id ******************/
                            if ($erpTaxId) {
                                array_push($itemTaxes, new xmlrpcval($erpTaxId, "int"));
                            }
                        }
                    } else {
                        $tableName = $resource->getTableName('sales_order_tax');
                        $orderTax = $write->query("SELECT code FROM ".$tableName." WHERE order_id= '".$mageOrderId."'");
                        $taxCodeResult = $orderTax->fetch();
                        if ($taxCodeResult) {
                            $taxCode = $taxCodeResult["code"];
                            $erpTaxId = $this->getOdooTaxId($taxCode);
                            if ($erpTaxId) {
                                array_push($itemTaxes, new xmlrpcval($erpTaxId, "int"));
                            }
                        }
                    }
                }

                $orderLineArray['tax_id'] = new xmlrpcval($itemTaxes, "array");
            } else {
                $itemTaxes = [];
                $taxRateData = $this->_taxRateModel
                                    ->getCollection()->addFieldToFilter('rate', 0)
                                    ->getData();
                if (count($taxRateData)) {
                    foreach ($taxRateData as $map) {
                        $taxMapData = $this->_taxModel
                                            ->load($map['tax_calculation_rate_id'], "magento_id")
                                            ->getData();
                        if (count($taxMapData)) {
                            $erpTaxId = $taxMapData['odoo_id'];
                            if ($erpTaxId) {
                                array_push($itemTaxes, new xmlrpcval($erpTaxId, "int"));
                            }
                            $orderLineArray['tax_id'] = new xmlrpcval($itemTaxes, "array");
                            break;
                        }
                    }
                }
            }

            $extraFieldArray = [];
            $this->_session->setExtraFieldArray($extraFieldArray);
            $this->_eventManager->dispatch('odoo_orderline_sync_before', ['item' => $item]);
            $extraFieldArray = $this->_session->getExtraFieldArray();
            foreach ($extraFieldArray as $field => $value) {
                $orderArray[$field]= $value;
            }
            $context = ['context' => new xmlrpcval($context, "struct")];
            $orderLineArray = [new xmlrpcval($orderLineArray, "struct")];
            $lineCreate = new xmlrpcmsg('execute_kw');
            $lineCreate->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $lineCreate->addParam(new xmlrpcval($userId, "int"));
            $lineCreate->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $lineCreate->addParam(new xmlrpcval("wk.skeleton", "string"));
            $lineCreate->addParam(new xmlrpcval("create_sale_order_line", "string"));
            $lineCreate->addParam(new xmlrpcval($orderLineArray, "array"));
            $lineCreate->addParam(new xmlrpcval($context, "struct"));
            $lineResp = $client->send($lineCreate);
            if ($lineResp->faultCode()) {
                $faultString = $lineResp->faultString();
                $error = "Item Sync Error, Order ".$incrementId.", Product id = ".$productId.'Error:-'.$faultString;
                $helper->addError($error);
                continue;
            }
            $status = $lineResp->value()->me["struct"]["status"]->me["boolean"];
            if(!$status){
                $status_message = $lineResp->value()->me["struct"]["status_message"]->me["string"];
                $error = "Item Sync Error, Order ".$incrementId.", Product id = ".$productId.'Error:-'.$status_message;
                $helper->addError($error);
                continue;
            } else {
                $lineId = $lineResp->value()->me["struct"]["order_line_id"]->me["int"];
                $lineIds .= $lineId.",";
                if ($discountAmount != 0) {
                    $taxes = '';
                    if (isset($orderLineArray['tax_id'])) {
                        $taxes = $orderLineArray['tax_id'];
                    }
                    $productName = $product->getName();
                    $voucherLineId = $this->createOdooOrderLineVoucherLine(
                        $thisOrder,
                        $discountAmount,
                        $odooId,
                        $taxes,
                        $productName
                    );
                    $lineIds .= $voucherLineId;
                }
            }
        }
        return $lineIds;
    }
    
    public function syncProduct($productId)
    {
        $odooProductId = 0;
        $parentIds = $this->_configModel
                            ->getParentIdsByChild($productId);
        if ($parentIds) {
            $configurableId = $parentIds[0];

            $response = $this->_templateModel
                            ->exportSpecificConfigurable($configurableId);
            if ($response['odoo_id'] > 0) {
                $erpTemplateId = $response['odoo_id'];
                $this->_templateModel
                    ->syncConfigChildProducts($configurableId, $erpTemplateId);
            }
            $mappingcollection = $this->_productModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$productId]);
            if ($mappingcollection) {
                foreach ($mappingcollection as $mapping) {
                    return $mapping->getOdooId();
                }
            }
        } else {
            $response = $this->_productMapping
                            ->createSpecificProduct($productId);
            if ($response['odoo_id'] > 0) {
                return $response['odoo_id'];
            }
        }
        return $odooProductId;
    }

    public function getOdooTaxId($taxCode)
    {
        $erpTaxId = 0;
        if ($taxCode) {
            $collection = $this->_taxRateModel
                                ->getCollection()
                                ->addFieldToFilter('code', ['eq'=>$taxCode])
                                ->getAllIds();

            foreach ($collection as $rateId) {
                $mappingcollection = $this->_taxModel
                                            ->getCollection()
                                            ->addFieldToFilter('magento_id', ['eq'=>$rateId]);
                                            
                if (count($mappingcollection)) {
                    foreach ($mappingcollection as $mapping) {
                        $erpTaxId = $mapping->getOdooId();
                    }
                } else {
                    $response = $this->_taxMapping
                                    ->createSpecificTax($rateId);

                    if ($response['odoo_id']) {
                        $erpTaxId = $response['odoo_id'];
                    }
                }
            }
        }
        return $erpTaxId;
    }

    public function getTaxId($mageOrderId)
    {
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $write = $resource->getConnection('default');
        $tableName = $resource->getTableName('sales_order_tax');
        $itemTaxes = [];
        $orderTax = $write->query("SELECT code FROM ".$tableName." WHERE order_id= '".$mageOrderId."'");
        $taxCodeResult = $orderTax->fetch();
        if ($taxCodeResult) {
            $taxCode = $taxCodeResult["code"];
            $erpTaxId = $this->getOdooTaxId($taxCode);
            if ($erpTaxId) {
                array_push($itemTaxes, new xmlrpcval($erpTaxId, "int"));
            }
        }
        return $itemTaxes;
    }

    public function createOdooOrderLineVoucherLine($thisOrder, $discountAmount, $odooId, $taxes, $productName)
    {
        $voucherLineId = 0;
        
        $discountAmount = -(float)$discountAmount;

        $name = "Discount";
        $description = "Discount on ".$productName;
        $voucherLineArray =  [
                'order_id'=>new xmlrpcval($odooId, "int"),
                'name'=>new xmlrpcval($name, "string"),
                'description'=>new xmlrpcval($description, "string"),
                'price_unit'=>new xmlrpcval($discountAmount, "double")
            ];
        if($taxes){
            $voucherLineArray['tax_id'] = $taxes;
        }
        $voucherLineId = $this->syncExtraOdooOrderLine($thisOrder, $voucherLineArray, $description);

        return $voucherLineId;
    }

    public function createOdooOrderVoucherLine($thisOrder, $odooId)
    {
        $voucherLineId = 0;
        $incrementId = $thisOrder->getIncrementId();
        $helper = $this->_connection;
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        
        $discountAmount = $thisOrder->getDiscountAmount();

        $description = "Discount";
        $name = "Discount";
        $couponDesc = $thisOrder->getDiscountDescription();
        if ($couponDesc) {
            $description .= "-".$couponDesc;
        }
        $code = $thisOrder->getCouponCode();
        if ($code) {
            $name = "Voucher";
            $description .= " Coupon Code:-".$code;
        }
        
        $voucherLineArray =  [
                'order_id'=>new xmlrpcval($odooId, "int"),
                'name'=>new xmlrpcval($name, "string"),
                'description'=>new xmlrpcval($description, "string"),
                'price_unit'=>new xmlrpcval($discountAmount, "double")
            ];
        $mageOrderId = $thisOrder->getId();
        $voucherLineId = $this->syncExtraOdooOrderLine($thisOrder, $voucherLineArray, $description);

        return $voucherLineId;
    }

    public function createOdooOrderShippingLine($thisOrder, $odooId)
    {
        $mageOrderId = $thisOrder->getId();
        $shippingDescription = urlencode($thisOrder->getShippingDescription());
        $shippingLineArray =  [
                'order_id'=>new xmlrpcval($odooId, "int"),
                'name'=>new xmlrpcval('Shipping', "string"),
                'description'=>new xmlrpcval($shippingDescription, "string")
            ];
        $shippingIncludesTax = $this->_scopeConfig->getValue('tax/calculation/shipping_includes_tax');
        if ($shippingIncludesTax) {
            $shippingLineArray['price_unit'] = new xmlrpcval($thisOrder->getShippingInclTax(), "double");
        } else {
            $shippingLineArray['price_unit'] = new xmlrpcval($thisOrder->getShippingAmount(), "double");
        }
        if ($thisOrder->getShippingTaxAmount()>0) {
            $shippingTaxes = $this->getMagentoTaxId($mageOrderId, 'shipping');
            if ($shippingTaxes) {
                $shippingLineArray['tax_id'] = new xmlrpcval($shippingTaxes, "array");
            }
        }

        $shippingLineId = $this->syncExtraOdooOrderLine($thisOrder, $shippingLineArray, $shippingDescription);

        return $shippingLineId;
    }

    public function getMagentoTaxId($orderId, $taxType)
    {
        $taxItems = $this->_taxItemModel
                         ->getTaxItemsByOrderId($orderId);
        $odooTaxes = [];
        foreach ($taxItems as $value) {
            if (isset($value['taxable_item_type'])) {
                if ($value['taxable_item_type'] == $taxType) {
                    if (isset($value['code'])) {
                        $erpTaxId = $this->getOdooTaxId($value['code']);
                        array_push($odooTaxes, new xmlrpcval($erpTaxId, "int"));
                    }
                }
            }
        }
        return $odooTaxes;
    }

    public function syncExtraOdooOrderLine($thisOrder, $extraLineArray, $type = "Extra")
    {
        $extraLineId = '';
        $incrementId = $thisOrder->getIncrementId();
        $helper = $this->_connection;
        $userId = $helper->getSession()->getUserId();
        $context = $helper->getOdooContext();
        $client = $helper->getClientConnect();
        $extraLineArray['ecommerce_channel'] = new xmlrpcval("magento", "string");
        $context = ['context' => new xmlrpcval($context, "struct")];
        $extraLineArray = [new xmlrpcval($extraLineArray, "struct")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("wk.skeleton", "string"));
        $msg->addParam(new xmlrpcval("create_order_shipping_and_voucher_line", "string"));
        $msg->addParam(new xmlrpcval($extraLineArray, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultCode()) {
            $error = $type." Line Export Error, For Order ".$incrementId." >>".$resp->faultString();
            $helper->addError($error);
        } else {
            $odooStatus = $resp->value()->me["struct"]["status"]->me["boolean"];
            if (!$odooStatus) {
                $statusMsg = $resp->value()->me["struct"]["status_message"]->me["string"];
                $error = "Line Export Error, Order ".$incrementId." >>".$statusMsg;
                $helper->addError($error);
                return $extraLineId;
            }
            $extraLineId = $resp->value()->me["struct"]["order_line_id"]->me["int"];
            $extraLineId = $extraLineId.",";
        }
        return $extraLineId;
    }

    public function createOrderMapping($thisOrder, $odooId, $orderName, $partnerId, $lineids = '')
    {
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $helper = $this->_connection;
        $mappingData = [
                'magento_order'=>$incrementId,
                'odoo_id'=>$odooId,
                'odoo_customer_id'=>$partnerId,
                'magento_id'=>$mageOrderId,
                'odoo_line_id'=>rtrim($lineids, ","),
                'odoo_order'=>$orderName,
                'created_by'=>$helper::$mageUser,
            ];
        $this->createMapping($mappingData);
    }

    public function confirmOdooOrder($odooId)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $context = ['context' => new xmlrpcval($context, "struct")];
        $odooId = [new xmlrpcval($odooId, "int")];
        $method = new xmlrpcmsg('execute_kw');
        $method->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $method->addParam(new xmlrpcval($userId, "int"));
        $method->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $method->addParam(new xmlrpcval("wk.skeleton", "string"));
        $method->addParam(new xmlrpcval("confirm_odoo_order", "string"));
        $method->addParam(new xmlrpcval($odooId, "array"));
        $method->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($method);
        if ($resp->faultcode()) {
            $error = "Odoo Order ".$odooId." Error During Order Confirm >>".$resp->faultString();
            $helper->addError($error);
        }
    }

    public function invoiceOdooOrder($thisOrder, $odooId, $invoiceNumber)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        
        $invoiceDate = $thisOrder->getUpdatedAt();
        $incrementId = $thisOrder->getIncrementId();
        $invoice = $thisOrder->getInvoiceCollection()
            ->addFieldToFilter('order_id', $thisOrder->getEntityId())
            ->getData();
        foreach ($invoice as $inv) {
            $invoiceDate = $inv['created_at'];
            if (!$invoiceNumber) {
                $invoiceNumber = $inv['increment_id'];
            }
            break;
        }
        $context['invoice_date'] = new xmlrpcval($invoiceDate, "string");
        $context = ['context' => new xmlrpcval($context, "struct")];
        $invoice_array = [new xmlrpcval($odooId, "int"), new xmlrpcval($invoiceNumber, "string")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("wk.skeleton", "string"));
        $msg->addParam(new xmlrpcval("create_order_invoice", "string"));
        $msg->addParam(new xmlrpcval($invoice_array, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);

        if ($resp->faultcode()) {
            $error = "Sync Error, Order ".$incrementId." During Invoice >>".$resp->faultString();
            $helper->addError($error);
            return false;
        } else {
            $status = $resp->value()->me["struct"]["status"]->me["boolean"];
            if(!$status){
                $status_message = $resp->value()->me["struct"]["status_message"]->me["string"];
                $error = "Sync Error, Order ".$incrementId." During Invoice >>".$status_message;
                $this->_connection->addError($error);
                return false;
            } else {
                $invoiceId = $resp->value()->me["struct"]["invoice_id"]->me["int"];
                if ($invoiceId > 0) {
                    $context = $helper->getOdooContext();
                    /**
                    ******** Odoo Order Payment *************
                    */
                    $paymentMethod = $thisOrder->getPayment()->getMethodInstance()->getTitle();
                    
                    $journalId = $this->getOdooPaymentMethod($paymentMethod);
                    $paymentArray = [
                                'order_id'=>new xmlrpcval($odooId, "int"),
                                'journal_id'=>new xmlrpcval($journalId, "int")
                            ];

                    $context = ['context' => new xmlrpcval($context, "struct")];
                    $paymentArray = [new xmlrpcval($paymentArray, "struct")];
                    $payment = new xmlrpcmsg('execute_kw');
                    $payment->addParam(new xmlrpcval($helper::$odooDb, "string"));
                    $payment->addParam(new xmlrpcval($userId, "int"));
                    $payment->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                    $payment->addParam(new xmlrpcval("wk.skeleton", "string"));
                    $payment->addParam(new xmlrpcval("set_order_paid", "string"));
                    $payment->addParam(new xmlrpcval($paymentArray, "array"));
                    $payment->addParam(new xmlrpcval($context, "struct"));
                    $payResp = $client->send($payment);
                    if ($payResp->faultcode()) {
                        $error = "Sync Error, Order ".$incrementId." During Payment >>".$payResp->faultString();
                        $helper->addError($error);
                        return false;
                    } else {
                        $status = $payResp->value()->me["struct"]["status"]->me["boolean"];
                        if(!$status){
                            $status_message = $payResp->value()->me["struct"]["status_message"]->me["string"];
                            $error = "Sync Error, Order ".$incrementId." During Payment >>".$status_message;
                            $helper->addError($error);
                            return false;
                        } else {
                            return true;
                        }
                    }
                } elseif ($invoiceId == 0) {
                    $error = "Sync Error, Order ".$incrementId." During Invoice >> Not able to create invoice at odoo.";
                    $this->_connection->addError($error);
                }
            }
        }
        return true;
    }

    public function deliverOdooOrder($thisOrder, $erpOrderId, $shipmentObj = false)
    {
        $shipmentNo = false;
        $tracknums = false;
        $trackCarrier = false;
        $helper = $this->_connection;
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $incrementId = $thisOrder->getIncrementId();
        if ($shipmentObj) {
            $shipmentNo = $shipmentObj->getId();
            foreach ($shipmentObj->getAllTracks() as $tracknum) {
                $tracknums=$tracknum->getTrackNumber();
                $trackCarrier=$tracknum->getCarrierCode();
                break;
            }
        } else {
            $shipment = $thisOrder->getShipmentsCollection();
            foreach ($shipment as $ship) {
                $shipmentNo = $ship->getId();
                foreach ($ship->getAllTracks() as $tracknum) {
                    $tracknums=$tracknum->getTrackNumber();
                    $trackCarrier=$tracknum->getCarrierCode();
                    break;
                }
                break;
            }
        }
        $context['ship_number'] = new xmlrpcval($shipmentNo, "string");
        if($trackCarrier && $tracknums){
            $context['carrier_tracking_ref'] = new xmlrpcval($tracknums, "string");
            $context['carrier_code'] = new xmlrpcval($trackCarrier, "string");
        }
        $context = ['context' => new xmlrpcval($context, "struct")];
        $erpOrderId = [new xmlrpcval($erpOrderId, "int")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("wk.skeleton", "string"));
        $msg->addParam(new xmlrpcval("set_order_shipped", "string"));
        $msg->addParam(new xmlrpcval($erpOrderId, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultcode()) {
            $error = "Sync Error, Order ".$incrementId." During Shipment >> ".$resp->faultString();
            $helper->addError($error);
            return false;
        } else {
            $response = $resp->value();
            $status = $response->me["struct"]["status"]->me["boolean"];
            if (!$status) {
                $statusMessage = $response->me["struct"]["status_message"]->me["string"];
                $error = "Sync Error, Order ".$incrementId." During Shipment >> ".$statusMessage;
                $helper->addError($error);
                return false;
            }
        }
        return true;
    }

    public function getOdooPaymentMethod($paymentMethod)
    {
        $mappingcollection = $this->_paymentModel
                                    ->getCollection()
                                    ->addFieldToFilter('magento_id', $paymentMethod);
        if (count($mappingcollection) > 0) {
            foreach ($mappingcollection as $map) {
                return $map->getOdooId();
            }
        } else {
            $response = $this->_paymentMapping
                             ->syncSpecificPayment($paymentMethod);
            $erpPaymentId = $response['odoo_id'];
            return $erpPaymentId;
        }
    }

    public function getErpOrderAddresses($thisOrder)
    {
        $partnerId = 0;
        $partnerInvoiceId = 0;
        $partnerShippingId = 0;
        $billingAddresssId = 0;
        $shippingAddressId = 0;
        $storeId = $thisOrder->getStoreId();
        $customerId = $thisOrder->getCustomerId();
        $billing = $thisOrder->getBillingAddress();
        $shipping = $thisOrder->getShippingAddress();
        $magerpsync = $this->_customerMapping;
        if ($billing) {
            $billing->setEmail($thisOrder->getCustomerEmail());
        }
        if ($shipping) {
            $shipping->setEmail($thisOrder->getCustomerEmail());
        }
        $customerArray =  [
            'name'=>new xmlrpcval(urlencode($thisOrder->getCustomerName()), "string"),
            'email'=>new xmlrpcval(urlencode($thisOrder->getCustomerEmail()), "string"),
            'is_company'=>new xmlrpcval(false, "boolean"),
        ];
        if ($thisOrder->getCustomerIsGuest() == 1) {
            $customerId = 0;
            $customerArray['name'] = new xmlrpcval(urlencode($billing->getName()), "string");
        }
        if ($customerId > 0) {
            $billingAddresssId =  $billing->getCustomerAddressId();
            if ($shipping) {
                $shippingAddressId = $shipping->getCustomerAddressId();
            }
            $mappingcollection = $this->_customerModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$customerId])
                                        ->addFieldToFilter('address_id', ['eq'=>"customer"]);
            if (count($mappingcollection)>0) {
                foreach ($mappingcollection as $map) {
                    $partnerId = $map->getOdooId();
                    break;
                }
            }
        }
        if (!$partnerId) {
            $partnerId = $magerpsync->odooCustomerCreate($customerArray, $customerId, 'customer', $storeId);
        }
        if ($partnerId){
            $partnerInvoiceId = $this->createErpAddress(
                $billing, 
                $partnerId, 
                $customerId, 
                $billingAddresssId, 
                $storeId
            );
            $isDifferent = $this->checkAddresses($thisOrder);
            if ($isDifferent == true && $shipping) {
                $partnerShippingId = $this->createErpAddress(
                    $shipping,
                    $partnerId,
                    $customerId,
                    $shippingAddressId,
                    $storeId
                );
                
            } else {
                $partnerShippingId = $partnerInvoiceId;
            }
        }

        return [$partnerId, $partnerInvoiceId, $partnerShippingId];
    }

    public function createErpAddress($flatAddress, $parentId, $mageCustomerId, $mageAddressId, $storeId = 0)
    {
        $flag = false;
        $erpCusId = 0;
        $addressArray = [];
        $addressArray = $this->customerAddressArray($flatAddress);

        if ($mageAddressId > 0) {
            $addresscollection =  $this->_customerModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$mageCustomerId])
                                        ->addFieldToFilter('address_id', ['eq'=>$mageAddressId]);

            if (count($addresscollection)>0) {
                foreach ($addresscollection as $add) {
                    $mapId = $add->getEntityId();
                    $erpCusId = $add->getOdooId();
                }
            } else {
                $flag = true;
            }
        } else {
            $flag = true;
        }
        if ($flag == true) {
            if ($addressArray) {
                $addressArray['parent_id'] = new xmlrpcval($parentId, "int");
                $erpCusId = $this->_customerMapping
                                ->odooCustomerCreate($addressArray, $mageCustomerId, $mageAddressId, $storeId);
            }
        }
        return $erpCusId;
    }

    public function customerAddressArray($flatAddress)
    {
        $type = '';
        $addressArray = [];
        if ($flatAddress['address_type'] == 'billing') {
            $type = 'invoice';
        }
        if ($flatAddress['address_type'] == 'shipping') {
            $type = 'delivery';
        }
        $streets = $flatAddress->getStreet();
        if (count($streets)>1) {
            $street = urlencode($streets[0]);
            $street2 = urlencode($streets[1]);
        } else {
            $street = urlencode($streets[0]);
            $street2 = urlencode('');
        }
        $name = urlencode($flatAddress->getName());
        $company = urlencode($flatAddress->getCompany());
        $email = urlencode($flatAddress->getEmail());
        $city = urlencode($flatAddress->getCity());
        $region = urlencode($flatAddress->getRegion());

        $addressArray =  [
            'name'=>new xmlrpcval($name, "string"),
            'street'=>new xmlrpcval($street, "string"),
            'street2'=>new xmlrpcval($street2, "string"),
            'city'=>new xmlrpcval($city, "string"),
            'email'=>new xmlrpcval($email, "string"),
            'zip'=>new xmlrpcval($flatAddress->getPostcode(), "string"),
            'phone'=>new xmlrpcval($flatAddress->getTelephone(), "string"),
            'country_code'=>new xmlrpcval($flatAddress->getCountryId(), "string"),
            'region'=>new xmlrpcval($region, "string"),
            'wk_company'=>new xmlrpcval($company, "string"),
            'customer_rank'=>new xmlrpcval(false, "boolean"),
            'type'=>new xmlrpcval($type, "string")
        ];
        return $addressArray;
    }

    public function checkAddresses($thisOrder)
    {
        $flag = false;
        if ($thisOrder->getShippingAddressId() && $thisOrder->getBillingAddressId()) {
            $s = $thisOrder->getShippingAddress();
            $b = $thisOrder->getBillingAddress();
            if ($s['street'] != $b['street']) {
                $flag = true;
            }
            if ($s['postcode'] != $b['postcode']) {
                $flag = true;
            }
            if ($s['city'] != $b['city']) {
                $flag = true;
            }
            if ($s['region'] != $b['region']) {
                $flag = true;
            }
            if ($s['country_id'] != $b['country_id']) {
                $flag = true;
            }
            if ($s['firstname'] != $b['firstname']) {
                $flag = true;
            }
        }
        return $flag;
    }

    public function createMapping($data)
    {
        $createdBy = 'Magento';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $carrierModel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Order');
        $carrierModel->setData($data);
        $carrierModel->save();
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_order', 'entity_id');
    }
}
