<?php
/**
 * Webkul Odoomagentoconnect Currency ResourceModel
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

class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Magento\Customer\Model\Customer $customerObj,
        Connection $connection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $resourcePrefix = null
    ) {
        $this->_customerObj = $customerObj;
        $this->_scopeConfig = $scopeConfig;
        $this->_connection = $connection;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $resourcePrefix);
    }

    public function mappingerp($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $customermodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Customer');
        $customermodel->setData($data);
        $customermodel->save();
        return true;
    }

    public function exportSpecificCustomer($customerId)
    {
        $response = [];
        if ($customerId) {
            $customer = $this->_customerObj->load($customerId);
            if (!$customer->getName()) {
                $response['odoo_id'] = 0;
                return $response;
            }
            $mageAddressId = 'customer';
            $mageCustomerId = $customer->getId();
            $storeId = $customer->getStoreId();
            $customerArray = $this->getCustomerArray($customer);
            $odooCustomerId = $this->odooCustomerCreate($customerArray, $mageCustomerId, $mageAddressId, $storeId);
            $response['odoo_id'] = $odooCustomerId;
            if ($odooCustomerId) {
                foreach ($customer->getAddresses() as $address) {
                    $odooAddressId = $this->syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address);
                }
            }
        }
        return $response;
    }

    public function updateSpecificCustomer($mappingId, $customerId, $odooCustomerId)
    {
        $helper = $this->_connection;
        if ($customerId && $odooCustomerId) {
            $model = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\Customer');
            $autoSync = $this->_scopeConfig->getValue('odoomagentoconnect/automatization_settings/auto_customer');
            $customer = $this->_customerObj->load($customerId);
            $mageAddressId = 'customer';
            $mageCustomerId = $customer->getId();
            $customerArray = $this->getCustomerArray($customer);
            $this->odooCustomerUpdate($customerId, $mageAddressId, $customerArray, $odooCustomerId);

            /*  Address Synchronization  */
            foreach ($customer->getAddresses() as $address) {
                $addresscollection = $model->getCollection()
                                    ->addFieldToFilter('magento_id', ['eq'=>$customerId])
                                    ->addFieldToFilter('address_id', ['eq'=>$address->getId()]);
                if ($addresscollection->getSize() > 0) {
                    foreach ($addresscollection as $map) {
                        $needSync = $map->getNeedSync();
                        if ($needSync == 'yes' || $autoSync) {
                            $odooId = $map->getOdooId();
                            $addressMapId = $map->getEntityId();
                            $odooAddressId = $this->syncSpecificAddressAtOdoo(
                                $odooCustomerId,
                                $customer,
                                $address,
                                'write',
                                $odooId
                            );
                        }
                    }
                } else {
                    $odooAddressId = $this->syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address);
                }
            }
            return true;
        }
    }
    
    public function syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address, $method = 'create', $partnerId = 0)
    {
        $odooId = 0;
        $addressArray = [];
        $streets = $address->getStreet();
        if (count($streets)>1) {
            $street = urlencode($streets[0]);
            $street2 = urlencode($streets[1]);
        } else {
            $street = urlencode($streets[0]);
            $street2 = urlencode('');
        }
        $customerId = $customer->getId();
        $storeId = $customer->getStoreId();
        $addressId = $address->getId();
        $name = urlencode($address->getName());
        $email = urlencode($customer->getEmail());
        $city = urlencode($address->getCity());
        $region = urlencode($address->getRegion());
        $company = urlencode($address->getCompany());
        $type = $this->getAddressType($customer, $address);
        $addressArray =  [
                    'name'=>new xmlrpcval($name, "string"),
                    'street'=>new xmlrpcval($street, "string"),
                    'street2'=>new xmlrpcval($street2, "string"),
                    'city'=>new xmlrpcval($city, "string"),
                    'email'=>new xmlrpcval($email, "string"),
                    'zip'=>new xmlrpcval($address->getPostcode(), "string"),
                    'phone'=>new xmlrpcval($address->getTelephone(), "string"),
                    'country_code'=>new xmlrpcval($address->getCountryId(), "string"),
                    'region'=>new xmlrpcval($region, "string"),
                    'type'=>new xmlrpcval($type, "string"),
                    'wk_company'=>new xmlrpcval($company, "string"),
                    'customer'=>new xmlrpcval(false, "boolean"),
                    'parent_id'=>new xmlrpcval($odooCustomerId, "int"),
                ];
        if ($method == 'create') {
            $odooId = $this->odooCustomerCreate($addressArray, $customerId, $addressId, $storeId);
        } elseif ($method == 'write') {
            $this->odooCustomerUpdate($customerId, $addressId, $addressArray, $partnerId);
        }
        /* Customer Vat Synchronization*/
        $taxVat = $customer->getTaxvat();
        if ($taxVat) {
            preg_match('/^[a-zA-Z]{2}/', $taxVat, $matches);
            if (!$matches) {
                $taxVat = $address->getCountryId().''.$taxVat;
            }
            $vatArray =  [
                    'vat'=>new xmlrpcval($taxVat, "string"),
                ];
            $this->odooCustomerUpdate($customerId, $addressId, $vatArray, $odooCustomerId);
        }
        return $odooId;
    }

    public function getCustomerArray($customer)
    {
        $customerArray =[];
        $helper = $this->_connection;
        $helper->getSocketConnect();

        $name = urlencode($customer->getName());
        $email = urlencode($customer->getEmail());
        $customerArray =  [
                'name'=>new xmlrpcval($name, "string"),
                'email'=>new xmlrpcval($email, "string"),
                'is_company'=>new xmlrpcval(false, "boolean"),
        ];
        return $customerArray;
    }

    public function odooCustomerCreate($customerArray, $mageCustomerId, $mageAddressId, $storeId = 0)
    {
        $odooId = 0;
        $extraFieldArray = [];
        $helper = $this->_connection;
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        /* Adding Extra Fields*/
        $helper->getSession()->setExtraFieldArray($extraFieldArray);
        $this->_eventManager->dispatch('customer_sync_before', ['mage_id' => $storeId]);
        $extraFieldArray = $helper->getSession()->getExtraFieldArray();
        foreach ($extraFieldArray as $field => $value) {
            $customerArray[$field]= $value;
        }
        $msg = new xmlrpcmsg('execute');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("res.partner", "string"));
        $msg->addParam(new xmlrpcval("create", "string"));
        $msg->addParam(new xmlrpcval($customerArray, "struct"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultCode()) {
            $error = "Export Error, Customer Id ".$mageCustomerId."(".$mageAddressId.") >>".$resp->faultString();
            $response['odoo_id'] = 0;
            $response['error'] = $error;
            $helper->addError($error);
        } else {
            $odooId = $resp->value()->me["int"];

            /* entry inside Mapping table*/
            if ($odooId && $mageCustomerId && $mageAddressId) {
                $mappingData = [
                        'odoo_id'=>$odooId,
                        'magento_id'=>$mageCustomerId,
                        'address_id'=>$mageAddressId,
                        'created_by'=>$helper::$mageUser
                    ];
                $this->mappingerp($mappingData);

                if ($odooId > 0 && $mageAddressId) {
                    $mapArray = [
                        'cus_name'=>new xmlrpcval($odooId, "int"),
                        'oe_customer_id'=>new xmlrpcval($odooId, "int"),
                        'mag_customer_id'=>new xmlrpcval($mageCustomerId, "string"),
                        'mag_address_id'=>new xmlrpcval($mageAddressId, "string"),
                        'created_by'=>new xmlrpcval($helper::$mageUser, "string"),
                        'instance_id'=>$context['instance_id'],
                    ];
                    $map = new xmlrpcmsg('execute');
                    $map->addParam(new xmlrpcval($helper::$odooDb, "string"));
                    $map->addParam(new xmlrpcval($userId, "int"));
                    $map->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                    $map->addParam(new xmlrpcval("magento.customers", "string"));
                    $map->addParam(new xmlrpcval("create", "string"));
                    $map->addParam(new xmlrpcval($mapArray, "struct"));
                    $msg->addParam(new xmlrpcval($context, "struct"));
                    $resp = $client->send($map);
                }
            }
        }
        return $odooId;
    }

    public function odooCustomerUpdate($customerId, $addressId, $addressArray, $erpCustomerId)
    {

        $helper = $this->_connection;
        $userId = $helper->getSession()->getUserId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $addressArray['mage_customer_id'] = new xmlrpcval($customerId, "int");

        $msg = new xmlrpcmsg('execute');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("res.partner", "string"));
        $msg->addParam(new xmlrpcval("write", "string"));
        $msg->addParam(new xmlrpcval($erpCustomerId, "int"));
        $msg->addParam(new xmlrpcval($addressArray, "struct"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultCode()) {
            $error = "Customer Update Error, Customer Id ".$customerId."(".$addressId.") >>".$resp->faultString();
            $helper->addError($error);
        }
        return true;
    }

    public function getAddressType($customer, $address)
    {
        $type = '';

        if ($customer->getDefaultBilling() && $customer->getDefaultBilling() == $address->getId()) {
            $type = 'invoice';
        } elseif ($customer->getDefaultShipping() && $customer->getDefaultShipping() == $address->getId()) {
            $type = 'delivery';
        } else {
            $type = 'other';
        }

        return $type;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_customer', 'entity_id');
    }
}
