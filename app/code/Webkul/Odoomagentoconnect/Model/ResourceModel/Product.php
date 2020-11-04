<?php
/**
 * Webkul Odoomagentoconnect Payment ResourceModel
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

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $productManager,
        Connection $connection,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_productManager = $productManager;
        $this->_objectManager = $objectManager;
        $this->_connection = $connection;
    }

    public function getMageProductArray()
    {
        $product = [];
        $product[''] ='--Select Magento Product--';
        $collection = $this->_productManager
                            ->getCollection()
                            ->addAttributeToFilter('type_id', ['neq' => 'configurable'])
                            ->addAttributeToSelect('name');
        foreach ($collection as $productObj) {
            $productId = $productObj->getId();
            $productName = $productObj->getName();
            $productSku = $productObj->getSku();
            if($productSku)
                $productName = "[$productSku] $productName";
            $product[$productId] = $productName;
        }
        return $product;
    }

    public function getErpProductArray()
    {
        $product = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();
        if ($userId > 0) {
            $product[''] ='--Select Odoo Product--';
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array ( 
                new xmlrpcval(
                    array(
                        new xmlrpcval('attribute_value_ids', "string"), 
                        new xmlrpcval('=', "string"), 
                        new xmlrpcval(false, "boolean")
                    ), "array"
                ),
            );
            $productSearch = new xmlrpcmsg('execute');
            $productSearch->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $productSearch->addParam(new xmlrpcval($userId, "int"));
            $productSearch->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $productSearch->addParam(new xmlrpcval("product.product", "string"));
            $productSearch->addParam(new xmlrpcval("search", "string"));
            $productSearch->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($productSearch);
            if ($resp0->faultCode()) {
                array_push($product, ['label' => $helper->__('Not Available(Error in Fetching)'), 'value' => '']);
                return $product;
            } else {
                $val = $resp0->value()->me['array'];
                $productGet = new xmlrpcmsg('execute');
                $productGet->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $productGet->addParam(new xmlrpcval($userId, "int"));
                $productGet->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $productGet->addParam(new xmlrpcval("product.product", "string"));
                $productGet->addParam(new xmlrpcval("name_get", "string"));
                $productGet->addParam(new xmlrpcval($val, "array"));
                $productGet->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($productGet);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($product, ['label' => $msg, 'value' => '']);
                    return $product;
                } else {
                    $valueArray=$resp1->value()->scalarval();
                    $count = count($valueArray);
                    for ($x=0; $x<$count; $x++) {
                        $id = $valueArray[$x]->me['array'][0]->me['int'];
                        $name = $valueArray[$x]->me['array'][1]->me['string'];
                        $product[$id] = $name;
                    }
                }
            }
            return $product;
        } else {
            $product['error'] = $errorMessage;
            return $product;
        }
    }

    public function syncSimpleProduct($visibility, $parentIds, $mappingObj, $proId)
    {
        if (!$parentIds && $visibility != 1) {
            if ($mappingObj) {
                $this->updateNormalProduct($mappingObj);
            } else {
                $response = $this->createSpecificProduct($proId);
            }
        }
        return true;
    }

    public function createSpecificProduct($mageId)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $errorMessage = $helper->getSession()->getErrorMessage();

            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $productArray = $this->getProductArray($mageId);
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("product.product", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($productArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Export Error, Product Id ".$mageId." >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $response['error'] = $resp->faultString();
                $helper->addError($error);
            } else {
                $odooId = $resp->value()->me["int"];
                if ($odooId > 0) {
                    $mappingData = [
                                'odoo_id'=>$odooId,
                                'magento_id'=>$mageId,
                                'created_by'=>$helper::$mageUser
                            ];
                    $this->mappingerp($mappingData);
                    $response['odoo_id'] = $odooId;
                    $this->createInventoryAtOdoo($mageId, $odooId);
                    $dispatchData = ['product' => $mageId, 'erp_product' => $odooId, 'type' => 'product'];
                    $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                }
            }
        }
        return $response;
    }

    public function getProductCategoryArray($categoryIds)
    {
        $odooCategories = [];

        $helper = $this->_connection;
        $helper->getSocketConnect();
        foreach ($categoryIds as $catId) {
            $mapcollection = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Category')->getCollection()
                                                    ->addFieldToFilter('magento_id', ['eq'=>$catId]);
            foreach ($mapcollection as $map) {
                $odooId = $map->getOdooId();
                array_push($odooCategories, new xmlrpcval($odooId, 'int'));
            }
        }
        if (!$odooCategories) {
            $defaultCategory = $helper->getSession()->getErpCateg();
            array_push($odooCategories, new xmlrpcval($defaultCategory, 'int'));
        }
        return $odooCategories;
    }

    public function getProductArray($productId)
    {
        $type = 'product';
        $product = $this->_objectManager
                        ->create('\Magento\Catalog\Model\Product')
                        ->load($productId);
        $itemId = $this->_objectManager
                        ->create('\Magento\CatalogInventory\Api\StockRegistryInterface')
                        ->getStockItem($productId)->getId();
        $ean = $product->getEan();
        $keys = ['simple','grouped','configurable','virtual','bundle','downloadable'];
        $productType = $product->getTypeID();
        if (!in_array($productType, $keys)) {
            $productType = "";
        }
        if (!in_array($productType, ['simple', 'configurable'])) {
            $type = 'service';
        }
        $status = true;
        $status = urlencode($product->getStatus());
        if ($status == '2') {
            $status = false;
        }
        $sku = urlencode($product->getSku());
        $xmlrpcArray = [
                'type'=>new xmlrpcval($type, "string"),
                'default_code'=>new xmlrpcval($sku, "string"),
                'magento_stock_id'=>new xmlrpcval($itemId, "int"),
                'mage_id'=>new xmlrpcval($productId, "int"),
                'sale_ok'=>new xmlrpcval($status, "boolean")
        ];
        $setId = $product->getAttributeSetId();
        $name = urlencode($product->getName());
        $description = urlencode($product->getDescription());
        $shortDescription = urlencode($product->getShortDescription());
        $odooCategoryArray = $this->getProductCategoryArray($product->getCategoryIds());
        $odooSetId = $this->_objectManager
                            ->create('\Webkul\Odoomagentoconnect\Model\ResourceModel\Set')
                            ->getOdooAttributeSetId($setId);
        
        $xmlrpcArray['name'] = new xmlrpcval($name, "string");
        $xmlrpcArray['description'] = new xmlrpcval($description, "string");
        $xmlrpcArray['attribute_set_id'] = new xmlrpcval($odooSetId, "int");
        $xmlrpcArray['description_sale'] = new xmlrpcval($shortDescription, "string");
        $xmlrpcArray['list_price'] = new xmlrpcval($product->getPrice(), "double");
        $xmlrpcArray['prod_type'] = new xmlrpcval($productType, "string");
        $xmlrpcArray['weight'] = new xmlrpcval($product->getWeight(), "double");
        $xmlrpcArray['standard_price'] = new xmlrpcval($product->getCost(), "double");
        $xmlrpcArray['category_ids'] = new xmlrpcval($odooCategoryArray, "array");
        if ($product->getImage()) {
            if ($product->getImage() != "no_selection") {
                try {
                    $store = $this->_storeManager->getStore();
                    $baseImage = 'catalog/product'.$product->getImage();
                    $imagePath = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseImage;
                    $productImagePath = $this->_objectManager
                                                ->get('\Magento\Framework\App\Filesystem\DirectoryList')
                                                ->getPath('media').'/'.$baseImage;
                    if (file_exists($productImagePath)) {
                        $imageUrl = $imagePath;
                    } else {
                        $imageUrl = false;
                    }
                } catch (\Exception $e) {
                    $imageUrl = false;
                }
                if ($imageUrl) {
                    $xmlrpcArray['image_url'] = new xmlrpcval($imageUrl, "string");
                }
            }
        }
        if ($ean) {
            $xmlrpcArray['barcode'] = new xmlrpcval($ean, "string");
        }
        return $xmlrpcArray;
    }

    public function updateNormalProduct($mappingId)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mappingId) {
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $mapping =  $mappingId->getData();
            $odooId = $mapping['odoo_id'];
            $mageId = $mapping['magento_id'];
            $productArray = $this->getProductArray($mageId);
            unset($productArray['type']);
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("product.product", "string"));
            $msg->addParam(new xmlrpcval("write", "string"));
            $msg->addParam(new xmlrpcval($odooId, "int"));
            $msg->addParam(new xmlrpcval($productArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Update Error, Product Id ".$mageId." Reason >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $response['error'] = $resp->faultString();
                $helper->addError($error);
            } else {
                $response['odoo_id'] = $odooId;
                $this->updateMapping($mappingId, 'no');
                $dispatchData = ['product' => $mageId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                return $response;
            }
            return $response;
        }
    }

    public function createInventoryAtOdoo($productId, $erpProId)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $productQty = $this->_objectManager
                            ->create('\Magento\CatalogInventory\Api\StockRegistryInterface')
                            ->getStockItem($productId)->getQty();
        $inventoryArray = [
            'product_id'=>new xmlrpcval($erpProId, "int"),
            'new_quantity'=>new xmlrpcval($productQty, "double")
        ];
        $inv = new xmlrpcmsg('execute');
        $inv->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $inv->addParam(new xmlrpcval($userId, "int"));
        $inv->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $inv->addParam(new xmlrpcval("bridge.backbone", "string"));
        $inv->addParam(new xmlrpcval("update_quantity", "string"));
        $inv->addParam(new xmlrpcval($inventoryArray, "struct"));
        $inv->addParam(new xmlrpcval($context, "struct"));
        $inventoryResp = $client->send($inv);
        if ($inventoryResp->faultcode()) {
            $error = "Stock Change Qty Create, ".'Error:-'.$inventoryResp->faultString();
            $helper->addError($error);
        }
        return true;
    }

    public function mappingerp($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $productModel = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Product');
        $productModel->setData($data);
        $productModel->save();
        return true;
    }

    public function updateMapping($model, $status = 'yes')
    {
        $model->setNeedSync($status);
        $model->save();
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_product', 'entity_id');
    }
}
