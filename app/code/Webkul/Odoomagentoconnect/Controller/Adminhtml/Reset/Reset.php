<?php
/**
 * Webkul Odoomagentoconnect Reset Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Reset;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Reset extends Action
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface;
     */
    protected $_scopeConfig;
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }

    public function execute()
    {
        $models = ['Category','Product',
                        'Template','Attribute',
                        'Set','Option','Customer',
                        'Tax','Payment','Order',
                        'Carrier','Currency'];
        $status = false;
        $resetModels = '';
        foreach ($models as $model) {
            $collections = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Model\\'.$model)->getCollection();
            if ($collections) {
                $collections->walk('delete');
                $status = true;
            }
            if ($status) {
                $resetModels .= $model.' ,';
                $status = false;
            }
        }
        $this->_eventManager->dispatch('odoomagentoconnect_mapping_delete_after', [$this]);
        $resetData = $this->_session->getResetData();
        if ($resetData) {
            $resetModels .= $resetData.' ,';
        }

        if ($resetModels) {
            $this->messageManager->addSuccess(__($resetModels." Odoo mapping data deleted successfully."));
        } else {
            $this->messageManager->addError(__("All mapping data are already deleted"));
        }
        return $resultJson = $this->_resultJsonFactory->create()->setData('');
    }
}
