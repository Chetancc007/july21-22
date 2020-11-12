<?php
/**
 * Webkul Odoomagentoconnect Product Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Product\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Product;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;
/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_PRODUCT_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;



    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\product $product,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_authSession = $authSession;
        $this->_LocaleLists = $localeLists;
        $this->_objectManager = $objectManager;        
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $Mage_Product = $this->_product->getMageProductArray();
        $Erp_Product = $this->_product->getErpProductArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $productmodel = $this->_coreRegistry->registry('odoomagentoconnect_product');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Product Mapping'), 'class' => 'fieldset-wide']);

        if($productmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Product'),
                    'title' => __('Magento Product'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Product
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Product'),
                    'title' => __('Odoo Product'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Product
                ]
            );

        //$data = $model->getData();
        $data= $productmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
