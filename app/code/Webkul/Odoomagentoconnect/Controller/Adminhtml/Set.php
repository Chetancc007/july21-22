<?php
/**
 * Webkul Odoomagentoconnect Set Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml;

abstract class Set extends \Magento\Backend\App\AbstractAction
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
     * Set model factory
     *
     * @var \Webkul\Odoomagentoconnect\Model\SetFactory
     */
    protected $_setFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\Odoomagentoconnect\Model\SetFactory $setFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setInterface,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Set $setModel,
        \Webkul\Odoomagentoconnect\Model\Set $setMapping,
        \Webkul\Odoomagentoconnect\Model\SetFactory $setFactory
    ) {
        parent::__construct($context);
        $this->_setModel = $setModel;
        $this->_setMapping = $setMapping;
        $this->_setInterface = $setInterface;
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
        $this->_setFactory = $setFactory;
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
