<?php
/**
 * Webkul Odoomagentoconnect Carrier Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml;

abstract class Carrier extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * Carrier model factory
     *
     * @var \Webkul\Odoomagentoconnect\Model\carrierFactory
     */
    protected $_carrierFactory;

    protected $_scopeConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\Odoomagentoconnect\Model\carrierFactory $carrierFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Odoomagentoconnect\Model\CarrierFactory $carrierFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Odoomagentoconnect\Model\Carrier $carrierMapping
    ) {
    
        parent::__construct($context);
        $this->_carrierMapping = $carrierMapping;
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
        $this->_carrierFactory = $carrierFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Webkul_Odoomagentoconnect::manager'
        );
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_users');
    }
}
