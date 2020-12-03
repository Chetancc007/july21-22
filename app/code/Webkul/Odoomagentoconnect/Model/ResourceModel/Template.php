<?php
/**
 * Webkul Odoomagentoconnect Template ResourceModel
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

class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productManager,
        \Webkul\Odoomagentoconnect\Model\Product $productModel,
        \Webkul\Odoomagentoconnect\Model\Option $optionMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attributeModel,
        \Magento\Catalog\Model\Product $catalogManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_connection = $connection;
        $this->_productModel = $productModel;
        $this->_productManager = $productManager;
        $this->_optionMapping = $optionMapping;
        $this->_catalogManager = $catalogManager;
        $this->_configurableModel = $configurableModel;
        $this->_attributeModel = $attributeModel;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $resourcePrefix);
    }

    public function mappingtemplatemap($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $categorymodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Template');
        $categorymodel->setData($data);
        $categorymodel->save();
        return true;
    }

    public function updateMapping($model, $status = 'yes')
    {
        $model->setNeedSync($status);
        $model->save();
        return true;
    }

    public function syncConfigurableProduct($mappingObj, $proId)
    {
        if ($mappingObj) {
            $this->updateConfigurableProduct($mappingObj);
        } else {
            $response = $this->exportSpecificConfigurable($proId);
            if ($response['odoo_id'] > 0) {
                $erpTemplateId = $response['odoo_id'];
                $this->syncConfigChildProducts($proId, $erpTemplateId);
            }
        }
        return true;
    }

    public function exportSpecificConfigurable($configurableId)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if (!$userId) {
            $response['odoo_id'] = 0;
            $error = $helper->getSession()->getErrorMessage();
            $response['error'] = $error;
            return $response;
        }
        if ($configurableId) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            
            $childIds = $this->_configurableModel->getChildrenIds($configurableId);
            if (!$childIds[0]) {
                $errorMsg = "Product Export Error, Product Id ".$configurableId.", No Child Product Exists!!!";
                $helper->addError($errorMsg);
                return [
                    'error' => $errorMsg,
                    'odoo_id' => -1
                ];
            }
            $configurableArray = $this->_productManager->getProductArray($configurableId);
            $attributes = $this->erpAttributeList($configurableId);
            $configurableArray['attribute_list'] = new xmlrpcval($attributes, "array");
            $context['configurable'] = new xmlrpcval('configurable', "string");
            $context['create_product_product'] = new xmlrpcval(true, "boolean");
            $product = $this->_catalogManager->load($configurableId);
            if (isset($product->getData()['price'])) {
                $productPrice = $product->getData()['price'];
                $configurableArray['list_price'] = new xmlrpcval($productPrice, "double");
            }
            $context = ['context' => new xmlrpcval($context, "struct")];
            $configurableArray = [new xmlrpcval($configurableArray, "struct")];
            $msg = new xmlrpcmsg('execute_kw');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("product.template", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($configurableArray, "array"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Export Error, Prduct Id ".$configurableId." Reason >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $response['error'] = $error;
                $helper->addError($error);
            } else {
                $odooId = $resp->value()->me["int"];
                if ($odooId > 0) {
                    $mappingData = [
                                'odoo_id'=>$odooId,
                                'magento_id'=>$configurableId,
                                'created_by'=>$helper::$mageUser
                            ];
                    $this->mappingtemplatemap($mappingData);
                    $response['odoo_id'] = $odooId;
                    $dispatchData = ['product' => $configurableId, 'erp_product' => $odooId, 'type' => 'template'];
                    $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                }
            }
        }
        return $response;
    }

    public function updateConfigurableProduct($mappingId)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($mappingId) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $userId = $helper->getSession()->getUserId();
            
            $template =  $mappingId->getData();
            $configurableId = $template['magento_id'];
            $erpTemplateId = $template['odoo_id'];

            $configurableArray = $this->_productManager->getProductArray($configurableId);
            $product = $this->_catalogManager->load($configurableId);
            if (isset($product->getData()['price'])) {
                $productPrice = $product->getData()['price'];
                $configurableArray['list_price'] = new xmlrpcval($productPrice, "double");
            }
            $context = ['context' => new xmlrpcval($context, "struct")];
            $configurableArray = [new xmlrpcval($erpTemplateId, "int"), new xmlrpcval($configurableArray, "struct")];
            $msg = new xmlrpcmsg('execute_kw');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("product.template", "string"));
            $msg->addParam(new xmlrpcval("write", "string"));
            $msg->addParam(new xmlrpcval($configurableArray, "array"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Product Update Error, Prduct Id ".$configurableId." Reason >>".$resp->faultString();
                $helper->addError($error);
                $response['error'] = $resp->faultString();
                $response['odoo_id'] = 0;
            } else {
                $response['odoo_id'] = $erpTemplateId;
                $this->syncConfigChildProducts($configurableId, $erpTemplateId);
                $this->updateMapping($mappingId, 'no');
                $dispatchData = ['product' => $configurableId, 'erp_product' => $erpTemplateId, 'type' => 'template'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                return $response;
            }
            return $response;
        }
    }

    public function syncConfigChildProducts($configurableId, $erpTemplateId)
    {
        if ($configurableId) {
            $template = $this->_catalogManager->load($configurableId);
            $templatePrice = $template->getPrice();
            if (isset($template->getData()['price'])) {
                $templatePrice = $template->getData()['price'];
            }
            $attrCodes = $this->productAttributeLine($configurableId, $erpTemplateId);
            if ($attrCodes) {
                $childIds = $this->_configurableModel
                                    ->getChildrenIds($configurableId);
                foreach ($childIds[0] as $childId) {
                    $productMapping = $this->_productModel;
                    $mappingCollection = $productMapping->getCollection()
                                                            ->addFieldToFilter('magento_id', ['eq'=>$childId]);
                    if ($mappingCollection->getSize() > 0) {
                        foreach ($mappingCollection as $map) {
                            $mappingId = $map->getEntityId();
                            $this->updateChildProduct($mappingId, $erpTemplateId, $attrCodes, $templatePrice);
                        }
                    } else {
                        $this->exportChildProduct($erpTemplateId, $childId, $attrCodes, $templatePrice);
                    }
                }
            }
        }
        return true;
    }

    public function exportChildProduct($erpTmplId, $childId, $attrCodes, $templatePrice)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $context = $helper->getOdooContext();
        $client = $helper->getClientConnect();
        $userId = $helper->getSession()->getUserId();
        
        $productArray = $this->_productManager->getProductArray($childId);

        $product = $this->_catalogManager->load($childId);
        $productPrice = $product->getPrice();
        $variantExtraPrice = $productPrice - $templatePrice;
        $attributeValueIds = [];
        foreach ($attrCodes as $key) {
            $optionid =  $product->getData($key);
            $optionCollection = $this->_optionMapping->getCollection()
                                                ->addFieldToFilter('magento_id', $optionid);
            foreach ($optionCollection as $value) {
                $erpValueId = $value->getOdooId();
                array_push($attributeValueIds, new xmlrpcval($erpValueId, 'int'));
            }
        }
        if ($attributeValueIds) {
            $productArray['value_ids'] = new xmlrpcval($attributeValueIds, "array");
        }
        if ($erpTmplId) {
            $productArray['product_tmpl_id'] = new xmlrpcval($erpTmplId, "int");
        }
        if ($variantExtraPrice) {
            $productArray['wk_extra_price'] = new xmlrpcval($variantExtraPrice, "double");
        }
        if (isset($productArray['name'])) {
            unset($productArray['name']);
        }
        if (isset($productArray['list_price'])) {
            unset($productArray['list_price']);
        }
        $context = ['context' => new xmlrpcval($context, "struct")];
        $productArray = [new xmlrpcval($productArray, "struct")];
        $msg = new xmlrpcmsg('execute_kw');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("product.product", "string"));
        $msg->addParam(new xmlrpcval("create", "string"));
        $msg->addParam(new xmlrpcval($productArray, "array"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultCode()) {
            $error = "Export Error, Product Id ".$childId." Reason >>".$resp->faultString();
            $response['odoo_id'] = 0;
            $response['error'] = $error;
            $helper->addError($error);
        } else {
            $odooId = $resp->value()->me["int"];
            if ($odooId > 0) {
                $mappingData = [
                            'odoo_id'=>$odooId,
                            'magento_id'=>$childId,
                            'created_by'=>$helper::$mageUser
                        ];
                $this->_productManager->mappingerp($mappingData);
                $response['odoo_id'] = $odooId;
                $syncStock = $this->_scopeConfig->getValue('odoomagentoconnect/automatization_settings/auto_inventory');
                if ($syncStock) {
                    $this->_productManager
                            ->createInventoryAtOdoo($childId, $odooId);
                }
                $dispatchData = ['product' => $childId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
            }
        }
        return $response;
    }

    public function productAttributeLine($configurableId, $erpTemplateId)
    {
        $attrCodes = [];
        $helper = $this->_connection;
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $userId = $helper->getSession()->getUserId();
        $_product = $this->_catalogManager->load($configurableId);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        $context = ['context' => new xmlrpcval($context, "struct")];
        foreach ($attributes as $attribute) {
            $attributeArray = [];
            $attributeId = $attribute['attribute_id'];
            $attributeCode = $attribute['attribute_code'];
            array_push($attrCodes, $attributeCode);
            $response = $this->_attributeModel
                                ->syncAttribute($attributeId);
            $erpAttributeId = $response['erp_attribute_id'];
            if ($erpAttributeId) {
                $valueArray = [];
                $attributeOptions = $attribute['values'];
                $attributeArray['attribute_id'] = new xmlrpcval($erpAttributeId, "int");
                $attributeArray['product_tmpl_id'] = new xmlrpcval($erpTemplateId, "int");
                foreach ($attributeOptions as $option) {
                    $optionId = $option['value_index'];
                    $priceExtra = 0;
                    $optionCollection = $this->_optionMapping
                                                ->getCollection()
                                                ->addFieldToFilter('magento_id', $optionId);
                    foreach ($optionCollection as $value) {
                        $erpValueId = $value->getOdooId();
                        $value = [
                                        'value_id'=>new xmlrpcval($erpValueId, "int"),
                                        'price_extra'=>new xmlrpcval($priceExtra, "string"),
                                    ];
                        array_push($valueArray, new xmlrpcval($value, 'struct'));
                        break;
                    }
                }
                $attributeArray['values'] = new xmlrpcval($valueArray, 'array');
            }
            if ($attributeArray) {
                $attributeArray = [new xmlrpcval($attributeArray, "struct")];
                $msg = new xmlrpcmsg('execute_kw');
                $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
                $msg->addParam(new xmlrpcval($userId, "int"));
                $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                $msg->addParam(new xmlrpcval("connector.template.mapping", "string"));
                $msg->addParam(new xmlrpcval("create_n_update_attribute_line", "string"));
                $msg->addParam(new xmlrpcval($attributeArray, "array"));
                $msg->addParam(new xmlrpcval($context, "struct"));
                $resp = $client->send($msg);
            }
        }
        return $attrCodes;
    }

    public function erpAttributeList($configurableId)
    {
        $erpAttributes = [];
        $_product = $this->_catalogManager->load($configurableId);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            $response = $this->_attributeModel
                                ->syncAttribute($attributeId);
            $erpAttributeId = $response['erp_attribute_id'];
            if ($erpAttributeId) {
                array_push($erpAttributes, new xmlrpcval($erpAttributeId, 'int'));
            }
        }
        return $erpAttributes;
    }

    public function updateChildProduct($mappingId, $erpTemplateId, $attrCodes, $templatePrice)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($mappingId) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $userId = $helper->getSession()->getUserId();
            
            $mapping =  $this->_productModel
                                ->load($mappingId);
            $mappingData = $mapping->getData();
            $odooId = $mappingData['odoo_id'];
            $mageId = $mappingData['magento_id'];
            
            $productArray = $this->_productManager->getProductArray($mageId);
            $product = $this->_catalogManager->load($mageId);
            $productPrice = $product->getPrice();
            $variantExtraPrice = $productPrice - $templatePrice;
            $attributeValueIds = [];
            foreach ($attrCodes as $key) {
                $optionid =  $product->getData($key);
                $optionCollection = $this->_optionMapping->getCollection()
                                                    ->addFieldToFilter('magento_id', $optionid);
                foreach ($optionCollection as $value) {
                    $erpValueId = $value->getOdooId();
                    array_push($attributeValueIds, new xmlrpcval($erpValueId, 'int'));
                }
            }
            if ($attributeValueIds) {
                $productArray['value_ids'] = new xmlrpcval($attributeValueIds, "array");
            }
            if ($erpTemplateId) {
                $productArray['product_tmpl_id'] = new xmlrpcval($erpTemplateId, "int");
            }
            if ($variantExtraPrice) {
                $productArray['wk_extra_price'] = new xmlrpcval($variantExtraPrice, "double");
            }
            if (isset($productArray['name'])) {
                unset($productArray['name']);
            }
            if (isset($productArray['list_price'])) {
                unset($productArray['list_price']);
            }
            $context['create_product_variant'] = new xmlrpcval('create_product_variant', "string");
            $context = ['context' => new xmlrpcval($context, "struct")];
            $productArray = [new xmlrpcval($odooId, "int"), new xmlrpcval($productArray, "struct")];
            $productMsg = new xmlrpcmsg('execute_kw');
            $productMsg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $productMsg->addParam(new xmlrpcval($userId, "int"));
            $productMsg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $productMsg->addParam(new xmlrpcval("product.product", "string"));
            $productMsg->addParam(new xmlrpcval("write", "string"));
            $productMsg->addParam(new xmlrpcval($productArray, "array"));
            $productMsg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($productMsg);
            if ($resp->faultCode()) {
                $error = "Product Update Error, Product Id ".$mageId." Reason >>".$resp->faultString();
                $helper->addError($error);
            } else {
                $this->_productManager
                        ->updateMapping($mapping, 'no');
                $dispatchData = ['product' => $mageId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                return true;
            }
            return false;
        }
    }
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_template', 'entity_id');
    }
}
