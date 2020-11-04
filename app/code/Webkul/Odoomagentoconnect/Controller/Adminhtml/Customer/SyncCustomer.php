<?php
/**
 * Webkul Odoomagentoconnect Customer Save Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class SyncCustomer extends \Magento\Backend\App\Action
{
        /**
         * @param Action\Context $context
         * @param Builder $productBuilder
         * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
         * @param Filter $filter
         * @param CollectionFactory $collectionFactory
         */
    protected $_collectionFactory;
    protected $_filter;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        \Webkul\Odoomagentoconnect\Model\Customer $customerModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerMapping,
        CollectionFactory $collectionFactory
    ) {
        $this->_customerModel = $customerModel;
        $this->_connection = $connection;
        $this->_customerMapping = $customerMapping;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $countSyncCustomer = 0;
            $alreadySyncCustomer = 0;
            $countNotSyncCustomer = 0;
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $customerIds = $collection->getAllIds();

            foreach ($customerIds as $customerId) {
                $mapping = $this->_customerModel->getCollection()
                                ->addFieldToFilter('address_id', ['eq'=>'customer'])
                                ->addFieldToFilter('magento_id', ['eq'=>$customerId]);
                if ($mapping->getSize() == 0) {
                    $response = $this->_customerMapping->exportSpecificCustomer($customerId);
                    if ($response['odoo_id'] > 0) {
                        $countSyncCustomer++;
                    } else {
                        $countNotSyncCustomer++;
                    }
                } else {
                    $alreadySyncCustomer++;
                }
            }
            if ($countSyncCustomer) {
                $this->messageManager->addSuccess(__('%1 customer(s) synchronized at Odoo.', $countSyncCustomer));
            }
            if ($countNotSyncCustomer) {
                $this->messageManager->addError(
                    __(
                        '%1 customer(s) cannot be synchronized at Odoo.',
                        $countNotSyncCustomer
                    )
                );
            }
            if ($alreadySyncCustomer) {
                $this->messageManager->addSuccess(
                    __(
                        'Total of %1 customer(s) are already Synchronized at Odoo.',
                        $alreadySyncCustomer
                    )
                );
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Selected customer(s) cannot be synchronized at Odoo. !! Reason : ".$errorMessage
                )
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return  $resultRedirect->setPath('customer/index/index', ['store' => $storeId]);
    }
}
