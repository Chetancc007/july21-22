<?php

namespace Nits\ProductAutoSyncWithOdoo\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductSync {

    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Model\Template $templateMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateModel,
        \Webkul\Odoomagentoconnect\Model\Product $productMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product\CollectionFactory $productMappingCollection,
        \Magento\Catalog\Model\Product $catalogModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {

        $this->filter = $filter;
        $this->_templateModel = $templateModel;
        $this->_connection = $connection;
        $this->_catalogModel = $catalogModel;
        $this->_templateMapping = $templateMapping;
        $this->_productModel = $productModel;
        $this->_productMapping = $productMapping;
        $this->_productMappingCollection = $productMappingCollection;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        //parent::__construct($context, $productBuilder);
    }

    public function execute()
    {

        $countNonSyncProduct = 0;
        $countSyncProduct = 0;
        $alreadySyncProduct = 0;
        $countUpdateProduct = 0;
        $countNonUpdateProduct = 0;
        $errorMessage = '';
        $errorUpdateMessage = '';

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/odoo_product_sync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $mappingAvailable = $this->_productMappingCollection->create();
        $mappingAvailable->addFieldToSelect('magento_id');

        $items = [];
        foreach($mappingAvailable as $item) {
            $items[] = $item->getData('magento_id');
        }
        $logger->info("Already Sync Count: ".count($items));

        $collection = $this->collectionFactory->create();
        $logger->info("Magento Product Count: ".count($collection->getAllIds()));


        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['nin' => $items]);
        $logger->info("Product Not Sync to Odoo Count: ".count($collection->getAllIds()));

        //$collection = $this->collectionFactory->create();
        //$collection->addFieldToFilter('entity_id', ['nin' => $items]);
        $collection->setPageSize(100);
        $collection->setCurPage(1);
        $logger->info("Product Sync processing Count: ".count($collection));


        $productIds = $collection->getAllIds();
        foreach ($collection as $product) {
            $productId = $product->getId();
            if ($product->getTypeID() == "configurable") {
                $mapping = $this->_templateMapping->getCollection()
                    ->addFieldToFilter('magento_id', ['eq'=>$productId]);
                $templateObj = $this->_templateModel;
                if ($mapping->getSize() == 0) {
                    $response = $templateObj->exportSpecificConfigurable($productId);
                    if ($response['odoo_id'] > 0) {
                        $erpTemplateId = $response['odoo_id'];
                        $templateObj->syncConfigChildProducts($productId, $erpTemplateId);
                        $countSyncProduct++;
                    } else {
                        $countNonSyncProduct++;
                        $errorMessage = $response['error'];
                    }
                } else {
                    foreach ($mapping as $mageObj) {
                        if ($mageObj->getNeedSync() == "yes") {
                            $response = $templateObj->updateConfigurableProduct($mageObj);
                            if ($response['odoo_id'] > 0) {
                                $countUpdateProduct++;
                            } else {
                                $countNonUpdateProduct++;
                                $errorUpdateMessage = $response['error'];
                            }
                        } else {
                            $alreadySyncProduct++;
                        }
                    }
                }
            } else {
                $mapping = $this->_productMapping->getCollection()
                    ->addFieldToFilter('magento_id', ['eq'=>$productId]);
                $productObj = $this->_productModel;
                if ($mapping->getSize() == 0) {
                    $response = $productObj->createSpecificProduct($productId);
                    if ($response['odoo_id'] > 0) {
                        $countSyncProduct++;
                    } else {
                        $countNonSyncProduct++;
                        $errorMessage = $response['error'];
                    }
                } else {
                    foreach ($mapping as $mageObj) {
                        if ($mageObj->getNeedSync() == "yes") {
                            $response = $productObj->updateNormalProduct($mageObj);
                            if ($response['odoo_id'] > 0) {
                                $countUpdateProduct++;
                            } else {
                                $countNonUpdateProduct++;
                                $errorUpdateMessage = $response['error'];
                            }
                        } else {
                            $alreadySyncProduct++;
                        }
                    }
                }
            }
        }

        $logger->info("Product Sync completed Count: ".count($collection));

        if ($countNonSyncProduct) {
            $logger->info($countNonSyncProduct.' product(s) cannot be synchronized at Odoo. Reason : '.$errorMessage);
        }
        if ($countSyncProduct) {
            $logger->info('Total of '.$countSyncProduct.' product(s) have been successfully Exported at Odoo.');
        }
        if ($countUpdateProduct) {
            $logger->info('Total of '.$countUpdateProduct.' product(s) have been successfully Updated at Odoo.');
        }
        if ($countNonUpdateProduct) {
            $logger->info($countNonUpdateProduct.' product(s) cannot be update at Odoo. Reason : '.$errorUpdateMessage);
        }
        if ($alreadySyncProduct) {
            $logger->info('Total of '.$alreadySyncProduct.' product(s) are already Synchronized at Odoo.');
        }

         $logger->info("-----------------------------------------------------------------");

    }

}
