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

abstract class Option extends \Magento\Backend\App\AbstractAction
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
     * Option model factory
     *
     * @var \Webkul\Odoomagentoconnect\Model\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\Odoomagentoconnect\Model\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Odoomagentoconnect\Model\Option $optionMapping,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory 
    ) {
    
        parent::__construct($context);
        $this->_optionMapping = $optionMapping;
        $this->_coreRegistry = $coreRegistry;
        $this->_connection = $connection;
        $this->_userFactory = $userFactory;
        $this->_optionFactory = $optionFactory;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
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
