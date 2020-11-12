<?php
/**
 * Webkul Odoomagentoconnect Category ResourceModel
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

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_connection = $connection;
        parent::__construct($context, $resourcePrefix);
    }

    public function mappingerp($data)
    {
        $createdBy = 'Odoo';
        if (isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        }
        $categorymodel = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Category');
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

    public function getCategoryArray($categoryId)
    {
        $category = $this->_objectManager->create('\Magento\Catalog\Model\Category')->load($categoryId);
        $name = urlencode($category->getName());
        $xmlrpcArray = [
                'name'=>new xmlrpcval($name, "string"),
                'mage_id'=>new xmlrpcval($categoryId, "int")
        ];
        $parentId = $category->getParentId();
        if ($parentId) {
            $mapping = $this->_objectManager->create('Webkul\Odoomagentoconnect\Model\Category')->getCollection()
                                         ->addFieldToFilter('magento_id', ['eq'=>$parentId]);
            foreach ($mapping as $map) {
                $erpParentId = $map->getOdooId();
                $xmlrpcArray['parent_id'] = new xmlrpcval($erpParentId, "int");
            }
        }
        return $xmlrpcArray;
    }

    public function createSpecificCategory($mageId, $method, $odooId = 0, $categMapModel = 0)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $errorMessage = $helper->getSession()->getErrorMessage();

            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();

            $categoryArray = $this->getCategoryArray($mageId);
            $categoryArray['method'] = new xmlrpcval($method, "string");
            if ($odooId) {
                $categoryArray['category_id'] = new xmlrpcval($odooId, "int");
            }
            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval("magento.category", "string"));
            $msg->addParam(new xmlrpcval("create_category", "string"));
            $msg->addParam(new xmlrpcval($categoryArray, "struct"));
            $msg->addParam(new xmlrpcval($context, "struct"));
            $resp = $client->send($msg);
            if ($resp->faultCode()) {
                $error = "Export Error, Category Id ".$mageId." >>".$resp->faultString();
                $response['odoo_id'] = 0;
                $helper->addError($error);
            } else {
                if (!$odooId) {
                    $odooId = $resp->value()->me["int"];
                }
                if ($odooId > 0) {
                    $mappingData = [
                                'odoo_id'=>$odooId,
                                'magento_id'=>$mageId,
                                'created_by'=>$helper::$mageUser
                            ];
                    if ($categMapModel) {
                        $this->updateMapping($categMapModel, 'no');
                    } else {
                        $this->mappingerp($mappingData);
                    }

                    $this->_eventManager
                        ->dispatch('catalog_category_sync_after', ['mage_id' => $mageId, 'odoo_id' => $odooId,]);
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
        $this->_init('odoomagentoconnect_category', 'entity_id');
    }
}
