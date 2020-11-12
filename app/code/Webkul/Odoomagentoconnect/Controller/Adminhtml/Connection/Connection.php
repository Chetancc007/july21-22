<?php
/**
 * Webkul Odoomagentoconnect Category Connection Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Connection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Connection extends Action
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface;
     */
    protected $_scopeConfig;
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
        \Magento\Config\Model\ResourceModel\Config $configModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_connection = $connection;
        $this->_configModel = $configModel;
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
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();
        $configPath = 'odoomagentoconnect/settings/odoo_status';
        if ($userId > 0) {
            $message = "Successfully Connected";
            $this->_configModel
            ->saveConfig($configPath, $message, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->messageManager->addSuccess(__("Congratulation !! Magento is successfully connected with Odoo"));
        } else {
            $this->_configModel
            ->saveConfig($configPath, "Not Connected", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->messageManager->addError(__($errorMessage));
            return $resultJson = $this->_resultJsonFactory->create()->setData(['hi'=>'adsa']);
        }
    }
}
