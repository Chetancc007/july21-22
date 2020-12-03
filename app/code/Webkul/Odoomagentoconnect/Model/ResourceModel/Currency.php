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

class Currency extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_objectManager;
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Connection $connection,
        $resourcePrefix = null
    ) {
    
        $this->_objectManager = $objectManager;
        parent::__construct($context, $resourcePrefix);
        $this->_connection = $connection;
    }

    public function syncCurrency($currencyCode)
    {
        $pricelistId = 0;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $errorMessage = $helper->getSession()->getErrorMessage();
        if ($currencyCode) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $userId = $helper->getSession()->getUserId();
            $mappingcollection = $this->_objectManager
                                        ->create('\Webkul\Odoomagentoconnect\Model\Currency')
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$currencyCode]);
            if ($mappingcollection->getSize() > 0) {
                foreach ($mappingcollection as $map) {
                    $pricelistId = $map->getOdooId();
                }
            } else {
                $pricelistArray =  [
                    'code'=>new xmlrpcval($currencyCode, "string")
                ];
                $context = ['context' => new xmlrpcval($context, "struct")];
                $pricelistArray = [new xmlrpcval($pricelistArray, "struct")];
                $msg = new xmlrpcmsg('execute_kw');
                $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
                $msg->addParam(new xmlrpcval($userId, "int"));
                $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                $msg->addParam(new xmlrpcval("connector.snippet", "string"));
                $msg->addParam(new xmlrpcval("create_pricelist", "string"));
                $msg->addParam(new xmlrpcval($pricelistArray, "array"));
                $msg->addParam(new xmlrpcval($context, "struct"));
                $resp = $client->send($msg);
                if ($resp->faultCode()) {
                    $error = "Export Error, Currency ".$currencyCode." >>".$resp->faultString();
                    $helper->addError($error);
                } else {
                    $pricelistId = $resp->value()->me["int"];
                    if ($pricelistId) {
                        $curModel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Currency');
                        $curModel->setOdooId($pricelistId);
                        $curModel->setMagentoId($currencyCode);
                        $curModel->setCreatedBy($helper::$mageUser);
                        $curModel->save();
                    } else {
                        $error = "Export Error, Currency ".$currencyCode." >> Please enable this currency at odoo end.";
                        $helper->addError($error);
                    }
                }
            }
        }
        return $pricelistId;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_currency', 'entity_id');
    }
}
