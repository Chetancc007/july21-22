<?php
/**
 * Webkul Odoomagentoconnect Category Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Category;

class ExportAllIds extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Category $categoryMapping,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_categoryMapping = $categoryMapping;
        $this->_categoryModel = $categoryModel;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::category_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $exportIds = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $mapTemplates = [];
            $categoryCollection = $this->_categoryMapping->getCollection()
                                        ->addFieldToSelect('magento_id')->getData();
            foreach ($categoryCollection as $value) {
                array_push($mapTemplates, $value['magento_id']);
            }
            if ($mapTemplates) {
                $collection = $this->_categoryModel->getCollection()
                                        ->addAttributeToFilter('entity_id', ['nin' => $mapTemplates])
                                        ->addAttributeToFilter('level', ['neq' => 0])
                                        ->addAttributeToSelect('entity_id');
            } else {
                $collection = $this->_categoryModel->getCollection()
                                        ->addAttributeToFilter('level', ['neq' => 0])
                                        ->addAttributeToSelect('entity_id');
            }
            $exportIds = $collection->getAllIds();
            
            if (count($collection) == 0) {
                if (empty($mapTemplates)) {
                    $this->messageManager->addSuccess(__("No Categories are exist at Magento to export at Odoo."));
                } else {
                    $this->messageManager->addSuccess(__("All Magento Categories are already exported at Odoo."));
                }
            } else {
                if (empty($exportIds)) {
                    $this->messageManager->addSuccess(__("All Magento Categories are already exported at Odoo."));
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Category(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
