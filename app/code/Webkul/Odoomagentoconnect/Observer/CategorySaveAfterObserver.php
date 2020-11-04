<?php
/**
 * Webkul Odoomagentoconnect SalesOrderPlaceAfterObserver Observer Model
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 *
 */
namespace Webkul\Odoomagentoconnect\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class CategorySaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Category $categoryMapping,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Category $categoryModel,
        \Magento\Catalog\Model\Category $catalogModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_categoryModel = $categoryModel;
        $this->_catalogModel = $catalogModel;
        $this->_categoryMapping = $categoryMapping;
        $this->_connection = $connection;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Category save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $category->save();
        $mappingObj = 0;
        $catId = $category->getId();
        $route = $this->_requestInterface->getControllerName();
        $categoryModelObj = $this->_categoryMapping;
        if ($catId && $route == 'category') {
            $helper = $this->_connection;
            $helper->getSocketConnect();
            $autoSync = $this->_scopeConfig->getValue('odoomagentoconnect/automatization_settings/auto_category');
            $collection = $this->_categoryModel->getCollection()
                                ->addFieldToFilter('magento_id', ['eq'=>$catId]);
            foreach ($collection as $map) {
                $mappingObj = $map;
            }
            if (!$autoSync && $mappingObj) {
                $categoryModelObj->updateMapping($mappingObj, 'yes');
            }
            
            $userId = $helper->getSession()->getUserId();
            if ($userId > 0 && $autoSync) {
                $catalogObj = $this->_catalogModel->load($catId);
                $catalogObj->setName($category->getName());
                $catalogObj->setParentId($category->getParentId());
                $catalogObj->save();
                if ($mappingObj) {
                    $mageId = $mappingObj->getMagentoId();
                    $odooId = $mappingObj->getOdooId();
                    $categoryModelObj->createSpecificCategory($mageId, 'write', $odooId, $mappingObj);
                } else {
                    $response = $categoryModelObj->createSpecificCategory($catId, 'create');
                }
            }
        }
    }
}
