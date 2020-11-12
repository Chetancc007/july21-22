<?php
/**
 * Webkul Odoomagentoconnect Option Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml;

abstract class Order extends \Magento\Backend\App\AbstractAction
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
     * Order model factory
     *
     * @var \Webkul\Odoomagentoconnect\Model\orderFactory
     */
    protected $_orderFactory;

    /**
     * Order model factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\Odoomagentoconnect\Model\orderFactory $orderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Sales\Model\Order $salesOrderModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderModel,
        \Webkul\Odoomagentoconnect\Model\Order $orderMapping,
        \Webkul\Odoomagentoconnect\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_saleOrderModel = $salesOrderModel;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->_userFactory = $userFactory;
        $this->_orderFactory = $orderFactory;
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
