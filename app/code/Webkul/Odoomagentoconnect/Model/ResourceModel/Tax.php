<?php
/**
 * Webkul Odoomagentoconnect Tax ResourceModel
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

class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_scopeConfig;
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Tax\Model\Calculation\Rate $rateModel,
        Connection $connection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $resourcePrefix = null
    ) {
        $this->_connection = $connection;
        $this->_rateModel = $rateModel;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $resourcePrefix);
    }

    public function mappingerp($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $categorymodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Tax');
        $categorymodel->setData($data);
        $categorymodel->save();
        return true;
    }

    public function getTaxArray($taxId)
    {
        $includesTax = $this->_scopeConfig->getValue('tax/calculation/price_includes_tax');
        $taxRate = $this->_rateModel->load($taxId);
        $code = $taxRate->getCode();
        $rate = $taxRate->getRate();
        $rate = $rate;
        $xmlrpcArray = [
                'name'=>new xmlrpcval($code, "string"),
                'description'=>new xmlrpcval($code, "string"),
                'amount_type'=>new xmlrpcval('percent', "string"),
                'price_include'=>new xmlrpcval($includesTax, "boolean"),
                'amount'=>new xmlrpcval($rate, "string")
        ];
        return $xmlrpcArray;
    }

    public function createSpecificTax($mageId)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $rateArray = $this->getTaxArray($mageId);
            $taxRate = $this->_rateModel->load($mageId);
            $code = $taxRate->getCode();
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("account.tax", "string"));
            $msg->addParam(new xmlrpcval("create", "string"));
            $msg->addParam(new xmlrpcval($rateArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Export Error, Tax Id ".$mageId." >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $response['error'] = $error;
                $helper->addError($error);
            } else {
                $odooId = $resp->value()->me["int"];
                if ($odooId > 0) {
                    $mappingData = [
                                'odoo_id'=>$odooId,
                                'magento_id'=>$mageId,
                                'code'=>$code,
                                'created_by'=>$helper::$mageUser
                            ];
                    $this->mappingerp($mappingData);
                    $response['odoo_id'] = $odooId;
                }
            }
        }
        return $response;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_tax', 'entity_id');
    }
}
