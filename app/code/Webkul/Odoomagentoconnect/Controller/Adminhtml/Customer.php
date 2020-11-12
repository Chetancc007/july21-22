<?php
/**
 * Webkul Odoomagentoconnect Customer Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml;

abstract class Customer extends \Magento\Backend\App\AbstractAction
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
     * Customer model factory
     *
     * @var \Webkul\Odoomagentoconnect\Model\customerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\Odoomagentoconnect\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerModel,
        \Webkul\Odoomagentoconnect\Model\Customer $customerMapping,
        \Webkul\Odoomagentoconnect\Model\CustomerFactory $customerFactory
    ) {
    
        parent::__construct($context);
        $this->_customerMapping = $customerMapping;
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
        $this->_customerModel = $customerModel;
        $this->_customerFactory = $customerFactory;
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
